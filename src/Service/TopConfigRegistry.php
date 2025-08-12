<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use RuntimeException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\DTO\TopConfig;
use Topdata\TopdataFoundationSW6\Exception\PluginNotRegisteredException;
use Topdata\TopdataFoundationSW6\Util\CliLogger;
use Topdata\TopdataFoundationSW6\Util\UtilPlugin;

/**
 * This service handles the registration and retrieval of plugin configurations.
 * It supports both flat and tree-structured configuration access, with type-safe getters.
 *
 * 11/2024 created
 */
class TopConfigRegistry
{

    /**
     * @var array The registered plugin configurations, format: [pluginName => ['pluginSystemConfig' => [], 'mapping' => []]]
     */

    /** @var array<string, TopConfig> */
    private array $registeredTopConfigs = [];


    public function __construct(
        private readonly SystemConfigService $systemConfigService
    )
    {
    }

    /**
     * This is called in a container compiler pass.
     * It registers the plugin in the TopConfigRegistry.
     *
     * 11/2024 created
     */
    public function registerPlugin(string $pluginClass, array $configMapping): void
    {
        $pluginName = UtilPlugin::extractPluginName($pluginClass);
        $pluginSystemConfig = $this->systemConfigService->get($pluginName . '.config');
        if ($pluginSystemConfig === null) {
            // CliLogger::warning("plugin $pluginName has no config");
            $pluginSystemConfig = [];
        }
        $completeMapping = $this->_getCompleteMapping($pluginSystemConfig, $configMapping);
        $this->registeredTopConfigs[$pluginName] = new TopConfig(
            $pluginName,
            $pluginSystemConfig,
            $completeMapping
        );
    }




    /**
     * 11/2024 created
     *
     * @throws PluginNotRegisteredException
     */
    public function getTopConfig(string $pluginName): TopConfig
    {
        if (!isset($this->registeredTopConfigs[$pluginName])) {
            throw new PluginNotRegisteredException($pluginName, array_keys($this->registeredTopConfigs));
        }

        return $this->registeredTopConfigs[$pluginName];
    }


    /**
     * Get a value using dot notation for any registered plugin
     *
     * @throws RuntimeException If the plugin is not registered or the configuration key is not found
     */
    private function get(string $pluginName, string $dotKey, ?string $type = null)
    {
        return $this->getTopConfig($pluginName)->get($dotKey, $type);
    }




    public function _getCompleteMapping(array $pluginSystemConfig, array $mapping): array
    {
        // ---- add missing keys to $mapping
        $mappingComplete = $mapping;
        foreach ($pluginSystemConfig as $key => $value) {
            if (!array_key_exists($key, $mappingComplete)) {
                $mappingComplete[$key] = $key;
            }
        }

        // ---- remove invalid keys from $mapping
        foreach ($mappingComplete as $key => $value) {
            if (!array_key_exists($key, $pluginSystemConfig)) {
                unset($mappingComplete[$key]);
            }
        }

        return $mappingComplete;
    }

    /**
     * 11/2024 created
     *
     * @return TopConfig[]
     */
    public function getRegisteredTopConfigs(): array
    {
        return $this->registeredTopConfigs;
    }

    /**
     * 11/2024 created
     *
     * @return string[]
     */
    public function getRegisteredPluginNames(): array
    {
        return array_keys($this->registeredTopConfigs);
    }

    /**
     * Persists any changed configuration values to the database
     * 
     * @return int Number of configuration values that were persisted
     */
    public function persistChanges(): int
    {
        $changedCount = 0;
        foreach ($this->registeredTopConfigs as $pluginName => $topConfig) {
            $dirtyValues = $topConfig->getDirtyValues();
            if (!empty($dirtyValues)) {
                foreach ($dirtyValues as $key => $value) {
                    $this->systemConfigService->set(
                        $pluginName . '.config.' . $key,
                        $value
                    );
                    $changedCount++;
                }
                $topConfig->clearDirtyState();
            }
        }
        return $changedCount;
    }

}
