<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RenderController extends AbstractController
{
    #[Route('/{path}', 'render', methods: ['get'], requirements: ['path' => '.+'], priority: -100)]
    public function renderPath(string $path = ''): Response
    {
        dump('render');
        dd($path);
    }
}
