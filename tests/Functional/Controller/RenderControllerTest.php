<?php

/*

vendor/bin/phpunit --testsuite functional --filter RenderControllerTest
vendor/bin/phpunit --testsuite functional --filter RenderControllerTest testMyMethod

*/

namespace App\Tests\Functional\Controller;

use App\Controller\Render\RenderController;
use App\Helpers\ProjectDirHelper;
use App\Tests\Base\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

// https://symfony.com/doc/current/testing.html#application-tests

##[CoversClass(RenderController::class)]
#[CoversNothing]
class RenderControllerTest extends BaseFunctionalTestCase
{
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
