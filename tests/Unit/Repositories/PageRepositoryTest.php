<?php

/*

vendor/bin/phpunit --testsuite unit --filter PageRepositoryTest
vendor/bin/phpunit --testsuite unit --filter PageRepositoryTest testMyMethod

*/

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Repositories\PageRepository;
use App\Tests\Base\BaseUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageRepository::class)]
class PageRepositoryTest extends BaseUnitTestCase
{
    public function testExample(): void
    {
        $this->markTestSkipped();
    }
}
