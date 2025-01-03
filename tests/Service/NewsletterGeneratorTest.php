<?php

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test. This assures that each test is run independently from each other.

// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsletterGeneratorTest extends KernelTestCase
{
    public function testSomething(): void
    {
        self::bootKernel();

        // ...
        $this->assertEquals(true, true);
    }
}
