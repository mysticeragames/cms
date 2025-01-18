<?php

namespace App\Helpers;

class ProjectDirHelper
{
    public static function getProjectDir(): string
    {
        return dirname(dirname(__DIR__));
    }
}
