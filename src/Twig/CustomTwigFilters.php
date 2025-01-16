<?php

// https://symfony.com/doc/7.3/templates.html#templates-twig-extension

namespace App\Twig;

use App\Services\TreeService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomTwigFilters extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('tree', [$this, 'twigBuildTree']),
            new TwigFilter('push', [$this, 'twigPush']),
        ];
    }

    public function twigPush(mixed $array, mixed $value): array
    {
        if ($array === null) {
            return [$value];
        }
        $array = (array) $array;
        $array[] = $value;

        return $array;
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
}
