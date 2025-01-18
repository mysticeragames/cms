<?php

namespace App\Tests\Base;

interface IBaseTestCase
{
    public function getTestSiteName(): string;
    public function getTestSiteRootPath(): string;
    public function getProjectDir(): string;
}
