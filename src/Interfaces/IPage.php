<?php

namespace App\Interfaces;

interface IPage
{
    public function getPathName(): string; // pathname (news/post/my post)
    public function getName(): string; // name (my post)
    public function getFilePath(): string; // filePath (/var/www/.../content/src/my-site/pages/post/my post.md)
    public function getCreatedAt(): string; // createdAt (2025-01-01 10:15:16)
    public function getUpdatedAt(): string; // updatedAt (2025-01-01 10:15:16)
    public function getSlug(): string; // slug (my-post)
    public function getTitle(): string; // title (My post)
}
