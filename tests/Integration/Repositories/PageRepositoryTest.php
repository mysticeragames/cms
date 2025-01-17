<?php

/*

vendor/bin/phpunit --testsuite integration --filter PageRepositoryTest
vendor/bin/phpunit --testsuite integration --filter PageRepositoryTest testMyMethod

*/

namespace App\Tests\Integration\Repositories;

use App\Repositories\PageRepository;
use App\Tests\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageRepository::class)]
class PageRepositoryTest extends BaseKernelTestCase
{
    public function testGetPages(): void
    {
        $pages = $this->pageRepository->getPages($this->site);

        foreach ($pages as $page) {
            $this->assertTrue($this->dateHelper->isValidDate($page['createdAt']));
            $this->assertTrue($this->dateHelper->isValidDate($page['updatedAt']));
        }

        $about = $pages[0];
        $index = $pages[1];
        $post = $pages[3];
        $another = $pages[2];

        foreach (
            [
            "path" => "about",
            "name" => "about",
            "filePath" => $this->getSiteRootPath() . '/pages/about.md',
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
            "filePath" => $this->getSiteRootPath() . '/pages/index.md',
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
            "filePath" => $this->getSiteRootPath() . '/pages/news/post 1.md',
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
            "filePath" => $this->getSiteRootPath() . '/pages/news/another.md',
            "slug" => "another",
            "title" => "another-page",
            ] as $key => $value
        ) {
            $this->assertEquals($value, $another[$key], $key);
        }
    }
}
