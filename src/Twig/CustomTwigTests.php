<?php

// https://symfony.com/doc/7.3/templates.html#templates-twig-extension

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class CustomTwigTests extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            // Usage:   {% if value is array %}
            new TwigTest('array', [$this, 'twigIsArray']),

            // Usage:   {% if value is string %}
            new TwigTest('string', [$this, 'twigIsString']),
        ];
    }

    public function twigIsArray(mixed $value): bool
    {
        return is_array($value);
    }

    public function twigIsString(mixed $value): bool
    {
        return is_string($value);
    }
}
