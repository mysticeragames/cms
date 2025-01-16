<?php

namespace App\Services;

use Exception;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class TwigRenderer
{
    /**
     * @param array<int, AbstractExtension> $extensions
     */
    public function renderBlock(string $content, array $variables, array $extensions = []): string
    {
        return $this->render(
            templateBundles: ['template.html.twig' => $content],
            template: 'template.html.twig',
            variables: $variables,
            extensions: $extensions
        );
    }

    /**
     * @param array<int, AbstractExtension> $extensions
     */
    public function render(
        array $templateBundles,
        string $template,
        array $variables = [],
        array $extensions = []
    ): string {
        if (count($templateBundles) === 0) {
            throw new Exception('No template bundles found');
        }

        $twig = $this->createEnvironment($templateBundles, $extensions);

        if (!str_ends_with($template, '.html.twig')) {
            $template .= '.html.twig';
        }

        return $twig->render($template, $variables);
    }

    /**
     * Create an environment from array template bundles
     *
     * See: https://twig.symfony.com/doc/3.x/api.html#twig-loader-chainloader
     *
     * Usage, array based:
     * createEnvironment([
     *   [ // custom override templates
     *     'base.html.twig' => '{% block content %}{% endblock %}',
     *   ],
     *   [ // theme templates
     *     'index.html.twig' => '{% extends "base.html.twig" %}{% block content %}Hello {{ name }}{% endblock %}',
     *     'base.html.twig'  => 'Will never be loaded',
     *   ],
     *   [ // default (fallback) templates
     *     'base.html.twig'  => 'Will never be loaded',
     *     '404.html.twig'  => '404 Not found',
     *   ],
     *   '403.html.twig'  => '403 Forbidden',
     * ]);
     *
     * Or, file based:
     * createEnvironment([
     *   '/absolute/folder/site/templates',
     *   '/absolute/folder/theme/templates',
     *   '/absolute/folder/default/templates',
     * ]);
     *
     * @param array<mixed> $templateBundles
     * @return Environment
     */
    public function createEnvironment(array $templateBundles, array $extensions): Environment
    {
        $loaders = [];

        foreach ($templateBundles as $index => $templateBundle) {
            if (!is_array($templateBundle)) {
                if (is_numeric($index)) {
                    // Numeric index: path
                    $loaders[] = new FilesystemLoader($templateBundle);
                } else {
                    // 'template' => '403.html.twig'
                    $loaders[] = new ArrayLoader([$index => $templateBundle]);
                }
            } else {
                if (isset($templateBundle[0])) {
                    // Numeric index: path
                    $loaders[] = new FilesystemLoader($templateBundle);
                } else {
                    // The key defines the template path ('base.html.twig' => '{% block content %}{% endblock %}')
                    $loaders[] = new ArrayLoader($templateBundle);
                }
            }
        }

        $loader = new ChainLoader($loaders);
        $environment = new Environment($loader, [
            'debug' => true,
        ]);
        $environment->addExtension(new \Twig\Extension\DebugExtension());
        foreach ($extensions as $extension) {
            $environment->addExtension($extension);
        }
        return $environment;
    }
}
