<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;

class NavigationHelper
{
    public static function isGroupActive(array $urls): bool
    {
        foreach ($urls as $url) {
            if (is_callable($url)) {
                $url = $url();
            }

            if (request()->url() === $url || request()->is(parse_url($url, PHP_URL_PATH))) {
                return true;
            }
        }
        return false;
    }

    protected static function getUrlPath($url): string
    {
        if (is_callable($url)) {
            $url = $url();
        }

        if (is_object($url) && method_exists($url, '__toString')) {
            $url = (string) $url;
        }

        return self::normalizePath(parse_url($url, PHP_URL_PATH));
    }

    protected static function normalizePath(string $path): string
    {
        return trim($path, '/');
    }
}
