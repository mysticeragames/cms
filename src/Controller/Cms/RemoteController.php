<?php

namespace App\Controller\Cms;

use App\Models\GitContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/remote', name: 'cms.remote.')]
class RemoteController extends AbstractController
{
    private GitContent $gitContent;

    public function __construct(GitContent $gitContent)
    {
        $this->gitContent = $gitContent;
    }

    #[Route('/connect', 'connect', methods: ['post'])]
    public function connect(Request $request): Response
    {
        $remoteUrl = $request->get('remote-url');

        $this->gitContent->clone($remoteUrl);
        //$this->gitContent->setRemote($remoteUrl);

        // dd('TODO: disconnect remote');
        return $this->redirectToRoute('index');
    }

    #[Route('/disconnect', 'disconnect', methods: ['post'])]
    public function disconnect(): Response
    {
        //$this->gitContent->disconnectRemote();
        $this->gitContent->destroy(true);

        // dd('TODO: disconnect remote');
        return $this->redirectToRoute('index');
    }
}
