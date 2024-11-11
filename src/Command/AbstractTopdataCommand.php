<?php

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;
use Topdata\TopdataFoundationSW6\Util\UtilDict;
use Topdata\TopdataFoundationSW6\Util\UtilFormatter;

/**
 * base command class with useful stuff for all commands.
 *
 * 04/2024 created
 */
abstract class AbstractTopdataCommand extends Command
{
    protected CliStyle $cliStyle;
    private float $_startTime; // in seconds, used for profiling

    /**
     * 07/2023 created.
     *
     * @param  InputInterface $input
     * @return array
     */
    protected static function getFilteredCommandLineArgs(InputInterface $input): array
    {
        // UtilDebug::d($input->getArguments());
        $ignoreList = [
            'help',
            'quiet',
            'verbose',
            'version',
            'ansi',
            'no-interaction',
            'env',
            'no-debug',
            'profile',
        ];
        $options = UtilDict::omit($input->getOptions(), $ignoreList);
        foreach ($options as $key => $val) {
            if (is_bool($val)) {
                $options[$key] = $val ? 'true' : 'false';
            }
            if (is_array($val)) {
                $options[$key] = implode(', ', $val);
            }
        }

        return $options;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->_startTime = microtime(true); // for performance profiling

        if ($input->hasOption('quiet') && (true === $input->getOption('quiet'))) {
            $output = new NullOutput();
        }

        $this->cliStyle = new CliStyle($input, $output);

        // ---- print current date name + description
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Berlin')))->format('Y-m-d H:i');
        $this->cliStyle->title($now . ' - ' . $this->getName() . ' - ' . $this->getDescription());

        // ---- dump arguments
        $arguments = $input->getArguments();
        unset($arguments['command']);
        if (!empty($arguments)) {
            $this->cliStyle->dumpDict($arguments, 'Command Arguments');
        }

        // ---- dump options
        $options = $this->getFilteredCommandLineArgs($input);
        if (!empty($options)) {
            $this->cliStyle->dumpDict($options, 'Command Options');
        }

        // ---- disable deprecation logs
        ErrorHandler::register(null, false)->setLoggers([
            \E_DEPRECATED      => [null],
            \E_USER_DEPRECATED => [null],
        ]);

        // ---- reduce doctrine memory leakage (not really)
        // this is what --no-debug option is doing (not really)
//        if (isset($this->em)) {
//            Log::notice("disabling doctrine SQL logging ...");
//            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
//        }
    }


    /**
     * 01/2023 created.
     *
     * prints FAIL error and exits
     */
    protected function done()
    {
        $this->cliStyle->done('DONE ' . $this->getName() . ' [' . UtilFormatter::formatBytes(memory_get_peak_usage(true)) . ' / ' . UtilFormatter::formatDuration(microtime(true) - $this->_startTime, 2) . ']');
    }
}
