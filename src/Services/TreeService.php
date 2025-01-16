<?php

namespace App\Services;

class TreeService
{
    /**
     * @props array<array<mixed>> $pages
     */
    public function buildTree(
        array $pages,
        string $keyPath = 'path',
        string $keyBasename = 'name',
        string $keyChildren = 'children',
        bool $sort = true,
        ?string $sortBy = null,
        bool $createParents = false,
    ): array {
        $tree = [];
        $pathMap = [];

        if ($sort === true) {
            if ($sortBy === null || empty($sortBy)) {
                $sortBy = $keyPath;
            }
            $pages = $this->sortBy($pages, $sortBy);
        }

        $availableKeys = [];

        // Step 1: Build a map of paths for quick access
        foreach ($pages as $page) {
            $availableKeys = array_merge($availableKeys, array_flip(array_keys($page)));

            $pathMap[$page[$keyPath]] = array_merge(
                $page,
                [
                $keyPath => $page[$keyPath],
                $keyBasename => basename($page[$keyPath]),
                $keyChildren => [],
                ]
            );
        }

        if (isset($availableKeys[$keyPath])) {
            unset($availableKeys[$keyPath]);
        }
        if (isset($availableKeys[$keyBasename])) {
            unset($availableKeys[$keyBasename]);
        }
        foreach ($availableKeys as $availableKey => $availableValue) {
            $availableKeys[$availableKey] = null;
        }

        // Step 2: Dynamically create missing parent paths
        foreach ($pages as $page) {
            $parts = explode('/', $page[$keyPath]);
            $currentPath = '';

            for ($i = 0; $i < count($parts) - 1; $i++) {
                $currentPath .= ($i > 0 ? '/' : '') . $parts[$i];

                if (!isset($pathMap[$currentPath])) {
                    $pathMap[$currentPath] = array_merge($availableKeys, [
                        $keyPath => $currentPath,
                        $keyBasename => $parts[$i],
                        $keyChildren => [],
                    ]);
                }
            }
        }

        // Step 3: Build the tree structure
        foreach ($pathMap as $path => &$node) {
            $parts = explode('/', $path);
            array_pop($parts); // Remove the last part to get the parent path
            $parentPath = implode('/', $parts);

            // Add to the root if no parent, or attach to its parent's 'children'
            if ($parentPath === '' || !isset($pathMap[$parentPath])) {
                $tree[] = &$node;
            } else {
                $pathMap[$parentPath][$keyChildren][] = &$node;
            }
        }

        return $tree;
    }

    public function sortBy(array $results, string $key): array
    {
        usort($results, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });
        return $results;
    }
}
