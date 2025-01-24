<?php

/*

vendor/bin/phpunit --testsuite integration --filter PageRepositoryTest
vendor/bin/phpunit --testsuite integration --filter PageRepositoryTest testMyMethod

*/

namespace App\Tests\Integration\Repositories;

use App\Helpers\DateHelper;
use App\Repositories\PageRepository;
use App\Tests\Base\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;

##[CoversClass(PageRepository::class)]
#[CoversNothing]
class PageRepositoryTest extends BaseIntegrationTestCase
{
    protected PageRepository $pageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        parent::setupTestContent();
        $this->pageRepository = self::getContainer()->get(PageRepository::class);
    }

    public function testGetPages(): void
    {
        $pages = $this->pageRepository->getPages($this->getTestSiteName());

        $dateHelper = new DateHelper();

        foreach ($pages as $page) {
            $this->assertTrue($dateHelper->isValidDate($page['createdAt']));
            $this->assertTrue($dateHelper->isValidDate($page['updatedAt']));
        }

        $about = $pages[0];
        $index = $pages[1];
        $post = $pages[3];
        $another = $pages[2];

        //dd($this->getTestSiteRootPath());
        foreach (
            [
            "path" => "about",
            "name" => "about",
            "filePath" => $this->getTestSiteRootPath() . '/pages/about.md',
            "slug" => "about",
            "title" => "About",
            ] as $key => $value
        ) {
            $this->assertEquals($value, $about[$key], $key);
        }

        foreach (
            [
            "path" => "index",
            "name" => "index",
            "filePath" => $this->getTestSiteRootPath() . '/pages/index.md',
            "slug" => "index",
            "title" => "Home",
            ] as $key => $value
        ) {
            $this->assertEquals($value, $index[$key], $key);
        }

        foreach (
            [
            "path" => "news/post 1",
            "name" => "post 1",
            "filePath" => $this->getTestSiteRootPath() . '/pages/news/post 1.md',
            "slug" => "post 1",
            "title" => "My Post",
            ] as $key => $value
        ) {
            $this->assertEquals($value, $post[$key], $key);
        }

        foreach (
            [
            "path" => "news/another",
            "name" => "another",
            "filePath" => $this->getTestSiteRootPath() . '/pages/news/another.md',
            "slug" => "another",
            "title" => "another-page",
            ] as $key => $value
        ) {
            $this->assertEquals($value, $another[$key], $key);
        }
    }

    public function testTest(): void
    {
        $this->assertNotEmpty($this->getProjectDir());
    }
}
