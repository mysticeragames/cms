<?php

// https://symfony.com/doc/7.3/templates.html#templates-twig-extension

namespace App\Twig;

use App\Repositories\PageRepository;
use Symfony\Component\Filesystem\Path;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomTwigFunctions extends AbstractExtension
{
    public function __construct(
        private PageRepository $pageRepository,
        private array $twigVariables = []
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('staticPages', [$this, 'twigGetPages']),
            new TwigFunction('staticChildPages', [$this, 'twigGetChildPages']),
            new TwigFunction('staticPath', [$this, 'twigPath']),
        ];
    }

    public function twigPath(mixed $path = null): string
    {
        if ($path === null) {
            $path = '';
        }

        // $relativePath = $filesystem->makePathRelative(
        //     '/render/demo-site/' . $this->twigVariables['site']['slug'] . '/' . $path,
        //     '/render/demo-site/' . $this->twigVariables['site']['slug'] . '/' . $this->twigVariables['pagePath']
        // );

        // $relativePath = $filesystem->makePathRelative(
        //     '/a/b/' . $this->twigVariables['site']['slug'] . '/' . $path,
        //     '/a/b/' . $this->twigVariables['site']['slug'] . '/' . $this->twigVariables['pagePath']
        // );

        $absolutePath = Path::makeAbsolute(
            $path,
            '/render//' . $this->twigVariables['site']['slug'] . '/'
        );

        // $currentAbsolutePath = Path::makeAbsolute(
        //     $this->twigVariables['pagePath'],
        //     '/render//' . $this->twigVariables['site']['slug'] . '/'
        // );

        // $filesystem = new Filesystem();
        // $relativePath = $filesystem->makePathRelative(
        //     $absolutePath,
        //     $currentAbsolutePath,
        // );
        //return rtrim($relativePath, '/');
        return $absolutePath;
    }

    public function twigGetPages(mixed $site = null): array
    {
        if ($site === null || ! is_string($site)) {
            $site = $this->twigVariables['site']['slug'];
        }

        return $this->pageRepository->getPages($site);
    }

    public function twigGetChildPages(mixed $page = null, mixed $site = null): array
    {
        if ($site === null || ! is_string($site)) {
            $site = $this->twigVariables['site']['slug'];
        }

        if ($page === null || ! is_string($page)) {
            $page = $this->twigVariables['pagePath'];
        }

        $pages = $this->pageRepository->getPages($site);

        $childPages = [];
        foreach ($pages as $p) {
            if (
                str_starts_with($p['path'], $page)
                && $p['path'] !== $page
            ) {
                // Create relative path
                //$p['pathOriginal'] = $p['path'];
                //$p['path'] = substr($p['path'], strlen($page));
                $p['childpath'] = substr($p['path'], strlen($page));
                $childPages[] = $p;
            }
        }
        return $childPages;
    }
}
