<?php

namespace App\Helpers;

use DateTime;

class DateHelper
{
    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function isValidDate(?string $date): bool
    {
        if ($date === null) {
            return false;
        }

        $patterns = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
        ];

        foreach ($patterns as $pattern) {
            $dateTime = DateTime::createFromFormat($pattern, $date);

            $errors = DateTime::getLastErrors();
            if (empty($errors['warning_count']) && $dateTime !== false) {
                return true;
            }
        }
        return false;
    }
}
