<?php

namespace App\Services;

// https://commonmark.thephpleague.com/2.6/extensions/front-matter/
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;

class ContentParser
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = $this->createConverter();
    }

    public function parse(string $markdown): array
    {
        $result = $this->converter->convert($markdown);
        $content = $result->getContent();
        $variables = [];

        if ($result instanceof RenderedContentWithFrontMatter) {
            $variables = $result->getFrontMatter();
        }

        $variables = array_merge([
            'layout' => 'layout.html.twig',
            'template' => 'default.html.twig',
            //'updatedAt' => date("Y-m-d H:i:s", filemtime($filepath))
        ], $variables);
        
        if(!str_ends_with($variables['layout'], '.html.twig')) {
            $variables['layout'] .= '.html.twig';
        }

        if(!str_ends_with($variables['template'], '.html.twig')) {
            $variables['template'] .= '.html.twig';
        }

        return [
            'content' => $content,
            'variables' => $variables,
        ];
    }

    public function getConverter()
    {
        return $this->converter;
    }

    private function createConverter($config = [])
    {
        // Configure the Environment with all the CommonMark parsers/renderers
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        // Add the extension
        $environment->addExtension(new FrontMatterExtension());

        // Instantiate the converter engine and start converting some Markdown!
        return new MarkdownConverter($environment);
    }
}
