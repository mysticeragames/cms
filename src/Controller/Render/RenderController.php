<?php

namespace App\Controller\Render;

use App\Repositories\PageRepository;
use App\Services\ContentParser;
use App\Services\ContentRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/render', priority: -100)]
class RenderController extends AbstractController
{
    private PageRepository $pageRepository;
    private ContentRenderer $contentRenderer;

    public function __construct(
        PageRepository $pageRepository,
        ContentRenderer $contentRenderer
    ) {
        $this->pageRepository = $pageRepository;
        $this->contentRenderer = $contentRenderer;
    }

    // #[Route('/assets/{path}', 'asset', methods: ['get'], requirements: ['path' => '.+'], priority: -200)]
    // function renderAssetPath(string $site, string $path): Response
    // {
    //     return $this->renderAsset('/assets/' . $path);
    // }

    #[Route(
        '/---cms/render-edit/{path}',
        'cms.pages.render-edit',
        methods: ['get'],
        requirements: ['path' => '.+'],
        priority: 50
    )]
    public function renderEditUrl(string $site, string $path = ''): Response
    {
        dump('EDITMODE --- ' . $path);

        $page = $this->pageRepository->getPage($site, $path);
        if ($page !== null) {
            $editFilepath = $page['filePath'];
            if (!str_ends_with($editFilepath, PageRepository::EDIT_PATH_SUFFIX)) {
                $editFilepath = $editFilepath . PageRepository::EDIT_PATH_SUFFIX;
            }
            if (!is_file($editFilepath)) {
                copy($page['filePath'], $editFilepath);
            }
        }

        return $this->renderUrl($site, $path, true);
    }

    ##[Route('/{site}/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'], priority: -100)]
    #[Route('/{site}/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'])]
    public function runUrl(string $site, string $path = ''): Response
    {
        return $this->renderUrl($site, $path);
    }

    private function render404NotFoundHeaderOnly(): Response
    {
        return new Response(
            '',
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/plain'],
        );
    }

    private function isAsset(string $path): bool
    {
        if (str_starts_with($path, 'assets/')) {
            return true;
        }

        $pathinfo = pathinfo($path);
        if (isset($pathinfo['extension']) && strtolower($pathinfo['extension']) !== 'html') {
            return true;
        }

        return false;
    }

    private function renderAsset(string $path): Response
    {
        $filepath = $this->getParameter('kernel.project_dir') . '/content/public/' . $path;
        if (!is_file($filepath)) {
            return $this->render404NotFoundHeaderOnly();
        }

        $mimeType = null;

        $pathinfo = pathinfo($filepath);
        if (isset($pathinfo['extension'])) {
            $mimeTypes = (new MimeTypes())->getMimeTypes($pathinfo['extension']);
            if (!empty($mimeTypes)) {
                $mimeType = array_shift($mimeTypes);
            }
        }
        if (empty($mimeType)) {
            $mimeType = 'text/plain';
        }

        $response = new Response();
        $response->setContent(file_get_contents($filepath));
        $response->headers->set('Content-type', $mimeType);

        return $response;
    }

    public function renderUrl(string $site, string $path, bool $editMode = false): Response
    {
        // Render public asset
        if ($this->isAsset($path)) {
            return $this->renderAsset($path);
        }

        $content = $this->contentRenderer->render($this->getParameter('kernel.project_dir'), $site, $path, $editMode);

        return new Response($content);
    }

    // function scanPageFiles(): array
    // {
    //     $files = [];
    //     $directory = $this->getParameter('kernel.project_dir') . '/content/pages';

    //     foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator(
    //         $directory, RecursiveDirectoryIterator::SKIP_DOTS ) ) as $file ) {
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
