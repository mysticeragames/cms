<?php

namespace App\Controller\Render;

use App\Repositories\PageRepository;
use App\Services\ContentParser;
use App\Services\ContentRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;

class RenderController extends AbstractController
{
    private ContentParser $contentParser;
    private PageRepository $pageRepository;
    private ContentRenderer $contentRenderer;

    public function __construct(string $projectDir, ContentParser $contentParser, PageRepository $pageRepository, ContentRenderer $contentRenderer)
    {
        if(!is_dir($projectDir . '/content')) {

            dd('no content dir found: ' . $projectDir . '/content');
        }

        $this->contentParser = $contentParser;
        $this->pageRepository = $pageRepository;
        $this->contentRenderer = $contentRenderer;
    }

    // #[Route('/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'], priority: -100)]
    // public function renderPath(string $path = ''): Response
    // {
    //     dump('render');
    //     dd($path);
    // }

    // #[Route('/assets/{path}', 'debug', methods: ['get'], requirements: ['path' => '.+'], priority: 1000)]
    // function debug(string $path): Response
    // {
    //     dd($path);
    // }


    
    ##[Route('/assets/{path}', 'asset-single', methods: ['get'])]
    #[Route('/assets/{path}', 'asset', methods: ['get'], requirements: ['path' => '.+'], priority: -200)]
    function renderAssetPath(string $path): Response
    {
        return $this->renderAsset('/assets/' . $path);
    }

    #[Route('/---cms/render/edit/{path}', 'cms.pages.render-edit', methods: ['get'], requirements: ['path' => '.+'], priority: 50)]
    public function renderEditUrl(string $path = ''): Response
    {
        dump('EDITMODE --- ' . $path);

        $page = $this->pageRepository->getPage($path);
        if($page !== null) {

            $editFilepath = $page['filePath'];
            if(!str_ends_with($editFilepath, PageRepository::EDIT_PATH_SUFFIX)) {
                $editFilepath = $editFilepath . PageRepository::EDIT_PATH_SUFFIX;
            }
            if(!is_file($editFilepath)) {
                copy($page['filePath'], $editFilepath);
            }
        }

        //dd($repo->getPages());
        //dd($repo->getPage('games/age-of-jura'));
        return $this->renderUrl($path, true);
    }

    


    ##[Route('/{path}', 'render-single', methods: ['get'])]
    #[Route('/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'], priority: -100)]
    public function runUrl(string $path = ''): Response
    {
        //dd($repo->getPages());
        //dd($repo->getPage('games/age-of-jura'));
        return $this->renderUrl($path);
    }

    

    function render404NotFoundHeaderOnly(): Response
    {
        return new Response(
            '',
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/plain'],
        );
    }

    function isAsset(string $path): bool
    {
        if(str_starts_with($path, 'assets/')) {
            return true;
        }
        
        $pathinfo = pathinfo($path);
        if(isset($pathinfo['extension']) && strtolower($pathinfo['extension']) !== 'html') { // !in_array(strtolower($pathinfo['extension']), ['html', 'htm'])) {
            return true;
        }
        
        return false;
    }

    function renderAsset(string $path): Response
    {
        $filepath = $this->getParameter('kernel.project_dir') . '/content/public/' . $path;
        if(!is_file($filepath)) {
            return $this->render404NotFoundHeaderOnly();
        }

        $mimeType = null;

        $pathinfo = pathinfo($filepath);
        if(isset($pathinfo['extension'])) {
            $mimeTypes = (new MimeTypes())->getMimeTypes($pathinfo['extension']);
            if(!empty($mimeTypes)) {
                $mimeType = array_shift($mimeTypes);
            }
        }
        if(empty($mimeType)) {
            $mimeType = 'text/plain';
        }

        $response = new Response();
        $response->setContent(file_get_contents($filepath));
        $response->headers->set('Content-type', $mimeType);

        return $response;
    }

    public function renderUrl(string $path, bool $editMode = false): Response
    {
        // Render public asset
        if($this->isAsset($path)) {
            return $this->renderAsset($path);
        }

        $content = $this->contentRenderer->render($this->getParameter('kernel.project_dir'), $path, $editMode);

        return new Response($content);
    }

    function getConfig(): array
    {
        $configPath = $this->getParameter('kernel.project_dir') . '/content/config.md';
        if(file_exists($configPath)) {
            $markdown = file_get_contents($configPath);

            return $this->contentParser->parse($markdown)['variables'];
        }
        return [];
    }

    // function scanPageFiles(): array
    // {
    //     $files = [];
    //     $directory = $this->getParameter('kernel.project_dir') . '/content/pages';
        
    //     foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS ) ) as $file ) {
    //         if($file->isFile() && strtolower($file->getExtension()) === 'md') {
    //             $path = $file->getPathname();

    //             $files[] = [
    //                 //'file' => $file,
    //                 'url' => substr($path, strlen($directory) + 1, -3) . '.html',
    //                 'filepath' => $file->getPathname(),
    //                 'updatedAt' => date("Y-m-d H:i:s", $file->getMTime()),
    //             ];
    //         }
    //     }
    //     return $files;
    // }
}
