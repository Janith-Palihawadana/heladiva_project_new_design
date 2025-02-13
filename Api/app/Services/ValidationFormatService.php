<?php

namespace App\Services;

class ValidationFormatService
{
    public static function formatErrors($errors): array
    {
        $errors = $errors->toArray();
        $results = [];
        foreach ($errors as $key => $value) {
            $results[] = $value[0];
        }
        return $results;
    }
}
