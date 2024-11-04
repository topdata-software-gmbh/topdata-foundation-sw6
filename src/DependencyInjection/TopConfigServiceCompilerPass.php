<?php

namespace Topdata\TopdataFoundationSW6\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Topdata\TopdataFoundationSW6\Service\TopConfigService;

/**
 * 10/2024 created
 */
readonly class TopConfigServiceCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $shopwarePluginClass,
        private readonly array  $configMapping)
    {
    }


    public function process(ContainerBuilder $container): void
    {
        // ---- register the plugin in Topdata Configration Center's TopConfigService
        if ($container->hasDefinition(TopConfigService::class)) {
            $definition = $container->getDefinition(TopConfigService::class);
            $definition->addMethodCall('registerPluginConfig', [
                $this->shopwarePluginClass,
                $this->configMapping,
            ]);
        }
    }
}