<?php

namespace App\Services;

use App\Repositories\PageRepository;
use App\Twig\CustomTwigFilters;
use App\Twig\CustomTwigFunctions;
use Symfony\Component\Yaml\Yaml;

class ContentRenderer
{
    private PageRepository $pageRepository;
    private ContentParser $contentParser;

    public function __construct(PageRepository $pageRepository, ContentParser $contentParser)
    {
        $this->pageRepository = $pageRepository;
        $this->contentParser = $contentParser;
    }

    public function render(string $projectDir, string $site, string $path, bool $editMode = false): string
    {
        $twigRenderer = new TwigRenderer();

        $page = $this->pageRepository->getPage($site, $path);

        // Return 404 page
        if ($page === null) {
            $filepath = $projectDir . '/content/pages/404.md';
            if (!is_file($filepath)) {
                $filepath = $projectDir . '/default-content/pages/404.md';
            }
        } else {
            $filepath = $page['filePath'];

            if ($editMode) {
                if (!str_ends_with($filepath, PageRepository::EDIT_PATH_SUFFIX)) {
                    $filepath = $filepath . PageRepository::EDIT_PATH_SUFFIX;
                }
            }
        }

        // to array:
        // $value = Yaml::parseFile('/path/to/file.yaml');
        // from array:
        // $yaml = Yaml::dump($array);

        $config = [
            'site' => [
                'slug' => $site,
            ]
        ];
        $defaultConfigPath = "$projectDir/default-content/config.yml";
        $config = array_replace_recursive($config, (array)Yaml::parseFile($defaultConfigPath));

        $siteConfigPath = "$projectDir/content/src/$site/config.yml";
        if (file_exists($siteConfigPath)) {
            $config = array_replace_recursive($config, (array)Yaml::parseFile($siteConfigPath));
        }

        //$yaml = Yaml::dump($array);

        $nav = [];
        if (isset($config['nav'])) {
            $nav = $config['nav'];
            if (!is_array($nav)) {
                $nav = [$nav];
            }
        }

        $markdown = file_get_contents($filepath);

        // $converter = $contentParser->createConverter();
        // $result = $converter->convert($markdown);
        // $content = $result->getContent();

        $result = $this->contentParser->parse($markdown);
        $content = $result['content'];
        $pageVariables = $result['variables'];

        // Merge default variables from config
        if (isset($config['page']) && is_array($config['page'])) {
            $pageVariables = array_replace_recursive($config['page'], $pageVariables);
        }
        $siteVariables = [];
        if (isset($config['site']) && is_array($config['site'])) {
            $siteVariables = array_replace_recursive($config['site'], $siteVariables);
        }

        $twigVariables = [
            'site' => $siteVariables,
            'page' => $pageVariables,
            'config' => $config,
            'pagePath' => $path,
        ];

        // The markdown is converted to HTML, now also render twig variables
        //$twig = new TwigEnvironment(new TwigArrayLoader(['___render_template' => $content]));
        //$content = $twig->render('___render_template', $twigVariables);

        //dd($content, $twigVariables);

        $twigExtensions = [];
        $twigExtensions[] = new CustomTwigFunctions(
            $this->pageRepository,
            $twigVariables
        );
        $twigExtensions[] = new CustomTwigFilters(
            $this->pageRepository,
            $twigVariables
        );

        if (isset($pageVariables['twig']) && $pageVariables['twig'] === true) {
            $twigRenderer = new TwigRenderer();
            $content = $twigRenderer->renderBlock($content, $twigVariables, $twigExtensions);
        }
        $twigVariables['content'] = $content;


        $renderBundles = [];

        // Site templates (overrides the theme)
        $siteDirTemplates = "$projectDir/content/$site/templates";
        if (is_dir($siteDirTemplates)) {
            $renderBundles[] = $siteDirTemplates;
        }

        // Theme templates (overrides default templates)
        $theme = false;
        if (isset($page['theme'])) {
            $theme = $page['theme'];
        } elseif (isset($config['theme'])) {
            $theme = $config['theme'];
        }
        if (!empty($theme)) {
            $themeDir = "$projectDir/content/$site/themes/$theme";
            if (is_dir($themeDir)) {
                $renderBundles[] = $themeDir;
            }
        }

        // Default fallback templates
        $renderBundles[] = "$projectDir/default-content/templates";

        return $twigRenderer->render(
            $renderBundles,
            $pageVariables['template'],
            $twigVariables,
            $twigExtensions
        );
    }
}
