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

    protected function configure(): void
    {
        $this->addArgument('pluginName', InputArgument::OPTIONAL, 'name of the plugin');
    }

    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginName = $input->getArgument('pluginName');
        if (!$pluginName) {
            // ---- list registered plugins in a table
            $this->cliStyle->note('Please pass plugin name as argument!');
            $table = [];
            foreach ($this->topConfigRegistry->getRegisteredPluginNames() as $pluginName) {
                $table[] = [
                    'name'    => $pluginName,
                    'version' => $this->pluginHelperService->getPluginVersion($pluginName),
                    // 'active'   => $this->pluginHelperService->isPluginActive($pluginName),
                ];
            }
            $this->cliStyle->listOfDictsAsTable($table, 'Registered Plugins');

            return Command::SUCCESS;
        }

        // ---- dump config of given plugin
        $this->cliStyle->section('Dump plugin configuration');
        $topConfig = $this->topConfigRegistry->getTopConfig($pluginName);
//        $this->cliStyle->dumpDict($topConfig->getFlatConfig(), 'Flat Config');
//        $this->cliStyle->dumpDict($topConfig->getNestedConfig(), 'Nested Config');
        $this->cliStyle->writeln($topConfig->getToml());

        return Command::SUCCESS;
    }
}
