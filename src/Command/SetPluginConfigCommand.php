<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Topdata\TopdataFoundationSW6\Service\CliDumpService;
use Topdata\TopdataFoundationSW6\Service\TopConfigRegistry;

/**
 * 11/2024 created
 */
#[AsCommand(
    name: 'topdata:foundation:set-plugin-config',
    description: 'Set plugin configuration to stdout',
)]
class SetPluginConfigCommand extends AbstractTopdataCommand
{
    public function __construct(
        private readonly TopConfigRegistry   $topConfigRegistry,
        private readonly CliDumpService      $cliDumpService,

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

        // ---- set config of given plugin
        $this->cliStyle->section("$pluginName plugin configuration");
        $topConfig = $this->topConfigRegistry->getTopConfig($pluginName);

        // ---- get all config keys and let user choose
        $choices = $topConfig->getFlatConfig();
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select a config key:',
            array_keys($choices),
        );
        $question->setErrorMessage('Config key %s is invalid.');
        $dotKey = $helper->ask($input, $output, $question);

        // ---- print old value and let user choose new value
        $this->cliStyle->writeln(sprintf('Old value %s = %s', $dotKey, $choices[$dotKey]));
        $value = $this->cliStyle->ask('Please enter new value:');
        $topConfig->set($dotKey, $value);

        $numChanges =$this->topConfigRegistry->persistChanges();
        $this->cliStyle->writeln(sprintf('%d configuration values were changed', $numChanges));

        return Command::SUCCESS;
    }
}
