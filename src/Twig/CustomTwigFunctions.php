<?php

// https://symfony.com/doc/7.3/templates.html#templates-twig-extension

namespace App\Twig;

use App\Repositories\PageRepository;
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
            new TwigFunction('pages', [$this, 'twigGetPages']),
            new TwigFunction('childPages', [$this, 'twigGetChildPages']),
        ];
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
                $childPages[] = $p;
            }
        }
        return $childPages;
    }
}
