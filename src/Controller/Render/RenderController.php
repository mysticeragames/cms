<?php

namespace App\Controller\Render;

use App\Repositories\PageRepository;
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

    #[Route('/{site}/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'])]
    public function renderPage(string $site, string $path, bool $editMode = false): Response
    {
        if (str_starts_with($path, 'assets/')) {
            $assetPath =  $this->getParameter('kernel.project_dir') . '/content/src/public/' . $path;
            if (!file_exists($assetPath)) {
                return $this->render404NotFoundHeaderOnly();
            }
            return $this->renderAsset($path);
        }

        $content = $this->contentRenderer->render($this->getParameter('kernel.project_dir'), $site, $path, $editMode);

        return new Response($content);
    }

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

        return $this->renderPage($site, $path, true);
    }

    private function renderAsset(string $filepath): Response
    {
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

    private function render404NotFoundHeaderOnly(): Response
    {
        return new Response(
            '',
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/plain'],
        );
    }
}
