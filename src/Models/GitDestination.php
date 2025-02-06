<?php

namespace App\Models;

use App\Repositories\GitRepository;

class GitDestination extends GitRepository
{
    public function __construct(string $projectDir)
    {
        parent::__construct($projectDir . '/content/dist');
    }
}
