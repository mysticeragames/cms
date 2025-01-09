<?php

namespace App\Services;

use App\Repositories\PageRepository;

class ContentRenderer
{
    private PageRepository $pageRepository;
    private ContentParser $contentParser;

    public function __construct(PageRepository $pageRepository, ContentParser $contentParser)
    {
        $this->pageRepository = $pageRepository;
        $this->contentParser = $contentParser;
    }

    public function render(string $projectDir, string $path, bool $editMode = false): string
    {
        $twigRenderer = new TwigRenderer();

        $page = $this->pageRepository->getPage($path);

        // Return 404 page
        if($page === null) {
            $filepath = $projectDir . '/content/pages/404.md';
            if(!is_file($filepath)) {
                $filepath = $projectDir . '/src/content/pages/404.md';
            }
        } else {
            $filepath = $page['filePath'];

            if($editMode) {
                if(!str_ends_with($filepath, PageRepository::EDIT_PATH_SUFFIX)) {
                    $filepath = $filepath . PageRepository::EDIT_PATH_SUFFIX;
                }
            }
        }

        //$config = $this->getConfig();
        $config = [];
        $configPath = $projectDir . '/content/config.md';
        if(file_exists($configPath)) {
            $markdown = file_get_contents($configPath);
            $config = $this->contentParser->parse($markdown)['variables'];
        }
        
        $nav = [];
        if(isset($config['nav'])) {
            $nav = $config['nav'];
            if(!is_array($nav)) {
                $nav = [$nav];
            }
        }

        $markdown = file_get_contents($filepath);

        // $converter = $contentParser->createConverter();
        // $result = $converter->convert($markdown);
        // $content = $result->getContent();

        $result = $this->contentParser->parse($markdown);
        $content = $result['content'];
        $page = $result['variables'];
        
        $twigVariables = [
            'page' => $page,
            'config' => $config,
        ];

        // The markdown is converted to HTML, now also render twig variables
        //$twig = new TwigEnvironment(new TwigArrayLoader(['___render_template' => $content]));
        //$content = $twig->render('___render_template', $twigVariables);

        $twigRenderer = new TwigRenderer();
        $content = $twigRenderer->renderBlock($content, $twigVariables);

        $renderBundles = [
            $projectDir . '/content/pages',
            $projectDir . '/src/templates/front',
        ];

        return $twigRenderer->render(
            $renderBundles,
            $page['template'],
            array_merge($twigVariables, [
                'content' => $content,
            ])
        );
    }
}