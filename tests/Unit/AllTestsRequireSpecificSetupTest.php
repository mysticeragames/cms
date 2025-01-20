<?php

/*

vendor/bin/phpunit --testsuite unit --filter AllTestsRequireSpecificSetupTest
vendor/bin/phpunit --testsuite unit --filter AllTestsRequireSpecificSetupTest testMyMethod

*/

namespace App\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\UnicodeString;

#[CoversNothing]
class AllTestsRequireSpecificSetupTest extends TestCase
{
    public function testRequireComments(): void
    {
        $dirnames = [
            'EndToEnd',
            'Functional',
            'Integration',
            'Unit',
        ];

        $shouldStartWith = <<<EOD
<?php

/*

vendor/bin/phpunit --testsuite {{suite}} --filter {{filter}}
vendor/bin/phpunit --testsuite {{suite}} --filter {{filter}} testMyMethod

*/
EOD;

        foreach ($dirnames as $dirname) {
            $suite = (new UnicodeString($dirname))->kebab();

            $fs = new Filesystem();
            $finder = new Finder();

            $finder->files()->name('*Test.php')->in(dirname(__DIR__) . '/' . $dirname);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $contents = $fs->readFile($file->getRealPath());

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

    public function testRequireExtends(): void
    {
        $dirnames = [
            'Functional',
            'Integration',
            'Unit',
        ];

        $thisClassName = (new ReflectionClass($this))->getShortName();

        foreach ($dirnames as $dirname) {
            $fs = new Filesystem();
            $finder = new Finder();

            $finder->files()->name('*Test.php')->in(dirname(__DIR__) . '/' . $dirname);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $contents = $fs->readFile($file->getRealPath());

                    $filter = substr($file->getFileName(), 0, -strlen('.php'));

                    if ($filter === $thisClassName) {
                        continue; // Skip this class...
                    }

                    $expected = 'class {{class}} extends Base{{type}}TestCase';
                    $expected = str_replace('{{type}}', $dirname, $expected);
                    $expected = str_replace('{{class}}', $filter, $expected);

                    $this->assertTrue(
                        str_contains($contents, $expected),
                        "File has no (or an invalid) phpunit class: " .
                        $file->getRelativePathname() . "\n" .
                        "Expected:\n\n" . $expected . "\n\n----> " .
                        $file->getRealPath()
                    );
                }
            }
        }
    }
}
