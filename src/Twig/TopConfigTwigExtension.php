<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Twig;

use Topdata\TopdataFoundationSW6\Exception\TopConfigNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Topdata\TopdataFoundationSW6\Service\TopConfigService;

/**
 * Provides Twig functions for accessing plugin configurations
 */
class TopConfigTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly TopConfigService $topConfigService,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('topConfig', [$this, 'topConfig']),
            new TwigFunction('topConfigString', [$this, 'topConfigString']),
            new TwigFunction('topConfigBool', [$this, 'topConfigBool']),
            new TwigFunction('topConfigInt', [$this, 'topConfigInt']),
            new TwigFunction('topConfigTree', [$this, 'topConfigTree']),
            new TwigFunction('topConfigFlat', [$this, 'topConfigFlat']),
        ];
    }

    /**
     * Get an uncasted configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfig(string $pluginName, string $key)
    {
        return $this->topConfigService->get($pluginName, $key);
    }

    /**
     * Get a string configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigString(string $pluginName, string $key): string
    {
        return $this->topConfigService->getString($pluginName, $key);
    }


    /**
     * Get a boolean configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigBool(string $pluginName, string $key): bool
    {
        return $this->topConfigService->getBool($pluginName, $key);
    }

    /**
     * Get an integer configuration value
     *
     * @throws TopConfigNotFoundException when key is not found
     */
    public function topConfigTree(?string $pluginName = null)
    {
        return $this->topConfigService->getConfigTree($pluginName);
    }

    /**
     * TODO: make pluginName optional to get list of all plugins configs, each row
     *      prefixed with corresponding pluginName
     */
    public function topConfigFlat(string $pluginName)
    {
        return $this->topConfigService->getFlatConfig($pluginName);
    }

}

