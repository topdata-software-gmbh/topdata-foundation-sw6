<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util\Configuration;

use TopdataSoftwareGmbH\Util\UtilDebug;

/**
 * 11/2024 created [untested]
 */
class UtilToml
{
    /**
     * Convert flat config array to TOML string
     *
     * @param array $flatConfig Flat configuration array with dot notation keys
     * @return string TOML formatted string
     * @throws \RuntimeException
     */
    public static function flatConfigToToml(array $flatConfig): string
    {
        try {
            $nested = self::_flatToNested($flatConfig);
            return self::convertArrayToToml($nested);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to convert configuration to TOML: " . $e->getMessage());
        }
    }


    /**
     * Convert flat array with dot notation to nested array structure
     *
     * @param array $flat Flat configuration array
     * @return array Nested array structure
     */
    private static function _flatToNested(array $flat): array
    {
        $nested = [];

        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            $current = &$nested;

            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    if (!isset($current[$part]) || !is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
        }

        return $nested;
    }

    /**
     * Format value for TOML output
     *
     * @param mixed $value Value to format
     * @return string Formatted TOML value
     * @throws \InvalidArgumentException
     */
    private static function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }
        if (is_numeric($value)) {
            return (string)$value;
        }
        if (is_null($value)) {
            return '';
        }
        if (is_array($value) && empty($value)) {
            return '[]';
        }

        throw new \InvalidArgumentException("Unsupported value type: " . gettype($value));
    }

    /**
     * Convert nested array to TOML format
     *
     * @param array $data Nested array data
     * @param string $prefix Current key prefix
     * @return string TOML formatted string
     */
    private static function convertArrayToToml(array $data, string $prefix = ''): string
    {
        $output = '';

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "$prefix.$key" : $key;

            if (is_array($value)) {
                if (!empty($value)) {
                    $output .= "[$fullKey]\n";
                    $output .= self::convertArrayToToml($value, '');
                }
            } else {
                try {
                    $formattedValue = self::formatValue($value);
                    $output .= "$key = $formattedValue\n";
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException("Error formatting value for key '$fullKey': " . $e->getMessage());
                }
            }
        }

        return $output;
    }
}