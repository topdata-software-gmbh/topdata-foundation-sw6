<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * 04/2024 PluginHelper --> PluginHelperService
 * 11/2024 moved from TopdataConnectorSW6 to TopdataFoundationSW6
 */
class PluginHelperService
{
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function isPluginActive(string $pluginClass): bool
    {
        $activePlugins = $this->container->getParameter('kernel.active_plugins');

        return isset($activePlugins[$pluginClass]);
    }

    public function activePlugins(): array
    {
        return $this->container->getParameter('kernel.active_plugins');
    }
}

