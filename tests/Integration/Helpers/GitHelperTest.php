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

        (new Process(['git', '-C', $gitDir, 'init']))->run();

        dump($gitDir);

        $process = new Process(['pwd']);
        $process->run();
        dump($process->getOutput());

        $process = new Process(['ls', '-la', $gitDir]);
        $process->run();
        dump($process->getOutput());


        $process = new Process(['git', '-v']);
        $process->run();
        dump($process->getOutput());

        $process = new Process(['git', 'config', 'user.name']);
        $process->run();
        dump($process->getOutput());

        $process = new Process(['git', 'config', 'user.email']);
        $process->run();
        dump($process->getOutput());

        $process = new Process(['git', 'config', '-l']);
        $process->run();
        dump($process->getOutput());

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
