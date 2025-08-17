<?php

namespace App\Helpers;

class NavigationHelper
{
    public static function isGroupActive(array $urls): bool
    {
        $request = request();
        $currentUrl = $request->url();
        $currentPath = self::normalizePath($request->path());

        foreach ($urls as $url) {
            if (is_callable($url)) {
                $url = $url();
            }

            if ($currentUrl === $url || $currentPath === self::getUrlPath($url)) {
                return true;
            }
        }

        return false;
    }

    protected static function getUrlPath($url): string
    {
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
