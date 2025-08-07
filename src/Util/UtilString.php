<?php

namespace Topdata\TopdataFoundationSW6\Util;

class UtilString
{


    /**
     * shortens $str if too long .. prepending "...".
     *
     * @param         $str
     * @param         $maxLength
     * @param  string $suffix
     * @return string
     */
    public static function maxLength(string $str, int $maxLength, $suffix = '...')
    {
        $lenSuffix = strlen($suffix);
        if (strlen($str) > $maxLength - $lenSuffix) {
            return substr($str, 0, $maxLength - $lenSuffix) . $suffix;
        }

        return $str;
    }

    /**
     * @param  string $haystack
     * @param  string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        // return strpos($haystack, $needle) === 0;
        return substr($haystack, 0, strlen($needle)) === $needle;
    }


    /**
     * 07/2020 created (tldr2anki).
     * 07/2025 TODO: remove (use str_ends_with instead)
     *
     * @param  string $haystack
     * @param  string $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return substr($haystack, strlen($haystack) - strlen($needle)) === $needle;
    }

    public static function max255(string $str): string
    {
        return trim(substr(trim($str), 0, 255));
    }

}
