<?php

/*

vendor/bin/phpunit --testsuite end-to-end --filter IndexTest
vendor/bin/phpunit --testsuite end-to-end --filter IndexTest testMyMethod

*/

// https://symfony.com/doc/current/testing/end_to_end.html

namespace App\Tests\EndToEnd\Cms;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\Panther\PantherTestCase;

#[CoversNothing]
class IndexTest extends PantherTestCase
{
    public function testIndex(): void
    {
        // your app is automatically started using the built-in web server
        $client = static::createPantherClient();
        $client->request('GET', '/');

        // use any PHPUnit assertion, including the ones provided by Symfony...
        //$this->assertPageTitleContains('CMS');
        $this->assertSelectorTextContains('.main', 'CMS');

        // ... or the one provided by Panther
        // $this->assertSelectorIsEnabled('.search');
        // $this->assertSelectorIsDisabled('[type="submit"]');
        // $this->assertSelectorIsVisible('.errors');
        // $this->assertSelectorIsNotVisible('.loading');
        // $this->assertSelectorAttributeContains('.price', 'data-old-price', '42');
        // $this->assertSelectorAttributeNotContains('.price', 'data-old-price', '36');

        // ...
    }
}
