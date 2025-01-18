<?php

namespace App\Tests\Base;

use App\Helpers\ProjectDirHelper;
use App\Repositories\PageRepository;
use App\Tests\Base\IBaseTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\AsciiSlugger;

class BaseFunctionalTestCase extends WebTestCase implements IBaseTestCase
{
    //protected static bool $initialized = false;
    protected PageRepository $pageRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupTestContent();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }


    //////
    ////// ---> START: test setup
    //////
    private function setupTestContent(): void
    {
        // // setup once per test-run class
        // if (self::$initialized === false) {
        //     self::$initialized = true;

        //     $this->copyTestContentIfExists();
        // }
        $this->copyTestContentIfExists();
    }

    public function getTestSiteName(): string
    {
        $customContentDir = $this->getCustomContentDir();

        if (is_dir($customContentDir)) {
            $relativeName = substr($customContentDir, strlen($this->getProjectDir() . '/tests/'));
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($relativeName, '~')->toString();

            return 'phpunit-test-site - ' . $slug;
        }
        return 'phpunit-test-site';
    }

    public function getProjectDir(): string
    {
        return ProjectDirHelper::getProjectDir();
    }

    public function getTestSiteRootPath(): string
    {
        return $this->getProjectDir() . '/content/src/' . $this->getTestSiteName();
    }

    private function getCustomContentDir(): string
    {
        $reflector = new \ReflectionClass(get_called_class());

        return substr($reflector->getFileName(), 0, -strlen('.php')) . 'Content';
    }

    private function copyTestContentIfExists(): void
    {
        $fs = new Filesystem();
        $defaultContentDir = $this->getProjectDir() . '/tests/Base/content';
        $customContentDir = $this->getCustomContentDir();

        $contentDir = $defaultContentDir;

        if (is_dir($customContentDir)) {
            $contentDir = $customContentDir;
        }

        $fs->mirror(
            $contentDir,
            $this->getTestSiteRootPath(),
            options: ['override' => true, 'copy_on_windows' => true, 'delete' => true]
        );
    }

    // private function removeExistingTestContent(): void
    // {
    //     // Remove test content
    //     $fs = new Filesystem();
    //     $fs->remove($this->getTestSiteRootPath());
    // }
    //////
    ////// ---> END: test setup
    //////
}
