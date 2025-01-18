<?php

/*

vendor/bin/phpunit --testsuite functional --filter RenderControllerTest
vendor/bin/phpunit --testsuite functional --filter RenderControllerTest testMyMethod

*/

namespace App\Tests\Functional\Controller;

use App\Controller\Render\RenderController;
use App\Tests\Base\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

// https://symfony.com/doc/current/testing.html#application-tests

#[CoversClass(RenderController::class)]
class RenderControllerTest extends BaseFunctionalTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $container = self::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get('router');
        //$routeCollection = $router->getRouteCollection();

        $url = $router->generate('render', ['site' => $this->getTestSiteName(), 'path' => '']);
        //$this->assertEquals('/render/phpunit-test-site/', $url);

        /** @var Crawler $response */
        $response = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Home Page');
        $this->assertAnySelectorTextContains('a', 'Homepage');
    }
}
