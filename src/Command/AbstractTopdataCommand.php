<?php

namespace Topdata\TopdataFoundationSW6\Command;

use DateTime;
use DateTimeZone;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;
use Topdata\TopdataFoundationSW6\Util\CliLogger;
use Topdata\TopdataFoundationSW6\Util\UtilDict;
use Topdata\TopdataFoundationSW6\Util\UtilFormatter;
use Twig\Util\DeprecationCollector;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

/**
 * Base command class with useful stuff for all commands.
 * Provides common functionalities like CLI styling, logging, and argument parsing.
 *
 * 04/2024 created
 */
abstract class AbstractTopdataCommand extends Command
{
    protected CliStyle $cliStyle;
    private float $_startTime; // in seconds, used for profiling

    /**
     * Fixes non-scalar values for display purposes.
     * Converts booleans to unicode symbols, arrays to comma-separated strings, and other non-scalar values to JSON.
     *
     * @param float|int|bool|array|string|null $val The value to fix.
     * @return float|int|string The fixed value.
     */
    private static function _fixNonScalar(float|int|bool|array|string|null $val)
    {
        if (is_bool($val)) {
            // return $val ? '🟢' : '🔴';
//            return $val ? '●' : '○';
            return $val ? '🟢' : '◯';
        }

        if (is_scalar($val)) {
            return $val;
        }

        if (is_array($val)) { // fixme? remove and just use json_encode?
            return implode(', ', $val);
        }

        return json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gets filtered command line arguments, excluding common options like help, verbose, etc.
     *
     * 07/2023 created.
     *
     * @param InputInterface $input The input interface.
     * @return array An array of filtered command line arguments.
     */
    protected static function _getFilteredCommandLineArgs(InputInterface $input): array
    {
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

        return $options;
    }

    /**
     * Initializes the command.
     * Sets up CLI styling, logging, and disables deprecation logs.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     */
    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->_startTime = microtime(true); // for performance profiling

        if ($input->hasOption('quiet') && (true === $input->getOption('quiet'))) {
            $output = new NullOutput();
        }

        $this->cliStyle = new CliStyle($input, $output);
        CliLogger::setCliStyle($this->cliStyle);

        // ---- print current date name + description
        $now = (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('Y-m-d H:i');
        CliLogger::title($now . ' - ' . $this->getName() . ' - ' . $this->getDescription());

        // ---- dump arguments and options
        self::_dumpArgsAndOptions($input);

        // ---- disable deprecation logs
        ErrorHandler::register(null, false)->setLoggers([
            E_DEPRECATED      => [null],
            E_USER_DEPRECATED => [null],
        ]);

        // ---- reduce doctrine memory leakage (not really)
        // this is what --no-debug option is doing (not really)
//        if (isset($this->em)) {
//            Log::notice("disabling doctrine SQL logging ...");
//            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
//        }
    }

    /**
     * Dumps arguments and options to the CLI.
     *
     * 11/2024 created
     *
     * @param InputInterface $input The input interface.
     */
    private function _dumpArgsAndOptions(InputInterface $input): void
    {
        $arguments = $input->getArguments();
        unset($arguments['command']);
        $options = $this->_getFilteredCommandLineArgs($input);

        if (empty($arguments) && empty($options)) {
            CliLogger::writeln('<gray>No arguments or options found.</gray>');
            return;
        }

        // ---- build table
        $tbl = CliLogger::getCliStyle()->createTable();
        $tbl->setHorizontal(false);
        $tbl->setHeaderTitle('Args and Opts');
        $tbl->setHeaders(['Key', 'Value']);

        $rows = [];
        if (empty($arguments)) {
            $rows[] = ['<gray>No Arguments</gray>', ''];
        } else {
            foreach ($arguments as $key => $val) {
                $rows[] = [$key, self::_fixNonScalar($val)];
            }
        }
        $rows[] = new TableSeparator(['colspan' => 2]);
        if (empty($options)) {
            $rows[] = ['<gray>No Options</gray>', ''];
        } else {
            foreach ($options as $key => $val) {
                $rows[] = [$key, self::_fixNonScalar($val)];
            }
        }
        $tbl->setRows($rows);

        $tbl->render();
        CliLogger::newLine();
    }

    /**
     * Prints a "DONE" message to the CLI with memory and duration information.
     *
     * 01/2023 created.
     */
    protected function done()
    {
        CliLogger::getCliStyle()->done('DONE ' . $this->getName() . ' [' . UtilFormatter::formatBytes(memory_get_peak_usage(true)) . ' / ' . UtilFormatter::formatDuration(microtime(true) - $this->_startTime, 2) . ']');
    }
}