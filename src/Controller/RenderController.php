<?php

namespace App\Controller;

use App\Repositories\PageRepository;
use App\Services\ContentParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;


class RenderController extends AbstractController
{
    private ContentParser $contentParser;
    private PageRepository $pageRepository;

    public function __construct(ContentParser $contentParser, PageRepository $pageRepository)
    {
        $this->contentParser = $contentParser;
        $this->pageRepository = $pageRepository;
    }

    // #[Route('/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'], priority: -100)]
    // public function renderPath(string $path = ''): Response
    // {
    //     dump('render');
    //     dd($path);
    // }




    
    ##[Route('/assets/{path}', 'asset-single', methods: ['get'])]
    #[Route('/assets/{path}', 'asset', methods: ['get'], requirements: ['path' => '.+'], priority: -200)]
    function renderAssetPath(string $path): Response
    {
        return $this->renderAsset('/assets/' . $path);
    }

    public static function getEditPathSuffix(): string
    {
        return '.__EDITING_CONCEPT__.md';
    }

    private function getEditFilePath(string $path)
    {
        $append = $this->getEditPathSuffix();

        if(str_ends_with($path, $append)) {
            return $path;
        }
        return $path . $append;
    }


    #[Route('/---cms/render/edit/{path}', 'cms.pages.render-edit', methods: ['get'], requirements: ['path' => '.+'], priority: 50)]
    public function renderEditUrl(string $path = ''): Response
    {
        dump($path);
        //dd('ok');
        
        $page = $this->pageRepository->getPage($path);
        if($page !== null) {
            if(!is_file($this->getEditFilePath($page['filePath']))) {
                copy($page['filePath'], $this->getEditFilePath($page['filePath']));
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

    function renderUrl(string $path, bool $editMode = false): Response
    {
        // Render public asset
        if($this->isAsset($path)) {
            return $this->renderAsset($path);
        }

        $page = $this->pageRepository->getPage($path);

        // Return 404 page
        if($page === null) {
            $filepath = $this->getParameter('kernel.project_dir') . '/content/pages/404.md';
            if(!is_file($filepath)) {
                $filepath = $this->getParameter('kernel.project_dir') . '/src/content/pages/404.md';
            }
        } else {
            $filepath = $page['filePath'];

            if($editMode) {
                $filepath = $this->getEditFilePath($filepath);
            }
        }

        $config = $this->getConfig();

        $nav = [];
        if(isset($config['nav'])) {
            $nav = $config['nav'];
            if(!is_array($nav)) {
                $nav = [$nav];
            }
        }

        $markdown = file_get_contents($filepath);

        // $converter = $contentParser->createConverter();
        // $result = $converter->convert($markdown);
        // $content = $result->getContent();

        $result = $this->contentParser->parse($markdown);
        $content = $result['content'];
        $page = $result['variables'];
        
        $twigVariables = [
            'page' => $page,
            'config' => $config,
        ];

        // The markdown is converted to HTML, now also render twig variables
        $twig = new TwigEnvironment(new TwigArrayLoader(['___render_template' => $content]));
        $content = $twig->render('___render_template', $twigVariables);
        
        return $this->render($page['template'], array_merge($twigVariables, [
            'content' => $content,
        ]));
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
