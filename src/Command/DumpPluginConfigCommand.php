<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;
use Topdata\TopdataFoundationSW6\Service\CliDumpService;
use Topdata\TopdataFoundationSW6\Service\PluginHelperService;
use Topdata\TopdataFoundationSW6\Service\TopConfigRegistry;
use Topdata\TopdataFoundationSW6\Util\Configuration\UtilAsciiTree;
use Topdata\TopdataFoundationSW6\Util\Configuration\UtilToml;

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
        private readonly TopConfigRegistry $topConfigRegistry,
        private readonly CliDumpService    $cliDumpService,
    )
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->addArgument('pluginName', InputArgument::OPTIONAL, 'name of the plugin');
        $this->addOption('format', 'f', InputArgument::OPTIONAL, 'Output format (toml, yaml, tree, json, flat, sys)', 'toml');
    }

    /**
     * ==== MAIN ====
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ---- plugin name
        $pluginName = $input->getArgument('pluginName');
        if (!$pluginName) {
            // ---- list registered plugins and let user choose
            $this->cliDumpService->dumpRegisteredPlugins();

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select a plugin:',
                $this->topConfigRegistry->getRegisteredPluginNames(),
            );
            $question->setErrorMessage('Plugin %s is invalid.');

            $pluginName = $helper->ask($input, $output, $question);
        }

        // ---- dump config of given plugin
        $this->cliStyle->section("$pluginName plugin configuration");
        $topConfig = $this->topConfigRegistry->getTopConfig($pluginName);

        match ($input->getOption('format')) {
            'toml'  => $this->cliStyle->writeln(UtilToml::flatConfigToToml($topConfig->getFlatConfig())),
            'yaml'  => $this->cliStyle->writeln(Yaml::dump($topConfig->getNestedConfig())),
            'json'  => $this->cliStyle->writeln(json_encode($topConfig->getNestedConfig(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'tree'  => $this->cliStyle->writeln(UtilAsciiTree::tree($topConfig->getNestedConfig())),
            'flat'  => $this->cliStyle->dumpDict($topConfig->getFlatConfig()),
            'sys'   => $this->cliStyle->dumpDict($topConfig->getSystemConfig()),
            default => throw new \InvalidArgumentException("Invalid format: {$input->getOption('format')}, available formats: toml, yaml, json, tree, flat, sys")
        };

        return Command::SUCCESS;
    }
}
