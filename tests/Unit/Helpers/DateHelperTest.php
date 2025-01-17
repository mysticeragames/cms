<?php

/*

vendor/bin/phpunit --testsuite unit --filter DateHelperTest
vendor/bin/phpunit --testsuite unit --filter DateHelperTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Helpers\DateHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

// https://docs.phpunit.de/en/11.5/code-coverage.html#targeting-units-of-code

#[CoversClass(DateHelper::class)]
class DateHelperTest extends TestCase
{
    public function testValidDates(): void
    {
        $dateHelper = new DateHelper();
        $this->assertTrue($dateHelper->isValidDate('2025-01-01 10:15:58'));
        $this->assertTrue($dateHelper->isValidDate('2025-01-01 10:15'));
        $this->assertTrue($dateHelper->isValidDate('2025-01-01'));
        $this->assertTrue($dateHelper->isValidDate('2024-02-29'));
        $this->assertTrue($dateHelper->isValidDate('2025-02-28'));
    }

    public function testInvalidDates(): void
    {
        $dateHelper = new DateHelper();
        $this->assertFalse($dateHelper->isValidDate('2025-13-01'));
    }
}
