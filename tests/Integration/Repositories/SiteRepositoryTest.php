<?php

/*

vendor/bin/phpunit --testsuite integration --filter SiteRepositoryTest
vendor/bin/phpunit --testsuite integration --filter SiteRepositoryTest testMyMethod

*/

namespace App\Tests\Integration\Repositories;

use App\Helpers\ProjectDirHelper;
use App\Repositories\SiteRepository;
use App\Tests\Base\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[
    CoversClass(SiteRepository::class),
    UsesClass(ProjectDirHelper::class),
]
class SiteRepositoryTest extends BaseIntegrationTestCase
{
    protected SiteRepository $siteRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteRepository = self::getContainer()->get(SiteRepository::class);
    }

    public function testGetSites(): void
    {
        $this->siteRepository->create('My Site 1');
        $this->siteRepository->create('My Site 2');

        $sites = $this->siteRepository->getSites();
        $this->assertCount(2, $sites);

        $names = array_column($sites, 'path');
        $this->assertTrue(in_array('My Site 1', $names));
        $this->assertTrue(in_array('My Site 2', $names));
    }

    public function testCreateSite(): void
    {
        $siteName = 'My Test Site';

        $result = $this->siteRepository->create($siteName);

        $this->assertTrue($result);
        $this->assertTrue(is_dir(ProjectDirHelper::getProjectDir() . '/content/src/My Test Site/pages'));
    }

    public function testRemoveSite(): void
    {
        $siteName = 'My Test Site';
        $this->siteRepository->create($siteName);

        $result = $this->siteRepository->remove($siteName);

        $this->assertTrue($result);
        $this->assertTrue(! is_dir(ProjectDirHelper::getProjectDir() . '/content/src/My Test Site'));
    }
}
