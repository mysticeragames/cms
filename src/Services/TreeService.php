<?php

namespace App\Services;

class TreeService
{
    /**
     * @param array<int, array<string, mixed>> $pages
     */
    public function buildTree(
        array $pages,
        string $keyPath = 'path',
        string $keyBasename = 'name',
        string $keyChildren = 'children',
        bool $sort = true,
        ?string $sortBy = null,
    ): array {

        // Move index pages:
        // news/2025/index.md == news/2025/index -> move to: news/2025
        // NOTE: news/2025.md is OVERRULED by this! index comes first.
        $fixedPages = [];
        $allPaths = array_column($pages, $keyPath);
        foreach ($pages as $i => $page) {
            if ($page[$keyPath] !== 'index' && str_ends_with($page[$keyPath], '/index')) {
                $pathWithoutIndex = substr($page[$keyPath], 0, -strlen('/index'));
                if (!array_key_exists($pathWithoutIndex, $allPaths)) {
                    $page[$keyPath] = $pathWithoutIndex;
                    $fixedPages[] = $page;
                }
            } else {
                $fixedPages[] = $page;
            }
        }
        $pages = $fixedPages;


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

        // Step 2: Dynamically create missing paths
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

        if ($sort === true) {
            if ($sortBy === null || empty($sortBy)) {
                $sortBy = $keyPath;
            }
            $tree = $this->sortTreeBy($tree, $sortBy, $keyChildren);
        }

        return $tree;
    }

    public function sortTreeBy(array $tree, string $key, string $keyChildren = 'children'): array
    {
        foreach ($tree as &$page) {
            if (is_array($page[$keyChildren])) {
                $page[$keyChildren] = $this->sortTreeBy($page[$keyChildren], $key, $keyChildren);
            }
        }
        return $this->sortBy($tree, $key);
    }

    public function sortBy(array $results, string $key): array
    {
        usort($results, function ($a, $b) use ($key) {
            return strnatcasecmp((string)$a[$key], (string)$b[$key]);
            //return strcmp($a[$key], $b[$key]);
        });
        return $results;
    }
}
