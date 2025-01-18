<?php

namespace App\Tests\Base;

use App\Repositories\PageRepository;
use PHPUnit\Framework\TestCase;

class BaseUnitTestCase extends TestCase
{
    protected static bool $initialized = false;
    protected PageRepository $pageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        // Unit Tests don't need content...
    }

    protected function tearDown(): void
    {
        // teardown after every test function (so: multiple times in 1 class)
        parent::tearDown();
    }
}
