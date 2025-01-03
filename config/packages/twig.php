<?php

// https://symfony.com/doc/current/reference/configuration/twig.html

// config/packages/twig.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {

    $projectDir = dirname(dirname(__DIR__));

    $twig->fileNamePattern([
        '*.twig',
    ]);

    // Don't throw errors when a variable does not exist
    $twig->strictVariables(false);

    if(is_dir($projectDir . '/content/templates')) {
        $twig->defaultPath('%kernel.project_dir%/content/templates');

        // TODO: Find out which theme is being used, and add the path as first priority

        $twig->path('content/templates', null);

    } else {
        $twig->defaultPath('%kernel.project_dir%/src/templates');
    }

    // Default paths
    $twig->path('src/templates/front', null);
    $twig->path('src/templates/cms', 'cms');
};
