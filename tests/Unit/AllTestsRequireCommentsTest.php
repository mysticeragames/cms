<?php

/*

vendor/bin/phpunit --testsuite unit --filter AllTestsRequireCommentsTest
vendor/bin/phpunit --testsuite unit --filter AllTestsRequireCommentsTest testMyMethod

*/

namespace App\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[CoversNothing]
class AllTestsRequireCommentsTest extends TestCase
{
    public function testRequireComments(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $testdir = dirname(__DIR__);

        $shouldStartWith = <<<EOD
<?php

/*

vendor/bin/phpunit --testsuite {{suite}} --filter {{filter}}
vendor/bin/phpunit --testsuite {{suite}} --filter {{filter}} testMyMethod

*/
EOD;

        $finder->files()->name('*Test.php')->in($testdir);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $contents = $fs->readFile($file->getRealPath());

                $relativePathParts = explode('/', $file->getRelativePath());
                $suite = strtolower(array_shift($relativePathParts));
                $filter = substr($file->getFileName(), 0, -strlen('.php'));

                $expected = $shouldStartWith;
                $expected = str_replace('{{suite}}', $suite, $expected);
                $expected = str_replace('{{filter}}', $filter, $expected);

                $this->assertTrue(
                    str_starts_with($contents, $expected),
                    "File has no (or an invalid) phpunit comment: " .
                    $file->getRelativePathname() . "\n" .
                    "Expected:\n\n" . $expected . "\n\n----> " .
                    $file->getRealPath()
                );
            }
        }
    }
}
