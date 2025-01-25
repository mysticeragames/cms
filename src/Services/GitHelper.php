<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitHelper
{
    public function run(array $cmd): Process
    {
        $process = new Process($cmd);
        $process->run();

        return $process;
    }

    public function isGitInitiated(string $dir): bool
    {
        if (is_dir($dir) === false) {
            // Directory does not exist
            return false;
        }

        if (empty($this->getRootDir($dir))) {
            // Dir does not have a git-root, so it's not yet initiated
            return false;
        }

        return true;
    }

    public function getRootDir(string $dir): string
    {
        $process = $this->run(['git', '-C', $dir, 'rev-parse', '--show-toplevel']);

        return trim($process->getOutput());
    }

    public function isRootGitDir(string $dir): bool
    {
        if ($dir === $this->getRootDir($dir)) {
            return true;
        }
        return false;

        // For code coverage tests, it's split up
        // return $dir === $this->getRootDir($dir);
    }

    public function isNonRootGitDir(string $dir): bool
    {
        $rootDir = $this->getRootDir($dir);

        if (empty($rootDir)) {
            return false; // Not a git repository
        }
        if ($rootDir !== $dir) {
            return false; // The given $dir is part of a git repository, but not the root
        }
        return true;

        // For code coverage tests, it's split up, don't use below...
        //return !empty($output) && $output !== $dir;
    }

    public function init(string $dir): bool
    {
        if (is_dir($dir) === false) {
            // Directory does not exist
            return false;
        }

        if (! empty($this->getRootDir($dir))) {
            // This is already a git dir
            return false;
        }

        $process = $this->run(['git', '-C', $dir, 'init']);

        // For code coverage tests, it's split up (therefore it's not 'return $process->isSuccessful()' )
        if ($process->isSuccessful()) {
            return true;
        }
        return false;
    }

    public function pull(string $dir): void
    {
        $process = $this->run(['git', '-C', $dir, 'pull']);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function addAll(string $dir): bool
    {
        return $this->add($dir, '.');
    }

    public function add(string $dir, ?string $path = null): bool
    {
        if ($path === null) {
            return false;
        }

        $process = $this->run(['git', '-C', $dir, 'add', $path]);

        if (!$process->isSuccessful()) {
            //throw new ProcessFailedException($process);
            return false;
        }
        return true;
    }

    public function commit(string $dir, string $message = ''): void
    {
        $process = $this->run(['git', '-C', $dir, 'commit', '-m', $message]);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function parseGitStatus(string $gitOutput): array
    {
        $lines = array_filter(explode("\n", trim($gitOutput)));
        $results = [];

        foreach ($lines as $line) {
            $entry = [
                'path' => '',
                'x' => '',
                'y' => '',
                'status' => '',
                'orig_path' => '',
            ];

            // Determine status by looking at each part of the line
            $x = $line[0] ?? '';
            $y = $line[1] ?? '';
            $entry['x'] = $x;
            $entry['y'] = $y;

            // Extract the rest of the line
            $rest = trim(substr($line, 3));

            // Handle specific statuses
            if ($x === 'R') {
                // Renamed: split into original and new path
                [$origPath, $newPath] = explode(" -> ", $rest, 2);
                $entry['status'] = 'renamed';
                $entry['orig_path'] = $origPath;
                $entry['path'] = $newPath;
            } elseif ($x === 'M') {
                $entry['status'] = 'modified';
                $entry['path'] = $rest;
            } elseif ($x === 'A') {
                $entry['status'] = 'added';
                $entry['path'] = $rest;
            } elseif ($x === 'D') {
                $entry['status'] = 'deleted';
                $entry['path'] = $rest;
            } elseif ($x === 'C') {
                $entry['status'] = 'copied';
                $entry['path'] = $rest;
            } elseif ($x === '?' && $y === '?') {
                $entry['status'] = 'untracked';
                $entry['path'] = $rest;
            } elseif ($x === '!' && $y === '!') {
                $entry['status'] = 'ignored';
                $entry['path'] = $rest;
            } elseif ($x === 'U' || $y === 'U') {
                $entry['status'] = 'unmerged';
                $entry['path'] = $rest;
            } else {
                $entry['status'] = 'unknown';
                $entry['path'] = $rest;
            }

            $results[] = $entry;
        }
        return $results;
    }

    public function hasChanges(string $dir): bool
    {
        return !empty($this->changes($dir));
    }

    public function changes(string $dir): array
    {
        // https://git-scm.com/docs/git-status#_short_format
        // X          Y     Meaning
        // -------------------------------------------------
        //      [AMD]   not updated
        // M        [ MTD]  updated in index
        // T        [ MTD]  type changed in index
        // A        [ MTD]  added to index
        // D                deleted from index
        // R        [ MTD]  renamed in index
        // C        [ MTD]  copied in index
        // [MTARC]          index and work tree matches
        // [ MTARC]    M    work tree changed since index
        // [ MTARC]    T    type changed in work tree since index
        // [ MTARC]    D    deleted in work tree
        //         R    renamed in work tree
        //         C    copied in work tree
        // -------------------------------------------------
        // D           D    unmerged, both deleted
        // A           U    unmerged, added by us
        // U           D    unmerged, deleted by them
        // U           A    unmerged, added by them
        // D           U    unmerged, deleted by us
        // A           A    unmerged, both added
        // U           U    unmerged, both modified
        // -------------------------------------------------
        // ?           ?    untracked
        // !           !    ignored
        // -------------------------------------------------

        $process = $this->run(['git', '-C', $dir, 'status', '-u', '--porcelain']);

        return $this->parseGitStatus($process->getOutput());

        // $output = $process->getOutput();
        // $lines = array_filter(explode("\n", $output));

        // $files = [];
        // foreach ($lines as $line) {
        //     $line = trim($line);
        //     $parts = explode(' ', $line);

        //     dump($parts);

        //     $status = array_shift($parts);
        //     $file = trim(implode(' ', $parts));
        //     if ($status === '??') {
        //         $status = '?';
        //     }
        //     $files[$file] = $status;
        // }
        // return $files;
    }

    public function addCommitPush(string $dir, ?string $message = null): void
    {
        if (empty($message)) {
            $message = 'deployment ' . date('Y-m-d H:i:s');
        }

        // Get current branch
        $process = $this->run(['git', '-C', $dir, 'branch', '--show-current']);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $branch = trim($process->getOutput());

        // Add all files in working directory
        $process = $this->run(['git', '-C', $dir, 'add', '.']);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Commit files
        $process = $this->run(['git', '-C', $dir, 'commit', '-m', $message]);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Push
        $process = $this->run(['git', '-C', $dir, 'push', '-u', 'origin', $branch]);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
