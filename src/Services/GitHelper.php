<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitHelper
{
    public function isGitDir(string $dir): bool
    {
        $process = new Process(['git', '-C', $dir, 'rev-parse']);
        $process->disableOutput();
        $process->run();

        // For code coverage tests, it's split up (therefore it's not 'return $process->isSuccessful()' )
        if ($process->isSuccessful()) {
            return true;
        }
        return false;
    }

    public function pull(string $dir): void
    {
        // Pull
        $process = new Process(['git', '-C', $dir, 'pull']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function addCommitPush(string $dir, ?string $message = null): void
    {
        if (empty($message)) {
            $message = 'deployment ' . date('Y-m-d H:i:s');
        }

        // Get current branch
        $process = new Process(['git', '-C', $dir, 'branch', '--show-current']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $branch = trim($process->getOutput());

        // Add all files in working directory
        $process = new Process(['git', '-C', $dir, 'add', '.']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Commit files
        $process = new Process(['git', '-C', $dir, 'commit', '-m', $message]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Push
        $process = new Process(['git', '-C', $dir, 'push', '-u', 'origin', $branch]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
