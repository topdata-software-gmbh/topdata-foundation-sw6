<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use RuntimeException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\DTO\TopConfig;
use Topdata\TopdataFoundationSW6\Exception\PluginNotRegisteredException;
use Topdata\TopdataFoundationSW6\Exception\TopConfigNotFoundException;
use Topdata\TopdataFoundationSW6\Trait\CliStyleTrait;
use TopdataSoftwareGmbH\Util\UtilDebug;

/**
 * This service handles the registration and retrieval of plugin configurations.
 * It supports both flat and tree-structured configuration access, with type-safe getters.
 *
 * 11/2024 created
 */
class TopConfigRegistry
{
    use CliStyleTrait;

    /**
     * @var array The registered plugin configurations, format: [pluginName => ['pluginSystemConfig' => [], 'mapping' => []]]
     */
    private array $registeredPluginConfigs = [];
    private array $flatConfigs = [];
    private array $configTrees = [];

    public function __construct(
        private readonly SystemConfigService $systemConfigService
    )
    {
        $this->beVerboseOnCli();
    }


    /** @var array<string, TopConfig> */
    private array $registeredConfigs = [];

    /**
     * This is called in a container compiler pass.
     * It registers the plugin in the TopConfigRegistry.
     *
     * 11/2024 created
     */
    public function registerPlugin(string $pluginClass, array $configMapping): void
    {
        $pluginName = $this->extractPluginName($pluginClass);
        $pluginSystemConfig = $this->systemConfigService->get($pluginName . '.config');
        if($pluginSystemConfig === null) {
            $this->cliStyle->warning("plugin $pluginName has no config");
            $pluginSystemConfig = [];
        }
        $completeMapping = $this->_getCompleteMapping($pluginSystemConfig, $configMapping);
        $this->registeredConfigs[$pluginName] = new TopConfig(
            $pluginName,
            $pluginSystemConfig,
            $completeMapping
        );
    }

    public function getConfig(string $pluginName): ?TopConfig
    {
        return $this->registeredConfigs[$pluginName] ?? null;
    }

    private static function extractPluginName(string $pluginClass): string
    {
        $lastNamespaceSeparator = strrpos($pluginClass, '\\');
        if ($lastNamespaceSeparator === false) {
            throw new \InvalidArgumentException('Invalid plugin class name provided');
        }

        return substr($pluginClass, $lastNamespaceSeparator + 1);
    }


    /**
     * Get a boolean value using dot notation
     * @throws RuntimeException if the plugin is not registered or the key is not found
     */
    public function getBool(string $pluginName, string $dotKey): bool
    {
        return $this->get($pluginName, $dotKey, 'bool');
    }

    /**
     * Get a string value using dot notation
     * @throws RuntimeException if the plugin is not registered or the key is not found
     */
    public function getString(string $pluginName, string $dotKey): string
    {
        return $this->get($pluginName, $dotKey, 'string');
    }

    /**
     * Get an integer value using dot notation
     * @throws RuntimeException if the plugin is not registered, key is not found, or value is negative when $notNegative is true
     */
    public function getInt(string $pluginName, string $dotKey, bool $notNegative = false): int
    {
        $value = $this->get($pluginName, $dotKey, 'int');

        if ($notNegative && ($value < 0)) {
            throw new RuntimeException(sprintf('TopConfig key "%s" cannot have a negative value', $dotKey));
        }

        return $value;
    }

    /**
     * Get the flat configuration for a plugin with dot notation
     *
     * @throws RuntimeException if the plugin is not registered
     */
    public function getFlatConfig(string $pluginName): array
    {
        if (!isset($this->registeredPluginConfigs[$pluginName])) {
            throw new PluginNotRegisteredException($pluginName, array_keys($this->registeredPluginConfigs));
        }

        if (!isset($this->flatConfigs[$pluginName])) {
            $this->flatConfigs[$pluginName] = $this->_transformConfig(
                $this->registeredPluginConfigs[$pluginName]['pluginSystemConfig'],
                $this->registeredPluginConfigs[$pluginName]['mapping']
            );
        }

        return $this->flatConfigs[$pluginName];
    }

    /**
     * TODO: second argument (eg "import.product") for only returning a sub-tree
     *
     * Get the tree-structured configuration for a plugin
     * @throws RuntimeException if the plugin is not registered
     */
    public function getNestedConfig(?string $pluginName = null): array
    {
        // ---- special case: return all config trees
        if ($pluginName === null) {
            foreach ($this->registeredPluginConfigs as $pluginName => $foo) {
                $this->_buildMappingIfNotExists($pluginName);
            }
            return $this->configTrees;
        }

        // ---- normal case: return config tree of a single plugin
        if (!isset($this->registeredPluginConfigs[$pluginName])) {
            throw new PluginNotRegisteredException($pluginName, array_keys($this->registeredPluginConfigs));
        }

        $this->_buildMappingIfNotExists($pluginName);

        return $this->configTrees[$pluginName];
    }

    /**
     * Get a value using dot notation for any registered plugin
     *
     * @throws RuntimeException If the plugin is not registered or the configuration key is not found
     */
    public function get(string $pluginName, string $dotKey, ?string $type = null)
    {
        $flat = $this->getFlatConfig($pluginName);

        if (!array_key_exists($dotKey, $flat)) {
            throw new TopConfigNotFoundException(sprintf('TopConfig key "%s" not found for plugin "%s"', $dotKey, $pluginName));
        }

        return $this->castValue($flat[$dotKey], $type);
    }

    /**
     * Transform the original config using mapping
     */
    private function _transformConfig(array $originalConfig, array $mapping): array
    {
        $transformed = [];
        foreach ($mapping as $oldKey => $newKey) {
            if (isset($originalConfig[$oldKey])) {
                $transformed[$newKey] = $originalConfig[$oldKey];
            }
        }

        return $transformed;
    }

    /**
     * FIXME: this is a duplicate code!
     * Build a tree structure from flat dot notation
     */
    private function buildConfigTree(array $flat): array
    {
        $tree = [];
        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            $current = &$tree;
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            $current = $value;
        }

        return $tree;
    }

    /**
     * Cast a value to the specified type
     *
     * @param mixed $value The value to cast
     * @param string $type The type to cast the value to (null [=uncasted], 'bool', 'int', 'string')
     * @return mixed The casted value
     * @throws RuntimeException If the specified type is unsupported
     */
    private function castValue($value, ?string $type)
    {
        return match ($type) {
            null     => $value,
            'bool'   => (bool)$value,
            'int'    => (int)$value,
            'string' => (string)$value,
            default  => throw new RuntimeException(sprintf('Unsupported type "%s"', $type))
        };
    }

    public function _buildMappingIfNotExists(string $pluginName): void
    {
        if (!isset($this->configTrees[$pluginName])) {
            $flatConfig = $this->getFlatConfig($pluginName);
            $this->configTrees[$pluginName] = $this->buildConfigTree($flatConfig);
        }
    }

    /**
     * Get information about all registered plugins
     */
    public function getRegisteredPluginsInfo(): array
    {
        $info = [];
        foreach ($this->registeredPluginConfigs as $pluginName => $config) {
            $info[] = [
                'name'        => $pluginName,
                'configCount' => count($this->getFlatConfig($pluginName)),
                'mappings'    => array_values($config['mapping'])
            ];
        }
        return $info;
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
}
