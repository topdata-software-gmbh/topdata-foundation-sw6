<?php

namespace Topdata\TopdataFoundationSW6\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Topdata\TopdataFoundationSW6\Service\TopConfigRegistry;

/**
 * 10/2024 created
 */
class TopConfigRegistryCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $shopwarePluginClass,
        private readonly array  $configMapping)
    {
    }


    public function process(ContainerBuilder $container): void
    {
        // ---- register the plugin in Topdata Configration Center's TopConfigRegistry
        if ($container->hasDefinition(TopConfigRegistry::class)) {
            $definition = $container->getDefinition(TopConfigRegistry::class);
            $definition->addMethodCall('registerPlugin', [
                $this->shopwarePluginClass,
                $this->configMapping,
            ]);
        }
    }
}