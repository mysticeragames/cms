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
use Symfony\Component\Process\Process;

#[CoversClass(GitHelper::class)]
class GitHelperTest extends BaseIntegrationTestCase
{
    public function testIsGitDir(): void
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $git = new GitHelper();

        // Temporarily create a directory, and make it a GIT directory
        $fs = new FileSystem();
        $gitDir = $projectDir . '/content/src/--test-is-git-dir';
        $fs->createDir($gitDir);
        $process = new Process(['git', '-C', $gitDir, 'init']);
        $process->run();

        $this->assertTrue($git->isGitDir($projectDir));
    }

    public function testIsGitDirFalse(): void
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $outsideProjectDir = dirname($projectDir);

        $git = new GitHelper();
        $this->assertFalse($git->isGitDir($outsideProjectDir));
    }

    public function testIsGitDirNonExisting(): void
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        $nonExistingDir = $projectDir . '/---non-existing-dir---';

        $git = new GitHelper();
        $this->assertFalse($git->isGitDir($nonExistingDir));
    }
}
