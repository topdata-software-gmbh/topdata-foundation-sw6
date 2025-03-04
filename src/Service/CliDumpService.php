<?php

namespace Topdata\TopdataFoundationSW6\Service;


use Topdata\TopdataFoundationSW6\Util\CliLogger;

/**
 * 11/2024 created
 */
class CliDumpService
{


    public function __construct(
        private readonly TopConfigRegistry   $topConfigRegistry,
        private readonly PluginHelperService $pluginHelperService,
    )
    {
    }

    /**
     * list registered plugins in a table
     *
     * 11/2024 created
     */
    public function dumpRegisteredPlugins(): void
    {
        $table = [];
        foreach ($this->topConfigRegistry->getRegisteredTopConfigs() as $topConfig) {
            $table[] = [
                'name'    => $topConfig->getPluginName(),
                'version' => $this->pluginHelperService->getPluginVersion($topConfig->getPluginName()),
                'configs' => count($topConfig->getFlatConfig()),
            ];
        }

        CliLogger::getCliStyle()->listOfDictsAsTable($table, 'Registered Plugins');
    }
}