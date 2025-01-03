<?php

namespace App\Controller\Cms;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/---cms', name: 'cms.')]
class IndexController extends AbstractController
{
    #[Route('/', 'index', methods: ['get'])]
    public function index(): Response
    {
        dd('cms index');
    }

    #[Route('/{path}', 'catchall', methods: ['get'], requirements: ['path' => '.+'], priority: -10)]
    public function show(string $path = ''): Response
    {
        dd('cms catchall ' . $path);
    }
}
