<?php

// https://symfony.com/doc/current/validation/custom_constraint.html

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StartsWith extends Constraint
{
    public string $message = 'The string "{{ string }}" must start with "{{ expected }}".';
    public string $mode = 'strict';
    public string $expected;

    // all configurable options must be passed to the constructor
    public function __construct(
        string $expected,
        ?string $mode = null,
        ?string $message = null,
        ?array $groups = null,
        $payload = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->expected = $expected;
        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
}
