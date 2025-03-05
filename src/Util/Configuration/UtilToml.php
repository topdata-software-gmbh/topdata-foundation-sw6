<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util\Configuration;

use TopdataSoftwareGmbH\Util\UtilDebug;

/**
 * 11/2024 created [untested]
 */
class UtilToml
{
//    /**
//     * Convert flat config array to TOML string
//     *
//     * @param array $flatConfig Flat configuration array with dot notation keys
//     * @return string TOML formatted string
//     * @throws \RuntimeException
//     */
//    public static function flatConfigToTomlV1(array $flatConfig): string
//    {
//        try {
//            $nested = UtilConfigTransformation::flatToNested($flatConfig);
//            return self::_nestedToToml($nested);
//        } catch (\Throwable $e) {
//            throw new \RuntimeException("Failed to convert configuration to TOML: " . $e->getMessage());
//        }
//    }

    /**
     * Process configuration to TOML format
     *
     * @param array $config Configuration array
     * @return string TOML formatted string
     */
    public static function flatConfigToToml(array $config): string
    {
        $sections = [];
        $result = '';

        // Group by sections
        foreach ($config as $key => $value) {
            $parts = explode('.', $key);
            $lastPart = array_pop($parts);
            $section = implode('.', $parts);

            if (!isset($sections[$section])) {
                $sections[$section] = [];
            }

            $sections[$section][$lastPart] = $value;
        }

        // Sort sections to ensure consistent output
        ksort($sections);

        // Process each section
        foreach ($sections as $section => $values) {
            if (!empty($section)) {
                $result .= "[$section]\n";
            }

            // Sort values within section
            ksort($values);

            foreach ($values as $key => $value) {
                $formattedValue = self::_formatValue($value);
                $result .= "$key = $formattedValue\n";
            }

            $result .= "\n";
        }

        return rtrim($result);
    }

    /**
     * Format value for TOML output
     *
     * @param mixed $value Value to format
     * @return string Formatted TOML value
     * @throws \InvalidArgumentException
     */
    private static function _formatValue(mixed $value): string
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
     * @param array $nested Nested array data
     * @param string $prefix Current key prefix
     * @return string TOML formatted string
     * @throws \RuntimeException If the value cannot be formatted
     */
    private static function _nestedToToml(array $nested, string $prefix = ''): string
    {
        $output = '';

        foreach ($nested as $key => $value) {
            $fullKey = $prefix ? "$prefix.$key" : $key;

            if (is_array($value)) {
                if (!empty($value)) {
                    $output .= "[$fullKey]\n";
                    $output .= self::_nestedToToml($value, '');
                }
            } else {
                try {
                    $formattedValue = self::_formatValue($value);
                    $output .= "$key = $formattedValue\n";
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException("Error formatting value for key '$fullKey': " . $e->getMessage());
                }
            }
        }

        return $output;
    }


}