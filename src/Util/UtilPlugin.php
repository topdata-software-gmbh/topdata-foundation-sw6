<?php

namespace Topdata\TopdataFoundationSW6\Util;

/**
 * 11/2024 created
 */
class UtilPlugin
{

    /**
     * Extract the plugin name from a fully qualified class name
     *
     * @param string $pluginClass The fully qualified class name of the plugin
     * @return string The plugin name
     * @throws \InvalidArgumentException If the provided class name is not a valid plugin class name
     */
    public static function extractPluginName(string $pluginClass): string
    {
        $lastNamespaceSeparator = strrpos($pluginClass, '\\');
        if ($lastNamespaceSeparator === false) {
            throw new \InvalidArgumentException('Invalid plugin class name provided');
        }

        return substr($pluginClass, $lastNamespaceSeparator + 1);
    }

    public static function isClassName(string $pluginNameOrClass): bool
    {
        return str_contains($pluginNameOrClass, '\\');
    }

}