<?php

namespace App\Controller\Cms;

use App\Entity\GitCommit;
use App\Entity\GitRemoteEntity;
use App\Form\Type\GitCommitType;
use App\Form\Type\GitRemoteEntityType;
use App\Models\GitContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', 'index', methods: ['get', 'post'])]
    public function index(Request $request, GitContent $gitContent): Response
    {
        $gitCommit = new GitCommit();
        $gitCommitForm = $this->createForm(GitCommitType::class, $gitCommit);
        $gitCommitForm->handleRequest($request);
        if ($gitCommitForm->isSubmitted() && $gitCommitForm->isValid()) {
            $data = $gitCommitForm->getData();

            $gitContent->addCommitPush($data->getCommitMessage());

            return $this->redirectToRoute('index');
        }

        $gitRemote = new GitRemoteEntity();
        $gitRemote->setUrl($gitContent->getRemote());

        $gitRemoteForm = $this->createForm(GitRemoteEntityType::class, $gitRemote);

        $gitRemoteForm->handleRequest($request);
        if ($gitRemoteForm->isSubmitted() && $gitRemoteForm->isValid()) {
            $data = $gitRemoteForm->getData();

            $gitContent->destroy(true);
            $gitContent->clone($data->getUrl());
            //$gitContent->setRemote($data->getUrl());

            return $this->redirectToRoute('index');
        }
        // $form = $this->createFormBuilder($gitRemote)
        //     ->add('url', TextType::class)
        //     ->add('save', SubmitType::class, ['label' => 'Connect'])
        //     ->getForm();

        return $this->render('@cms/index.html.twig', [
            'remote' => $gitContent->getRemote(),
            'flatChanges' => $gitContent->flatChanges(),
            'gitRemoteForm' => $gitRemoteForm,
            'gitCommitForm' => $gitCommitForm,
        ]);
    }

    // #[Route('/{path}', 'catchall', methods: ['get'], requirements: ['path' => '.+'], priority: -10)]
    // public function show(string $path = ''): Response
    // {
    //     dd('cms catchall ' . $path);
    // }
}
