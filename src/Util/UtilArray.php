<?php

namespace Topdata\TopdataFoundationSW6\Util;

/**
 * Utility class for working with arrays.
 * Provides methods for checking array types and flattening nested arrays.
 */
class UtilArray
{

    /**
     * Checks if an array is associative.
     *
     * An associative array has string keys instead of sequential numeric keys.
     */
    public static function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Checks if an array is numeric (sequential).
     *
     * A numeric array has sequential numeric keys starting from 0.
     */
    public static function isNumeric(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Recursive private function for flatten.
     *
     * @param array $json The array to flatten.
     * @param string $separator The separator to use for flattening keys.
     * @param array $aIgnore An array of keys to ignore.
     * @param string $prefix The prefix for the current level of flattening.
     * @param array $row The accumulated flattened array.
     *
     * @return array The flattened array.
     */
    private static function _flattenRecursive($json, string $separator, array $aIgnore, string $prefix = '', $row = []): array
    {
        foreach ($json as $key => $val) {
            // ---- Ignore specified keys
            if (in_array($key, $aIgnore)) {
                continue;
            }

            // ---- Handle array values
            if (is_array($val)) {
                // ---- Handle associative arrays or arrays of arrays
                if (self::isAssoc($val) || is_array($val[0])) {
                    $newPrefix = "{$prefix}{$key}{$separator}";
                    $row = array_merge($row, self::_flattenRecursive($val, $separator, $aIgnore, $newPrefix, $row));
                } else {
                    // ---- Handle numeric arrays, implode with newline
                    $row[$prefix . $key] = implode("\n", $val);
                }
            } else {
                // ---- Handle scalar values
                $row[$prefix . $key] = $val;
            }
        }

        return $row;
    }

    /**
     * Flattens a nested array into a single-dimensional array.
     *
     * The keys of the flattened array are created by concatenating the keys of the nested arrays,
     * separated by the specified separator.
     *
     * 12/2023 created.
     *
     * @param array $json The array to flatten.
     * @param string $separator The separator to use for flattening keys (default: '.').
     * @param array $aIgnore An array of keys to ignore (default: []).
     *
     * @return array The flattened array.
     */
    public static function flatten($json, string $separator = '.', array $aIgnore = []): array
    {
        return self::_flattenRecursive($json, $separator, $aIgnore);
    }
}