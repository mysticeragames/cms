<?php

/*

vendor/bin/phpunit --testsuite integration --filter GitHelperTest
vendor/bin/phpunit --testsuite integration --filter GitHelperTest testMyMethod

*/

declare(strict_types=1);

// https://symfony.com/doc/current/testing.html#integration-tests
// The KernelTestCase also makes sure your kernel is rebooted for each test.
// This assures that each test is run independently from each other.

namespace App\Tests\Unit\Services;

use App\Services\GitHelper;
use App\Tests\Base\BaseIntegrationTestCase;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitHelper::class)]
class GitHelperTest extends BaseIntegrationTestCase
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
        $git = new GitHelper();
        $this->assertFalse($git->isRootGitDir($this->createTempDir()));
    }

    public function testNonExistingDirsCanNotBeInitiated(): void
    {
        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();

        $git = new GitHelper();
        $this->assertFalse($git->init($nonExistingDir));

        // Sanity check: not only check if the init returns false,
        //   but also check if a dir was not created and turned into a git dir
        $this->assertFalse($git->isRootGitDir($nonExistingDir));
    }

    public function testNonExistingDirsAreNotGitRootDirs(): void
    {
        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();

        $git = new GitHelper();
        $this->assertFalse($git->isRootGitDir($nonExistingDir));
    }

    public function testInit(): void
    {
        $gitDir = $this->createTempDir();
        $git = new GitHelper();

        // Sanity check
        $this->assertFalse($git->isRootGitDir($gitDir));

        $git->init($gitDir);
        $this->assertTrue($git->isRootGitDir($gitDir));
    }

    public function testIsInitiated(): void
    {
        $git = new GitHelper();

        $nonExistingDir = sys_get_temp_dir() . '/tmp-non-existing-dir-' . date('YmdHis') . '-' . mt_rand();
        $this->assertFalse($git->isGitInitiated($nonExistingDir));

        $dir = $this->createTempDir();
        $this->assertFalse($git->isGitInitiated($dir));

        $git->init($dir);
        $this->assertTrue($git->isGitInitiated($dir));

        // Non-existing subdir (false)
        $this->assertFalse($git->isGitInitiated($dir . '/non-existing-subdir'));

        $fs = new FileSystem();
        $fs->createDir($dir . '/subdir');
        $this->assertTrue($git->isGitInitiated($dir . '/subdir'));
    }

    public function testChanges(): void
    {
        $dir = $this->createTempDir();

        $git = new GitHelper();
        $git->init($dir);

        $fs = new FileSystem();
        $fs->write($dir . '/test.txt', 'test');
        $fs->write($dir . '/test/test2.txt', 'test2');
        $fs->write($dir . '/test/test2/test3.txt', 'test3');

        $this->assertEquals('test.txt', $git->changes($dir)[0]['path']);
        $this->assertEquals('test/test2.txt', $git->changes($dir)[1]['path']);
        $this->assertEquals('test/test2/test3.txt', $git->changes($dir)[2]['path']);
        $this->assertEquals('untracked', $git->changes($dir)[0]['status']);
        $this->assertEquals('untracked', $git->changes($dir)[1]['status']);
        $this->assertEquals('untracked', $git->changes($dir)[2]['status']);

        $git->add($dir, 'test.txt');

        $this->assertEquals('test.txt', $git->changes($dir)[0]['path']);
        $this->assertEquals('test/test2.txt', $git->changes($dir)[1]['path']);
        $this->assertEquals('test/test2/test3.txt', $git->changes($dir)[2]['path']);
        $this->assertEquals('added', $git->changes($dir)[0]['status']);
        $this->assertEquals('untracked', $git->changes($dir)[1]['status']);
        $this->assertEquals('untracked', $git->changes($dir)[2]['status']);

        $git->add($dir, '.');

        $this->assertEquals('test.txt', $git->changes($dir)[0]['path']);
        $this->assertEquals('test/test2.txt', $git->changes($dir)[1]['path']);
        $this->assertEquals('test/test2/test3.txt', $git->changes($dir)[2]['path']);
        $this->assertEquals('added', $git->changes($dir)[0]['status']);
        $this->assertEquals('added', $git->changes($dir)[1]['status']);
        $this->assertEquals('added', $git->changes($dir)[2]['status']);

        $git->commit($dir, 'test');
        $this->assertFalse($git->hasChanges($dir));

        $this->assertEquals([], $git->changes($dir));

        $testPathAdded = 'test/test2/test3/test4.txt';
        $testPathDeleted = 'test/test2.txt';
        $testPathRenamed = 'newtestdir/renamed-test3-to-test4.txt';

        $fs->write($dir . '/' . $testPathAdded, 'test4');
        $fs->delete($dir . '/' . $testPathDeleted);
        $fs->rename($dir . '/test/test2/test3.txt', $dir . '/' . $testPathRenamed);

        // TODO: it will not have the same result when the changes are not added first...
        // TODO: Test the $git->parseGitStatus() for all possible use cases (merge conflicts, etc)
        $git->addAll($dir);

        $changes = $git->changes($dir);
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

        $this->assertTrue($git->hasChanges($dir));
    }
}
