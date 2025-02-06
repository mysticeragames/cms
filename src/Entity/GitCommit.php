<?php

namespace App\Entity;

class GitCommit
{
    protected ?string $message = null;

    public function getCommitMessage(): string
    {
        if (!empty($this->message)) {
            return $this->message;
        }
        return $this->getDefaultMessage();
    }

    public function getDefaultMessage(): string
    {
        return "chore(site): Update from MakeItStatic-CMS [" . date('Y-m-d H:i:s') . "]";
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}
