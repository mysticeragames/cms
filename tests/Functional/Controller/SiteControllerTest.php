<?php

/*

vendor/bin/phpunit --testsuite functional --filter SiteControllerTest
vendor/bin/phpunit --testsuite functional --filter SiteControllerTest testMyMethod

*/

namespace App\Tests\Functional\Controller;

use App\Controller\Cms\SiteController;
use App\Repositories\PageRepository;
use App\Repositories\SiteRepository;
use App\Services\ContentParser;
use App\Tests\Base\BaseFunctionalTestCase;
use App\Twig\CustomTwigFunctions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DomCrawler\Crawler;

// https://symfony.com/doc/current/testing.html#application-tests

#[
    CoversClass(SiteController::class),
    UsesClass(SiteRepository::class),
    UsesClass(PageRepository::class),
    UsesClass(ContentParser::class),
    UsesClass(CustomTwigFunctions::class),
]
class SiteControllerTest extends BaseFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        //parent::setupTestContent();
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        /** @var Crawler $response */
        $response = $client->request('GET', '/sites/');

        //dd($response);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Sites');
        //$this->assertSelectorTextContains('a', 'Sites');
    }
}
