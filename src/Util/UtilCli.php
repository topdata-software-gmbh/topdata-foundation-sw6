<?php

namespace Topdata\TopdataFoundationSW6\Util;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * 01/2023 created
 */
class UtilCli
{
    /**
     * Check if a process with the given PID is still running
     * 
     * @param int $pid Process ID to check
     * @return bool True if process is running, false otherwise
     */
    public static function isProcessActive(int $pid): bool
    {
        if (empty($pid)) {
            return false;
        }
        
        // For Linux/Unix systems
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }
        
        // For Windows systems
        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\" /NH", $output);
            return count($output) > 0 && !str_contains($output[0], 'No tasks');
        }
        
        // Fallback using ps command
        $output = [];
        exec("ps -p $pid", $output);
        return count($output) > 1; // Header + process line
    }

    /**
     * Determines the verbosity level based on input options
     *
     * @param InputInterface $input
     * @return int
     */
    private static function getVerbosityLevel(InputInterface $input): int
    {
        if ($input->hasParameterOption('-vvv', true) || $input->hasParameterOption('--verbose=3', true)) {
            return OutputInterface::VERBOSITY_DEBUG;
        }

        if ($input->hasParameterOption('-vv', true) || $input->hasParameterOption('--verbose=2', true)) {
            return OutputInterface::VERBOSITY_VERY_VERBOSE;
        }

        if ($input->hasParameterOption('-v', true) || $input->hasParameterOption('--verbose=1', true) || $input->hasParameterOption('--verbose', true)) {
            return OutputInterface::VERBOSITY_VERBOSE;
        }

        if ($input->hasParameterOption('-q', true)){
            return OutputInterface::VERBOSITY_QUIET;
        }

        return OutputInterface::VERBOSITY_NORMAL;
    }

    /**
     * Creates and returns a CliStyle instance based on the execution context
     *
     * When running in CLI mode, creates a CliStyle with ArgvInput and ConsoleOutput
     * with appropriate verbosity settings. Otherwise returns a CliStyle with
     * ArrayInput and NullOutput.
     *
     * 09/2024 created (extracted from CliStyleTrait)
     *
     * @return CliStyle The configured CLI style instance
     */
    public static function getCliStyle(): CliStyle
    {
        $isCli = php_sapi_name() === "cli";

        if ($isCli) {
            $input = new ArgvInput();
            $output = new ConsoleOutput();

            // Check for verbose options
            $verbosityLevel = self::getVerbosityLevel($input);
            $output->setVerbosity($verbosityLevel);

            return new CliStyle($input, $output);

        } else {
            return new CliStyle(new ArrayInput([]), new NullOutput());
        }
    }

}
