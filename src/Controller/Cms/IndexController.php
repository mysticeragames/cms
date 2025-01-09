<?php

namespace App\Controller\Cms;

use App\Repositories\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', 'index', methods: ['get'])]
    public function index(): Response
    {
        return $this->render('@cms/index.html.twig');
    }

    // #[Route('/{path}', 'catchall', methods: ['get'], requirements: ['path' => '.+'], priority: -10)]
    // public function show(string $path = ''): Response
    // {
    //     dd('cms catchall ' . $path);
    // }
}
