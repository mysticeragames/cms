<?php

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
        self::bootKernel();

        $this->projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $this->dateHelper = new DateHelper();
        $this->pageRepository = self::getContainer()->get(PageRepository::class);

        if (self::$initialized === false) {
            self::$initialized = true;

            $testContentDir = __DIR__ . '/content';

            $reflector = new \ReflectionClass(get_called_class());
            $testContentDir = dirname($reflector->getFileName()) . '/content';

            if (is_dir($testContentDir)) {
                // Copy test content (once)
                $fs = new Filesystem();
                $fs->mirror(
                    $this->projectDir . '/tests/Integration/Repositories/PageRepository/content',
                    $this->getSiteRootPath(),
                    options: [
                        'override' => true,
                        'copy_on_windows' => true,
                        'delete' => true,
                    ]
                );
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        //$this->removeTestContent();
    }

    // private function removeTestContent()
    // {
    //     dump('remove');
    //     // Remove test content
    //     $projectDir = self::getContainer()->getParameter('kernel.project_dir');
    //     $fs = new Filesystem();
    //     $fs->remove($projectDir . '/content/src/' . $this->site);
    // }
}
