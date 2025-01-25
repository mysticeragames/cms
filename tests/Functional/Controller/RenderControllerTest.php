<?php

/*

vendor/bin/phpunit --testsuite functional --filter RenderControllerTest
vendor/bin/phpunit --testsuite functional --filter RenderControllerTest testMyMethod

*/

namespace App\Tests\Functional\Controller;

use App\Controller\Render\RenderController;
use App\Repositories\PageRepository;
use App\Services\ContentParser;
use App\Services\ContentRenderer;
use App\Services\TreeService;
use App\Services\TwigRenderer;
use App\Tests\Base\BaseFunctionalTestCase;
use App\Twig\CustomTwigFilters;
use App\Twig\CustomTwigFunctions;
use App\Twig\CustomTwigTests;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

// https://symfony.com/doc/current/testing.html#application-tests

#[
    CoversClass(RenderController::class),
    UsesClass(PageRepository::class),
    UsesClass(ContentParser::class),
    UsesClass(ContentRenderer::class),
    UsesClass(TreeService::class),
    UsesClass(TwigRenderer::class),
    UsesClass(CustomTwigFilters::class),
    UsesClass(CustomTwigFunctions::class),
    UsesClass(CustomTwigTests::class),
]
class RenderControllerTest extends BaseFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        parent::setupTestContent();
    }

    public static function additionProvider(): array
    {
        return [
            '' => [ '', 'Home Page', 'Homepage'],
            '/about' => [ '/about', 'About Page', 'About'],
            '/news' => [ '/news', '404 Not Found', 'News'],
            '/news/post 1' => [ '/news/post 1', 'Post 1', 'My Post 1'],
            '/news/post%201' => [ '/news/post%201', 'Post 1', 'My Post 1'],
        ];
    }

    #[DataProvider('additionProvider')]
    public function testIndex(string $path, string $expectedH1Text, string $expectedAText): void
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $container = self::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get('router');
        //$routeCollection = $router->getRouteCollection();

        // Be very specific about the path for this test:
        // Try: /render/site/path
        // Try: /render/site/path/
        // Try: /render/site/path?
        // Try: /render/site/path/?
        // etc...
        $suffixes = [
            '',
            '/',
            '?',
            '/?',
            '?v=1',
            '/?v=1',
            '#',
            '/#',
            '?#',
            '/?#',
            '?v=1#',
            '/?v=1#',
        ];

        foreach ($suffixes as $suffix) {
            $url = $router->generate('render', [
                'site' => $this->getTestSiteName(),
                'path' => '' // By adding it here, it will always append a slash, so for this test, don't do it...
            ]);
            $url = rtrim($url, '/') . $path . $suffix;

            /** @var Crawler $response */
            $response = $client->request('GET', $url . $suffix);

            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', $expectedH1Text);
            $this->assertAnySelectorTextContains('a', $expectedAText);
        }
    }
}
