<?php

namespace App\Entity;

use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class GitRemoteEntity
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[AppAssert\EndsWith('.git')]
    protected ?string $url;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
