<?php

namespace App\Controller\Cms;

use App\Repositories\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/---cms/pages', name: 'cms.pages.')]
class PageController extends AbstractController
{
    private PageRepository $pageRepository;
    
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    #[Route('/', 'index', methods: ['get'])]
    public function index(): Response
    {
        return $this->render('@cms/pages/index.html.twig', [
            'pages' => $this->pageRepository->getPages(),
        ]);
    }

    #[Route('/show/{path}', 'show', methods: ['get'], requirements: ['path' => '.+'])]
    public function show(string $path = ''): Response
    {
        dd('cms pages show ' . $path);
    }

    #[Route('/edit/{path}', 'edit', methods: ['get'], requirements: ['path' => '.+'])]
    public function edit(string $path = ''): Response
    {
        return $this->render('@cms/pages/edit.html.twig', [
            'page' => $this->pageRepository->getPage($path, true),
        ]);
    }

    #[Route('/patch/{path}', 'post', methods: ['store'], requirements: ['path' => '.+'])]
    public function create(string $path = ''): Response
    {
        dd('cms pages create ' . $path);
    }

    #[Route('/patch/{path}', 'put', methods: ['put'], requirements: ['path' => '.+'])]
    public function put(string $path = ''): Response
    {
        dd('cms pages put (overwrite entire file) ' . $path);
    }

    #[Route('/patch/{path}', 'patch', methods: ['patch'], requirements: ['path' => '.+'])]
    public function patch(string $path = ''): Response
    {
        dd('cms pages patch (patch single item) ' . $path);
    }

    #[Route('/delete/{path}', 'delete', methods: ['delete'], requirements: ['path' => '.+'])]
    public function delete(string $path = ''): Response
    {
        dd('cms pages delete ' . $path);
    }
}
