<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Twig;

use Topdata\TopdataFoundationSW6\Exception\TopConfigNotFoundException;
use Topdata\TopdataFoundationSW6\Util\Configuration\UtilToml;
use TopdataSoftwareGmbH\Util\UtilDebug;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Topdata\TopdataFoundationSW6\Service\TopConfigRegistry;

/**
 * Provides Twig functions for accessing plugin configurations
 */
class TopConfigTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly TopConfigRegistry $topConfigRegistry,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('topConfigGet', [$this, 'topConfigGet']),
            new TwigFunction('topConfigGetString', [$this, 'topConfigGetString']),
            new TwigFunction('topConfigGetBool', [$this, 'topConfigGetBool']),
            new TwigFunction('topConfigGetInt', [$this, 'topConfigGetInt']),
            new TwigFunction('topConfigNested', [$this, 'topConfigNested']),
            new TwigFunction('topConfigFlat', [$this, 'topConfigFlat']),
            new TwigFunction('topConfigToml', [$this, 'topConfigToml']),
        ];
    }

    /**
     * Get an uncasted configuration value
     * TODO: rename to topConfigGet
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigGet(string $pluginName, string $dotKey)
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->get($dotKey);
    }

    /**
     * Get a string configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigGetString(string $pluginName, string $dotKey): string
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->getString($dotKey);
    }


    /**
     * Get a boolean configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigGetBool(string $pluginName, string $dotKey): bool
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->getBool($dotKey);
    }


    /**
     * Get a integer configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigGetInt(string $pluginName, string $dotKey): int
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->getInt($dotKey);
    }

    /**
     * Get an integer configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigNested(string $pluginName): array
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->getNestedConfig();
    }

    /**
     * TODO: make pluginName optional to get list of all plugins configs, each row
     *      prefixed with corresponding pluginName
     */
    public function topConfigFlat(string $pluginName): array
    {
        return $this->topConfigRegistry->getTopConfig($pluginName)->getFlatConfig();
    }


    /**
     * Returns the config as toml
     *
     * 11/2024 created
     */
    public function topConfigToml(string $pluginName): string|null
    {
        $flatConfig = $this->topConfigRegistry->getTopConfig($pluginName)->getFlatConfig();

        return UtilToml::flatConfigToToml($flatConfig);
    }

}

