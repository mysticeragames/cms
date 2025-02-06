<?php

/*

vendor/bin/phpunit --testsuite integration --filter GitRepositoryTest
vendor/bin/phpunit --testsuite integration --filter GitRepositoryTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Repositories\GitRepository;
use App\Tests\Base\BaseIntegrationTestCase;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRepository::class)]
class GitRepositoryTest extends BaseIntegrationTestCase
{
    protected static array $tempDirNames = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeTempDirs();
    }

    // Clean up temp dirs after every test
    private function removeTempDirs(): void
    {
        $fs = new FileSystem();
        foreach (self::$tempDirNames as $tempDirName) {
            $tempDir = sys_get_temp_dir() . '/tmp-' . $tempDirName;
            if (is_dir($tempDir)) {
                $fs->delete($tempDir);
            }
        }
    }

    private function createTempDirName(): string
    {
        $tmpDirName = 'makeitstaticcms-test-' . date('YmdHis') . '-' . mt_rand();

        self::$tempDirNames[] = $tmpDirName;

        return sys_get_temp_dir() . '/tmp-' . $tmpDirName;
    }

    private function createTempDir(): string
    {
        $tempDir = $this->createTempDirName();

        $fs = new FileSystem();
        $fs->createDir($tempDir);

        return $tempDir;
    }

    public function testIsRootGitDirFalse(): void
    {
        $git = new GitRepository($this->createTempDir());
        $this->assertFalse($git->isRootGitDir());
    }

    public function testNonExistingDirsCanNotBeInitiated(): void
    {
        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();

        $git = new GitRepository($nonExistingDir);
        $this->assertFalse($git->init());

        // Sanity check: not only check if the init returns false,
        //   but also check if a dir was not created and turned into a git dir
        $this->assertFalse($git->isRootGitDir());
    }

    public function testNonExistingDirsAreNotGitRootDirs(): void
    {
        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();

        $git = new GitRepository($nonExistingDir);
        $this->assertFalse($git->isRootGitDir());
    }

    public function testInit(): void
    {
        $gitDir = $this->createTempDir();
        $git = new GitRepository($gitDir);

        // Sanity check
        $this->assertFalse($git->isRootGitDir());

        $git->init();
        $this->assertTrue($git->isRootGitDir());
    }

    public function testIsInitiated(): void
    {
        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();
        $git = new GitRepository($nonExistingDir);
        $this->assertFalse($git->isInitiated());

        $git = new GitRepository($this->createTempDir());
        $this->assertFalse($git->isInitiated());

        $git->init();
        $this->assertTrue($git->isInitiated());
    }

    public function testChanges(): void
    {
        $dir = $this->createTempDir();

        $git = new GitRepository($dir);
        $git->init();

        $fs = new FileSystem();
        $fs->write($dir . '/test.txt', 'test');
        $fs->write($dir . '/test/test2.txt', 'test2');
        $fs->write($dir . '/test/test2/test3.txt', 'test3');

        // Everything is added by default when the 'changes' method is called

        // $this->assertEquals('test.txt', $git->changes()[0]['path']);
        // $this->assertEquals('test/test2.txt', $git->changes()[1]['path']);
        // $this->assertEquals('test/test2/test3.txt', $git->changes()[2]['path']);
        // $this->assertEquals('untracked', $git->changes()[0]['status']);
        // $this->assertEquals('untracked', $git->changes()[1]['status']);
        // $this->assertEquals('untracked', $git->changes()[2]['status']);

        // $git->add('test.txt');

        // $this->assertEquals('test.txt', $git->changes()[0]['path']);
        // $this->assertEquals('test/test2.txt', $git->changes()[1]['path']);
        // $this->assertEquals('test/test2/test3.txt', $git->changes()[2]['path']);
        // $this->assertEquals('added', $git->changes()[0]['status']);
        // $this->assertEquals('untracked', $git->changes()[1]['status']);
        // $this->assertEquals('untracked', $git->changes()[2]['status']);

        // $git->add('.');

        $this->assertEquals('test.txt', $git->changes()[0]['path']);
        $this->assertEquals('test/test2.txt', $git->changes()[1]['path']);
        $this->assertEquals('test/test2/test3.txt', $git->changes()[2]['path']);
        $this->assertEquals('added', $git->changes()[0]['status']);
        $this->assertEquals('added', $git->changes()[1]['status']);
        $this->assertEquals('added', $git->changes()[2]['status']);

        $git->commit('test');
        $this->assertFalse($git->hasChanges());

        $this->assertEquals([], $git->changes());

        $testPathAdded = 'test/test2/test3/test4.txt';
        $testPathDeleted = 'test/test2.txt';
        $testPathRenamed = 'newtestdir/renamed-test3-to-test4.txt';

        $fs->write($dir . '/' . $testPathAdded, 'test4');
        $fs->delete($dir . '/' . $testPathDeleted);
        $fs->rename($dir . '/test/test2/test3.txt', $dir . '/' . $testPathRenamed);

        // TODO: it will not have the same result when the changes are not added first...
        // TODO: Test the $git->parseGitStatus() for all possible use cases (merge conflicts, etc)
        $git->addAll();

        $changes = $git->changes();
        $paths = array_column($changes, 'path');

        $this->assertContains($testPathAdded, $paths);
        $this->assertContains($testPathDeleted, $paths);
        $this->assertContains($testPathRenamed, $paths);

        foreach ($changes as $change) {
            if ($change['path'] === $testPathAdded) {
                $this->assertEquals('added', $change['status']);
            }
            if ($change['path'] === $testPathDeleted) {
                $this->assertEquals('deleted', $change['status']);
            }
            if ($change['path'] === $testPathRenamed) {
                $this->assertEquals('renamed', $change['status']);
            }
        }

        $this->assertTrue($git->hasChanges());
    }
}
