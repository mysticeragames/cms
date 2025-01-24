<?php

/*

vendor/bin/phpunit --testsuite functional --filter SiteControllerTest
vendor/bin/phpunit --testsuite functional --filter SiteControllerTest testMyMethod

*/

namespace App\Tests\Functional\Controller;

use App\Controller\Cms\SiteController;
use App\Tests\Base\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\DomCrawler\Crawler;

// https://symfony.com/doc/current/testing.html#application-tests

##[CoversClass(SiteController::class)]
#[CoversNothing]
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
        $response = $client->request('GET', '/');

        //dd($response);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CMS');
        $this->assertSelectorTextContains('a', 'Sites');
    }
}
