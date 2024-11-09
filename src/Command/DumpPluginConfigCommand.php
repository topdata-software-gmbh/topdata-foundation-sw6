<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Service\PluginHelperService;
use Topdata\TopdataFoundationSW6\Service\TopConfigRegistry;
use Topdata\TopdataFoundationSW6\Util\Configuration\UtilAsciiTree;

/**
 * 11/2024 created
 */
#[AsCommand(
    name: 'topdata:foundation:dump-plugin-config',
    description: 'Dump plugin configuration to stdout',
)]
class DumpPluginConfigCommand extends AbstractTopdataCommand
{
    public function __construct(
        private readonly TopConfigRegistry   $topConfigRegistry,
        private readonly PluginHelperService $pluginHelperService
    )
    {
        parent::__construct();
    }

    /**
     * list registered plugins in a table
     *
     * 11/2024 created
     */
    private function _listRegisteredPluginsInATable(): void
    {
        $this->cliStyle->note('Please pass plugin name as argument!');
        $table = [];
        foreach ($this->topConfigRegistry->getRegisteredPluginNames() as $pluginName) {
            $table[] = [
                'name'    => $pluginName,
                'version' => $this->pluginHelperService->getPluginVersion($pluginName),
            ];
        }
        $this->cliStyle->listOfDictsAsTable($table, 'Registered Plugins');
    }


    protected function configure(): void
    {
        $this
            ->addArgument('pluginName', InputArgument::OPTIONAL, 'name of the plugin')
            ->addOption(
                'format',
                'f',
                InputArgument::OPTIONAL,
                'Output format (toml, yaml, tree, json, flat)',
                'toml'
            );
    }

    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginName = $input->getArgument('pluginName');
        if (!$pluginName) {
            // ---- list registered plugins in a table and exit
            $this->_listRegisteredPluginsInATable();

            return Command::SUCCESS;
        }

        // ---- dump config of given plugin
        $this->cliStyle->section("$pluginName plugin configuration");
        $topConfig = $this->topConfigRegistry->getTopConfig($pluginName);
        
        match($input->getOption('format')) {
            'toml' => $this->cliStyle->writeln($topConfig->getToml()),
            'yaml' => $this->cliStyle->writeln($topConfig->getYaml()),
            'json' => $this->cliStyle->writeln(json_encode($topConfig->getSystemConfig(), JSON_PRETTY_PRINT)),
            'tree' => $this->cliStyle->writeln(UtilAsciiTree::tree($topConfig->getNestedConfig())),
            'flat' => $this->cliStyle->dumpDict($topConfig->getFlatConfig()),
            default => throw new \InvalidArgumentException("Invalid format: {$input->getOption('format')}, available formats: toml, yaml, json, tree, flat")
        };

        return Command::SUCCESS;
    }
}
