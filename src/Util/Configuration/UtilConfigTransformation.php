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
     * @param array $originalConfig the original plugin configuration array from shopware's systemConfigService
     * @param array $mapping Mapping array where values can contain dots for tree structure
     * @param string $prefix Prefix to add to all transformed keys (default: '')
     * @param bool $preserveUnmapped Keep keys that don't have a mapping (default: true)
     * @param bool $sort Sort resulting array by keys (default: true)
     * @return array Transformed configuration as tree structure
     */
    public static function transformConfigTree(
        array  $originalConfig,
        array  $mapping,
        string $prefix = '',
        bool   $preserveUnmapped = true,
        bool   $sort = true,
    ): array
    {
        // First transform using flat mapping with prefix
        $transformed = self::transformConfigFlat($originalConfig, $mapping, $prefix, $preserveUnmapped, $sort);
        $result = self::flatToNested($transformed);

        return $result;
    }


    /**
     * 11/2024 created
     *
     * @param array $flatConfig the flat config with dot notation
     * @return array the nested config
     */
    public static function flatToNested(array $flatConfig): array
    {
        // Convert flat result to tree structure
        $result = [];
        foreach ($flatConfig as $key => $value) {
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