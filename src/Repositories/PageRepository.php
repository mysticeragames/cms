<?php

namespace App\Repositories;

use App\Controller\RenderController;
use App\Services\ContentParser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PageRepository
{
    private string $projectDir;
    private string $contentPagesDirectory;
    private ContentParser $contentParser;

    public function __construct(string $projectDir, ContentParser $contentParser)
    {
        $this->projectDir = $projectDir;
        //$directory = $this->getParameter('kernel.project_dir') . '/content/pages';
        $this->contentPagesDirectory = $this->projectDir . '/content/pages';
        $this->contentParser = $contentParser;
    }

    public function getPages($path = null): array
    {
        $files = [];
        
        $finder = new Finder();
        $finder->files()->in($this->contentPagesDirectory);

        if($finder->hasResults()) {
            foreach($finder as $file) {
                if($file->isFile() && strtolower($file->getExtension()) === 'md' && !str_ends_with($file->getPathname(), RenderController::getEditPathSuffix())) {
                    $files[] = $this->parsePagePath($file);
                }
            }
        }
        return $files;
    }
    
    private function createRegexExactPath($path)
    {
        return '/^' . preg_quote($path, '/') . '$/';
    }

    private function findPageByPaths(array $searchPaths, bool $includeMarkdown = false)
    {
        foreach($searchPaths as $searchPath) {
            $finder = new Finder();
            $finder->path($this->createRegexExactPath($searchPath))->in($this->contentPagesDirectory)->files();

            if($finder->hasResults()) {
                foreach($finder as $file) {
                    return $this->parsePagePath($file, $includeMarkdown);
                }
            }
        }
        return null;
    }

    public function getPage(string $path, bool $includeMarkdown = false)
    {
        $searchPaths = [];

        if($path === null || $path === '' || rtrim($path, '/') === '' ) {
            $searchPaths[] = 'index.md';
        } elseif(str_ends_with($path, '.html')) {
            $searchPaths[] = substr($path, 0, -strlen('.html')) . '/index.md'; //  my/path.html ->   my/path/index.md
            $searchPaths[] = substr($path, 0, -strlen('.html')) . '.md'; //  my/path.html ->   my/path.md
        } else {
            $searchPaths[] = rtrim($path, '/') . '/index.md';  // my/path/ -> my/path/index.md
            $searchPaths[] = rtrim($path, '/') . '.md'; // my/path/ -> my/path.md
        }
        
        return $this->findPageByPaths($searchPaths, $includeMarkdown);
    }

    function parsePagePath(SplFileInfo $file, bool $includeMarkdown = false): array
    {
        if(is_file($file->getRealPath())) {
            $markdown = file_get_contents($file->getRealPath());
            $parsedPage = $this->contentParser->parse($markdown);
            $pageVariables = $parsedPage['variables'];

            // Generate default slug/title (overridable by the variables in the page yaml)
            $path = substr($file->getPathname(), strlen($this->contentPagesDirectory) + 1, -3);
            $parts = explode('/', $path);
            $defaultSlug = array_pop($parts);
            if($defaultSlug === 'index' && isset($parts[0])) {
                $defaultSlug = array_pop($parts);
            }

            $page = array_merge([
                'path' => $path,
                'filePath' => $file->getRealPath(),
                'createdAt' => null,
                'updatedAt' => null,
                'slug' => $defaultSlug,

                //'variables' => $result['variables'],
                //'content' => $result['content'],
            ], $pageVariables);

            $page['createdAt'] = $this->getValidDateTime($file, $page['createdAt']);
            $page['updatedAt'] = $this->getValidDateTime($file, $page['updatedAt']);
            
            if(!isset($page['title'])) {
                $page['title'] = ucfirst($page['slug']);
            }

            if($includeMarkdown) {
                $page['__markdown'] = $markdown;
            }
            return $page;
        }
        return null;
    }

    public function isValidDateString($dateString = null): bool
    {
        if(is_string($dateString) && !empty($dateString) && preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}( [0-9]{2}\:[0-9]{2}(\:[0-9]{2})?)?$/', $dateString)) {
            return true;
        }
        return false;
    }

    public function getValidDateTime(SplFileInfo $file, $overrideDateTimeString)
    {
        if($this->isValidDateString($overrideDateTimeString)) {
            return date("Y-m-d H:i:s", strtotime($overrideDateTimeString));
        }
        return date("Y-m-d H:i:s", $file->getMTime());
    }
}
