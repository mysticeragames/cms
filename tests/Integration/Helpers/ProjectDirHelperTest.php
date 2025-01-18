<?php

/*

vendor/bin/phpunit --testsuite integration --filter ProjectDirHelperTest
vendor/bin/phpunit --testsuite integration --filter ProjectDirHelperTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Helpers\ProjectDirHelper;
use App\Tests\Base\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectDirHelper::class)]
class ProjectDirHelperTest extends BaseIntegrationTestCase
{
    public function testValidProjectDir(): void
    {
        $realProjectDir = self::getContainer()->getParameter('kernel.project_dir');

        $helperProjectDir = ProjectDirHelper::getProjectDir();

        $this->assertNotEmpty($realProjectDir);
        $this->assertEquals(
            $realProjectDir,
            $helperProjectDir
        );
    }
}
