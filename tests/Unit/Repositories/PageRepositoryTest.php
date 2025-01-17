<?php

/*

vendor/bin/phpunit --testsuite unit --filter PageRepositoryTest
vendor/bin/phpunit --testsuite unit --filter PageRepositoryTest testMyMethod

*/

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Repositories\PageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageRepository::class)]
class PageRepositoryTest extends TestCase
{
    public function testExample(): void
    {
        $this->markTestSkipped();
    }
}
