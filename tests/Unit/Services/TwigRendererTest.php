<?php

/*

vendor/bin/phpunit --testsuite unit --filter TwigRendererTest
vendor/bin/phpunit --testsuite unit --filter TwigRendererTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Services\TwigRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// https://docs.phpunit.de/en/11.5/code-coverage.html#targeting-units-of-code

#[CoversClass(TwigRenderer::class)]
##[UsesClass(TwigRenderer::class)]
#class TwigRendererTest extends KernelTestCase
class TwigRendererTest extends TestCase
{
    public function testRenderBlock(): void
    {
        //self::bootKernel();

        $twigRenderer = new TwigRenderer();
        $result = $twigRenderer->renderBlock('Hi {{ name }}!', [
            'name' => 'Jon Doe'
        ]);

        $this->assertEquals('Hi Jon Doe!', $result);
    }

    public function testRenderTemplateBundleArray(): void
    {
        //self::bootKernel();

        $templateBundles = [];
        $templateBundles[] = [
            'index.html.twig' => 'Hi {{ name }}!',
        ];

        $twigRenderer = new TwigRenderer();
        $result = $twigRenderer->render($templateBundles, 'index.html.twig', [
            'name' => 'Jon Doe'
        ]);

        $this->assertEquals('Hi Jon Doe!', $result);
    }

    public function testRenderTemplateBundleArrayMultipleSources(): void
    {
        // self::bootKernel();

        $index = <<<EOD
{% extends 'layout.html.twig' %}

{% block content %}{{ name }} from {{ country }}{% endblock %}
EOD;

        $layout = <<<EOD
Welcome, {% block content %}{% endblock %}!
EOD;

        $templateBundles = [];
        $templateBundles[] = [
            'index.html.twig' => $index,
        ];
        $templateBundles[] = [
            'layout.html.twig' => $layout,
        ];

        $twigRenderer = new TwigRenderer();
        $result = $twigRenderer->render($templateBundles, 'index', [
            'name' => 'Jon Doe',
            'country' => 'the Netherlands',
        ]);

        $this->assertEquals('Welcome, Jon Doe from the Netherlands!', $result);
    }
}
