<?php

/*

vendor/bin/phpunit --testsuite integration --filter KernelTest
vendor/bin/phpunit --testsuite integration --filter KernelTest testMyMethod

*/

namespace App\Tests\Integration;

use App\Kernel;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Kernel::class)]
class KernelTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testProjectDir(): void
    {
        $this->assertNotEmpty(
            self::getContainer()->getParameter('kernel.project_dir')
        );
        $this->assertEquals(
            dirname(dirname(__DIR__)),
            self::getContainer()->getParameter('kernel.project_dir')
        );
    }
}
