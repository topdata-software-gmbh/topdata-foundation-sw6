<?php

namespace Topdata\TopdataFoundationSW6\Exception;

class PluginNotRegisteredException extends \RuntimeException
{

    /**
     * @param string $pluginName
     * @param string[] $registeredPlugins
     */
    public function __construct(string $pluginName, array $registeredPlugins, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Plugin "%s" is not registered. Registered plugins are: %s',
            $pluginName,
            empty($registeredPlugins) ? '[none]' : implode(', ', $registeredPlugins)
        );

        parent::__construct($message, 0, $previous);
    }

}

// example Usage in your code:
//
//    if (!isset($this->pluginConfigs[$pluginName])) {
//        throw new PluginNotRegisteredException($pluginName, $this->pluginConfigs);
//    }