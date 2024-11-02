<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service for handling plugin-related operations
 * 
 * @since 04/2024 PluginHelper --> PluginHelperService
 * @since 11/2024 moved from TopdataConnectorSW6 to TopdataFoundationSW6
 * @since 11/2024 refactored to use ParameterBag
 */
class PluginHelperService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * Check if a plugin is currently active
     *
     * @param string $pluginClass The fully qualified class name of the plugin
     * @return bool True if the plugin is active, false otherwise
     */
    public function isPluginActive(string $pluginClass): bool
    {
        $activePlugins = $this->parameterBag->get('kernel.active_plugins');

        return isset($activePlugins[$pluginClass]);
    }

    /**
     * Get all active plugins
     *
     * @return array<string, mixed> Array of active plugins where key is plugin class name
     */
    public function activePlugins(): array
    {
        return $this->parameterBag->get('kernel.active_plugins');
    }

    /**
     * 11/2024 created
     */
    public function isTopFeedPluginAvailable(): bool
    {
        return $this->isPluginActive('Topdata\TopdataTopFeedSW6\TopdataTopFeedSW6');
    }
}

