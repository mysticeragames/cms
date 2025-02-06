<?php

namespace App\Models;

use App\Repositories\GitRepository;

class GitContent extends GitRepository
{
    public function __construct(string $projectDir)
    {
        parent::__construct($projectDir . '/content/src');
    }
}
