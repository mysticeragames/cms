<?php

// https://symfony.com/doc/7.3/templates.html#templates-twig-extension

namespace App\Twig;

use App\Repositories\PageRepository;
use App\Services\TreeService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomTwigFilters extends AbstractExtension
{
    public function __construct(
        private PageRepository $pageRepository,
        private array $twigVariables = []
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('tree', [$this, 'twigBuildTree']),
        ];
    }

    public function twigBuildTree(
        array $pages = [],
        string $keyPath = 'path',
        string $keyBasename = 'name',
        string $keyChildren = 'children',
        bool $sort = true,
        ?string $sortBy = null
    ): array {
        $treeService = new TreeService();

        return $treeService->buildTree($pages, $keyPath, $keyBasename, $keyChildren, $sort, $sortBy);
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
