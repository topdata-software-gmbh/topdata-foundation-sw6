<?php

namespace Topdata\TopdataFoundationSW6\Util\Configuration;

/**
 * Utility class for configuration transformations
 *
 * 10/2024 created
 */
class UtilConfigTransformation
{
    const SEPARATOR = '.';

    /**
     * Transforms configuration using a flat key mapping
     *
     * @param array $config Original configuration array
     * @param array $mapping Simple key-to-key mapping array where key is old key and value is new key
     * @param string $prefix Prefix to add to all transformed keys (default: '')
     * @param bool $preserveUnmapped Keep keys that don't have a mapping (default: true)
     * @param bool $sort Sort resulting array by keys (default: true)
     * @return array Transformed configuration array
     */
    public static function transformConfigFlat(
        array  $config,
        array  $mapping,
        string $prefix = '',
        bool   $preserveUnmapped = true,
        bool   $sort = true,
    ): array
    {
        $transformed = [];
        $prefix = trim($prefix);
        $prefixDot = $prefix ? $prefix . self::SEPARATOR : '';

        foreach ($config as $oldKey => $value) {
            if (isset($mapping[$oldKey])) {
                $transformed[$prefixDot . $mapping[$oldKey]] = $value;
            } elseif ($preserveUnmapped) {
                $transformed[$prefixDot . $oldKey] = $value;
            }
        }

        if ($sort) {
            ksort($transformed);
        }

        return $transformed;
    }

    /**
     * Transforms a flat configuration array into a tree structure
     * First applies flat key mapping, then converts to tree structure
     *
     * @param array $config Original configuration array
     * @param array $mapping Mapping array where values can contain dots for tree structure
     * @param string $prefix Prefix to add to all transformed keys (default: '')
     * @param bool $preserveUnmapped Keep keys that don't have a mapping (default: true)
     * @param bool $sort Sort resulting array by keys (default: true)
     * @return array Transformed configuration as tree structure
     */
    public static function transformConfigTree(
        array  $config,
        array  $mapping,
        string $prefix = '',
        bool   $preserveUnmapped = true,
        bool   $sort = true,
    ): array
    {
        // First transform using flat mapping with prefix
        $transformed = self::transformConfigFlat($config, $mapping, $prefix, $preserveUnmapped, $sort);

        // Convert flat result to tree structure
        $result = [];
        foreach ($transformed as $key => $value) {
            $parts = explode(self::SEPARATOR, $key);
            $current = &$result;
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
            unset($current);
        }

        return $result;
    }
}