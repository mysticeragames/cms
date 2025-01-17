<?php

/*

vendor/bin/phpunit --testsuite unit --filter BaseKernelTestCase
vendor/bin/phpunit --testsuite unit --filter BaseKernelTestCase testMyMethod

*/

namespace App\Tests;

use App\Helpers\DateHelper;
use App\Repositories\PageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class BaseKernelTestCase extends KernelTestCase
{
    protected string $projectDir;
    protected static bool $initialized = false;
    protected string $site = 'phpunit-test-site';
    protected PageRepository $pageRepository;
    protected DateHelper $dateHelper;

    protected function getSiteRootPath(): string
    {
        return $this->projectDir . '/content/src/' . $this->site;
    }

    protected function setUp(): void
    {
        // setUp before every test function (so: multiple times in 1 class)
        self::bootKernel();

        $this->projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $this->dateHelper = new DateHelper();
        $this->pageRepository = self::getContainer()->get(PageRepository::class);

        if (self::$initialized === false) {
            self::$initialized = true;
            // this part below is setup once per class

            $this->copyTestContentIfExists();
        }
    }

    protected function tearDown(): void
    {
        // teardown after every test function (so: multiple times in 1 class)
        parent::tearDown();
    }

    private function copyTestContentIfExists(): void
    {
        $this->removeExistingTestContent();

        $reflector = new \ReflectionClass(get_called_class());
        $testContentDir = substr($reflector->getFileName(), 0, -strlen('.php')) . 'Content';

        if (is_dir($testContentDir)) {
            // Copy test content (once)
            $fs = new Filesystem();
            $fs->mirror(
                $testContentDir,
                $this->getSiteRootPath(),
                options: [
                    'override' => true,
                    'copy_on_windows' => true,
                    'delete' => true,
                ]
            );
        }
    }

    private function removeExistingTestContent(): void
    {
        // Remove test content
        $fs = new Filesystem();
        $fs->remove($this->getSiteRootPath());
    }
}
