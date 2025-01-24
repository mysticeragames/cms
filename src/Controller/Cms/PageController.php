<?php

namespace App\Controller\Cms;

use App\Repositories\PageRepository;
use App\Repositories\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sites/{site}/pages', name: 'cms.pages.')]
class PageController extends AbstractController
{
    private PageRepository $pageRepository;
    private SiteRepository $siteRepository;

    public function __construct(PageRepository $pageRepository, SiteRepository $siteRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->siteRepository = $siteRepository;
    }

    #[Route('/', 'index', methods: ['get'])]
    public function index(Request $request, string $site): Response
    {
        //dd($site);
        //$site = $request->get('site');

        $this->siteRepository->ensureValid($site);

        return $this->render('@cms/pages/index.html.twig', [
            'site' => $site,
            'pages' => $this->pageRepository->getPages($site),
        ]);
    }

    #[Route('/show/{path}', 'show', methods: ['get'], requirements: ['path' => '.+'])]
    public function show(string $path = ''): Response
    {
        dd('cms pages show ' . $path);
    }

    #[Route('/', 'create', methods: ['post'])]
    public function create(string $site): Response
    {
        $request = Request::createFromGlobals();
        $path = $request->get('path');

        $this->pageRepository->create($site, $path);

        return $this->redirectToRoute('cms.pages.index', ['site' => $site]);
    }

    #[Route('/destroy', 'destroy', methods: ['post'])]
    public function destroy(string $site): Response
    {
        $request = Request::createFromGlobals();
        $path = $request->get('path');

        $this->pageRepository->remove($site, $path);

        return $this->redirectToRoute('cms.pages.index', ['site' => $site]);
    }

    #[Route('/edit/{path}', 'edit', methods: ['get'], requirements: ['path' => '.+'])]
    public function edit(Request $request, string $path = ''): Response
    {
        $site = $request->get('site');

        return $this->render('@cms/pages/edit.html.twig', [
            'site' => $site,
            'page' => $this->pageRepository->getPage($site, $path, true),
        ]);
    }

    // #[Route('/patch/{path}', 'post', methods: ['store'], requirements: ['path' => '.+'])]
    // public function create(string $path = ''): Response
    // {
    //     dd('cms pages create ' . $path);
    // }

    // #[Route('/patch/{path}', 'put', methods: ['put'], requirements: ['path' => '.+'])]
    // public function put(string $path = ''): Response
    // {
    //     dd('cms pages put (overwrite entire file) ' . $path);
    // }

    // #[Route('/patch/{path}', 'patch', methods: ['patch'], requirements: ['path' => '.+'])]
    // public function patch(string $path = ''): Response
    // {
    //     dd('cms pages patch (patch single item) ' . $path);
    // }

    // #[Route('/delete/{path}', 'delete', methods: ['delete'], requirements: ['path' => '.+'])]
    // public function delete(string $path = ''): Response
    // {
    //     dd('cms pages delete ' . $path);
    // }
}
