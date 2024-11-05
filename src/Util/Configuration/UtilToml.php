<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util\Configuration;

/**
 * 11/2024 created [untested]
 */
class UtilToml
{
    private const DEFAULT_INDENT_SIZE = 2;
    private const DEFAULT_INDENT_CHAR = ' ';

    /**
     * Convert flat config array with dot notation keys to TOML string
     *
     * @param array $flatConfig Flat configuration array with dot notation keys
     * @param int $indentSize Number of spaces for each indent level
     * @param string $indentChar Character to use for indentation
     * @return string TOML formatted string
     * @throws \RuntimeException
     */
    public static function flatConfigToToml(
        array  $flatConfig,
        int    $indentSize = self::DEFAULT_INDENT_SIZE,
        string $indentChar = self::DEFAULT_INDENT_CHAR
    ): string
    {
        $nested = self::_flatToNested($flatConfig);

        return self::convertArrayToToml($nested, 0, '', $indentSize, $indentChar);
    }


    /**
     * TODO: somewhere else we have this already implemented
     * Convert flat array with dot notation to nested array structure
     *
     * @param array $flat Flat configuration array with dot notation keys
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
     * @param int $depth Current depth level
     * @param string $prefix Current key prefix
     * @param int $indentSize Number of spaces for each indent level
     * @param string $indentChar Character to use for indentation
     * @return string TOML formatted string
     */
    private static function convertArrayToToml(
        array  $data,
        int    $depth = 0,
        string $prefix = '',
        int    $indentSize = self::DEFAULT_INDENT_SIZE,
        string $indentChar = self::DEFAULT_INDENT_CHAR
    ): string
    {
        $output = '';
        $indent = str_repeat($indentChar, $depth * $indentSize);

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "$prefix.$key" : $key;

            if (is_array($value)) {
                if (!empty($value)) {
                    $output .= "$indent[$fullKey]\n";
                    $output .= self::convertArrayToToml($value, $depth + 1, '', $indentSize, $indentChar);
                    $output .= "\n";
                }
            } else {
                try {
                    $formattedValue = self::formatValue($value);
                    $output .= "$indent$key = $formattedValue\n";
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException("Error formatting value for key '$fullKey': " . $e->getMessage());
                }
            }
        }

        return $output;
    }
}
