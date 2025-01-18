<?php

namespace App\Interfaces;

interface ISite
{
    public function getContentRoot(): string; // /var/www/.../content/src/my site
    public function getPathName(): string; // 'my site'
    public function getTitle(): string; // 'My site'
}
