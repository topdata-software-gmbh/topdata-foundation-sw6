<?php

namespace Topdata\TopdataFoundationSW6\DTO;


use RuntimeException;
use Topdata\TopdataFoundationSW6\Exception\TopConfigNotFoundException;
use Topdata\TopdataFoundationSW6\Util\Configuration\UtilToml;

/**
 * A storage class for configuration of a (topdata)plugin
 *
 * 11/2024 created
 */
final class TopConfig
{
    private array $_flatConfig;
    private array $_nestedConfig;

    public function __construct(
        private string $pluginName,
        private array  $systemConfig,
        private array  $mapping
    )
    {
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function setPluginName(string $pluginName): void
    {
        $this->pluginName = $pluginName;
    }

    public function getSystemConfig(): array
    {
        return $this->systemConfig;
    }

    public function setSystemConfig(array $systemConfig): void
    {
        $this->systemConfig = $systemConfig;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }


    /**
     * Transform the original config using mapping
     */
    private function _sysToFlat(array $originalConfig, array $mapping): array
    {
        $transformed = [];
        foreach ($mapping as $oldKey => $newKey) {
            if (isset($originalConfig[$oldKey])) {
                $transformed[$newKey] = $originalConfig[$oldKey];
            }
        }

        return $transformed;
    }


    public function getFlatConfig(): array
    {
        if (!isset($this->_flatConfig)) {
            $this->_flatConfig = $this->_sysToFlat(
                $this->getSystemConfig(),
                $this->getMapping()
            );
        }

        return $this->_flatConfig;
    }

    /**
     * 11/2024 created
     *
     * @param array $flatConfig the flat config with dot notation
     * @return array the nested config
     */
    private static function _flatToNested(array $flatConfig): array
    {
        // Convert flat result to tree structure
        $result = [];
        foreach ($flatConfig as $key => $value) {
            $parts = explode('.', $key);
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

    /**
     * Get the tree-structured configuration for a plugin
     */
    public function getNestedConfig(): array
    {
        if (!isset($this->_nestedConfig)) {
            $this->_nestedConfig = self::_flatToNested($this->getFlatConfig());
        }

        return $this->_nestedConfig;
    }

    /**
     * Cast a value to the specified type
     *
     * @param mixed $value The value to cast
     * @param string $type The type to cast the value to (null [=uncasted], 'bool', 'int', 'string')
     * @return mixed The casted value
     * @throws RuntimeException If the specified type is unsupported
     */
    private function _castValue($value, ?string $type)
    {
        return match ($type) {
            null     => $value,
            'bool'   => (bool)$value,
            'int'    => (int)$value,
            'string' => (string)$value,
            default  => throw new RuntimeException(sprintf('Unsupported type "%s"', $type))
        };
    }

    public function get(string $dotKey, ?string $type = null)
    {
        $flat = $this->getFlatConfig();

        if (!array_key_exists($dotKey, $flat)) {
            throw new TopConfigNotFoundException(sprintf('TopConfig key "%s" not found for plugin "%s"', $dotKey, $this->pluginName));
        }

        return $this->_castValue($flat[$dotKey], $type);
    }

    public function getBool(string $dotKey): bool
    {
        return $this->get($dotKey, 'bool');
    }

    public function getString(string $dotKey)
    {
        return $this->get($dotKey, 'string');
    }

    public function getInt(string $dotKey)
    {
        return $this->get($dotKey, 'int');
    }

    public function getToml(): string
    {
        return UtilToml::flatConfigToToml($this->getFlatConfig());
    }


}
