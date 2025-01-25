<?php

/*

vendor/bin/phpunit --testsuite unit --filter ProjectDirHelperTest
vendor/bin/phpunit --testsuite unit --filter ProjectDirHelperTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Helpers;

use App\Helpers\ProjectDirHelper;
use App\Tests\Base\BaseUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectDirHelper::class)]
class ProjectDirHelperTest extends BaseUnitTestCase
{
    public function testValidProjectDir(): void
    {
        $expected = dirname(dirname(dirname(__DIR__)));
        $actual = $this->getProjectDir();

        // If this test ever fails (because it's moved), make sure the $expected really is the root of the project!
        //dd($realProjectDir, $helperProjectDir);

        $this->assertNotEmpty($expected);
        $this->assertEquals($expected, $actual);
    }
}
