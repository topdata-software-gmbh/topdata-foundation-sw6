<?php

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;
use Topdata\TopdataFoundationSW6\Util\UtilDict;
use Topdata\TopdataFoundationSW6\Util\UtilFormatter;
use TopdataSoftwareGmbH\Util\UtilDebug;

/**
 * base command class with useful stuff for all commands.
 *
 * 04/2024 created
 */
abstract class AbstractTopdataCommand extends Command
{
    protected CliStyle $cliStyle;
    private float $_startTime; // in seconds, used for profiling

    private static function _fixNonScalar(float|int|bool|array|string|null $val)
    {
        if(is_scalar($val)) {
            return $val;
        }

        return json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 07/2023 created.
     *
     * @param InputInterface $input
     * @return array dict
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

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
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

        // ---- dump arguments and options
        self::_dumpArgsAndOptions($input);

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
     * 11/2024 created
     */
    private function _dumpArgsAndOptions(InputInterface $input): void
    {
        $arguments = $input->getArguments();
        unset($arguments['command']);
        $options = $this->_getFilteredCommandLineArgs($input);

        if(empty($arguments) && empty($options)) {
            $this->cliStyle->writeln('<gray>No arguments or options found.</gray>');
            return;
        }

        // ---- build table
        $tbl = $this->cliStyle->createTable();
        $tbl->setHorizontal(false);
        $tbl->setHeaderTitle('Args and Opts');
        $tbl->setHeaders(['Key', 'Value']);

        $rows = [];
        foreach ($arguments as $key => $val) {
            $rows[] = [$key, self::_fixNonScalar($val)];
        }
        $rows[] = new TableSeparator(['colspan' => 2]);
        foreach ($options as $key => $val) {
            $rows[] = [$key, self::_fixNonScalar($val)];
        }
        $tbl->setRows($rows);

        $tbl->render();
        $this->cliStyle->newLine();
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
