<?php

namespace Topdata\TopdataFoundationSW6\Util;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Core\Content\TopdataReport\TopdataReportEntity;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * CliLogger is a static class providing a facade for the CliStyle class,
 * offering convenient methods for logging messages to the console with different styles and functionalities.
 *
 * 03/2025 created
 */
class CliLogger
{
    const DEFAULT_TERMINAL_WIDTH = 80;


    private static ?CliStyle $_cliStyle = null;
    /**
     * @var float Stores the microtime for lap time measurement.
     * see self::lap()
     */
    private static float $microtime;

    /**
     * Progress bars storage by label
     * @var ProgressBar[]
     */
    private static array $progressBars = [];


    /**
     * Prints an info message to stdout.
     *
     * 06/2024 created
     */
    public static function info(string $msg): void
    {
        // self::getCliStyle()->writeln("[info]\t<green>$msg</green>");
        self::writeln("[I]\t\033[34m" . $msg . "\033[0m");
    }


    /**
     * Prints a note message to stdout.
     */
    public static function note(string $msg): void
    {
        // self::getCliStyle()->writeln("[notice]\t<blue>$msg</blue>");

        // blue background, white text
        self::writeln("[N]\t\033[44m\033[37m" . $msg . "\033[0m");

    }

    /**
     * Prints a warning message to stdout.
     */
    public static function warning(string $msg): void
    {
        // yellow background, black text
        // self::writeln("[W]\t\033[43m\033[30m" . $msg . "\033[0m");

        self::getCliStyle()->writeln("⚠️ [Warning] $msg");
    }

    /**
     * Prints an error message to stdout.
     */
    public static function error(string $msg): void
    {
        // red background, white text
        // self::writeln("[E]\t\033[41m\033[37m" . $msg . "\033[0m");

        self::getCliStyle()->writeln("❌ [Error] $msg");
    }

    /**
     * Prints a success message to stdout.
     */
    public static function success(string $msg): void
    {
        self::getCliStyle()->writeln("✅ Success: $msg\n");
    }


    public static function setCliStyle(CliStyle $cliStyle): void
    {
        self::$_cliStyle = $cliStyle;
    }


    /**
     * Retrieves the CliStyle instance. If it's not set, it creates a new one (only if not in CLI mode).
     *
     * @return CliStyle The CliStyle instance.
     * @throws \LogicException If CliStyle has not been set in CLI mode.
     */
    public static function getCliStyle(): CliStyle
    {
        if (self::$_cliStyle === null) {

            if (php_sapi_name() === 'cli') {
                // Throw exception as the style should have been set by the command
                throw new \LogicException('CliStyle has not been set in CliLogger. Please call CliLogger::setCliStyle() first, typically in your command\'s initialize method.');
            }

            self::$_cliStyle = new CliStyle(new ArrayInput([]), new NullOutput());
        }

        return self::$_cliStyle;
    }


    /**
     * Dumps a TopdataReportEntity's report data to stdout.
     *
     * 02/2025 created
     */
    public static function dumpReport(TopdataReportEntity $report): void
    {
        self::getCliStyle()->dumpDict($report->getReportData());
    }


    /**
     * Writes a message to stdout using CliStyle.
     *
     * 01/2025 created
     */
    public static function writeln(string $msg = ''): void
    {
//        if(php_sapi_name() !== 'cli') {
//            return;
//        }
//
//        echo $msg . "\n";
        self::getCliStyle()->writeln($msg);
    }

    /**
     * Writes a red message to stdout.
     */
    public static function red(string $msg): void
    {
        self::writeln("\033[31m" . $msg . "\033[0m");
    }

    /**
     * Writes a blue message to stdout.
     */
    public static function blue(string $msg): void
    {
        self::writeln("\033[34m" . $msg . "\033[0m");
    }

    /**
     * Writes a yellow message to stdout.
     */
    public static function yellow(string $msg): void
    {
        self::writeln("\033[33m" . $msg . "\033[0m");
    }

    /**
     * Prints the progress of a task to stdout.
     *
     * 01/2025 created
     */
    public static function progress(int $current, int $total, ?string $label = null): void
    {
        if($label) {
            self::write($label . ' ');
        }

        $percentFormatted = round($current / $total * 100, 1) . '%';
        $currentFormatted = number_format($current, 0, ',', '.');
        $totalFormatted = number_format($total, 0, ',', '.');
        self::writeln("$currentFormatted / $totalFormatted - $percentFormatted");
    }



    /**
     * Create or update a progress bar using Symfony's ProgressBar component
     *
     * @param int $current Current progress value
     * @param int $total Total expected value
     * @param string $label Unique identifier for the progress bar
     * @param string|null $message Optional message to display
     *
     * 06/2025 created
     */
    public static function progressBar(int $current, int $total, string $label = 'default', ?string $message = null): void
    {
        // ---- Create progress bar if it doesn't exist
        if (!isset(self::$progressBars[$label])) {
            $progressBar = new ProgressBar(self::getCliStyle(), $total);
            $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%' . ($message ? ' %message%' : ''));

            if ($message) {
                $progressBar->setMessage($message);
            }

            $progressBar->start();
            self::$progressBars[$label] = $progressBar;
        }

        // ---- Get the progress bar
        $progressBar = self::$progressBars[$label];

        // ---- Update message if provided
        if ($message !== null) {
            $progressBar->setMessage($message);
        }

        // ---- Set progress to current value
        $progressBar->setProgress($current);

        // ---- If we've reached 100%, finish and clean up
        if ($current >= $total) {
            $progressBar->finish();
            self::writeln(''); // Add newline after completion
            unset(self::$progressBars[$label]); // Clean up to save memory
        }
    }

    /**
     * Finish and clean up a specific progress bar
     *
     * @param string $label Progress bar identifier
     *
     * 06/2025 created
     */
    public static function finishProgressBar(string $label = 'default'): void
    {
        if (isset(self::$progressBars[$label])) {
            self::$progressBars[$label]->finish();
            self::writeln(''); // Add newline after completion
            unset(self::$progressBars[$label]);
        }
    }

    /**
     * Clean up all progress bars
     *
     * 06/2025 created
     */
    public static function clearProgressBars(): void
    {
        foreach (self::$progressBars as $label => $progressBar) {
            $progressBar->finish();
            unset(self::$progressBars[$label]);
        }
        if (!empty(self::$progressBars)) {
            self::writeln(''); // Add newline if we cleaned up any bars
        }
    }


    /**
     * Prints a debug message to stdout if verbosity is high enough.
     */
    public static function debug(string $msg): void
    {
        if (self::getCliStyle()->getVerbosity() < OutputInterface::VERBOSITY_DEBUG) {
            return;
        }

        self::getCliStyle()->writeln("[debug]\t<gray>$msg</gray>");
    }

    /**
     * Prints a section message to stdout.
     */
    public static function section(string $msg): void
    {
        self::getCliStyle()->section("\n\n" . $msg);
    }


    /**
     * Prints a title message to stdout.
     */
    public static function title(string $msg): void
    {
        self::getCliStyle()->title($msg);
    }


    /**
     * Dumps variables to stdout if in CLI mode.
     *
     * 03/2025 created
     */
    public static function dump()
    {
        // only if we are in cli mode
        if (self::isCLi()) {
            dump(...func_get_args());
        }
    }


    /**
     * Checks if the current environment is CLI.
     *
     * 03/2025 extracted from ProgressLoggingService
     */
    private static function isCLi(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Gets the newline character based on the environment (CLI or web).
     *
     * 03/2025 extracted from ProgressLoggingService
     */
    private static function getNewline(): string
    {
        if (self::isCli()) {
            return "\n";
        } else {
            return '<br>';
        }
    }

    /**
     * Get caller information for logging
     *
     * @param int $stepsBack Number of steps back in the trace to get the caller info.
     * @return string Formatted caller information with file and line number
     *
     * 04/2025 extracted from activity()
     */
    private static function getCallerInfo(int $stepsBack = 2): string
    {
        $ddSource = debug_backtrace()[$stepsBack];

        return basename($ddSource['file']) . ':' . $ddSource['line'] . self::getNewline();
    }

    /**
     * Format and align a message with caller information on the right side
     *
     * @param string $message The message to display
     * @return array Returns an array with ['message' => formatted message, 'padding' => padding spaces, 'caller' => caller info]
     *
     * 04/2025 extracted from activity()
     */
    public static function formatWithCaller(string $message): array
    {
        $terminalWidth = self::_getTerminalWidth();

        // ---- Get caller information from one level up in the stack
        $caller = self::getCallerInfo();
        $callerLength = strlen($caller);

        // ---- Calculate padding needed
        $messageLength = strlen($message);
        $padding = max(0, $terminalWidth - $messageLength - $callerLength);

        return [
            'message' => $message,
            'padding' => str_repeat(' ', $padding),
            'caller'  => $caller
        ];
    }

    /**
     * Helper method for logging stuff to stdout with right-aligned caller information.
     *
     * 03/2025 extracted from ProgressLoggingService
     */
    public static function activity(string $msg = '.', bool $newLine = false): void
    {
        if(self::getCliStyle()->getVerbosity() < OutputInterface::VERBOSITY_DEBUG) {
            CliLogger::getCliStyle()->write($msg, $newLine);
        } else {
            // ---- Write the message with padding and called
            $formatted = self::formatWithCaller($msg);
            CliLogger::getCliStyle()->write($formatted['message']);
            CliLogger::getCliStyle()->write($formatted['padding']);
            CliLogger::getCliStyle()->write($formatted['caller']);
        }
    }

    /**
     * Prints memory usage to stdout.
     *
     * 03/2025 extracted from ProgressLoggingService
     */
    public static function mem(): void
    {
        self::writeln('[' . round(memory_get_usage(true) / 1024 / 1024) . 'Mb]');
    }

    /**
     * Measures and returns the time elapsed since the last call.
     *
     * 03/2025 extracted from ProgressLoggingService
     */
    public static function lap($start = false): string
    {
        if ($start) {
            self::$microtime = microtime(true);

            return '';
        }
        $lapTime = microtime(true) - self::$microtime;
        self::$microtime = microtime(true);

        return (string)round($lapTime, 3);
    }

    /**
     * Writes a message to stdout.
     *
     * 04/2025 created
     */
    public static function write(string $msg, bool $bNewLine = false): void
    {
        self::getCliStyle()->write($msg, $bNewLine);
    }

    /**
     * Adds a specified number of new lines to stdout.
     */
    public static function newLine(int $count = 1): void
    {
        self::getCliStyle()->newLine($count);
    }

    /**
     * Prints a "DONE" message to stdout.
     */
    public static function done(): void
    {
        self::getCliStyle()->writeln('✨ 🌟 ✨ DONE ✨ 🌟 ✨');
    }

    /**
     * Gets the terminal width.
     */
    public static function _getTerminalWidth(): int
    {
        try {
            $cols = shell_exec('tput cols');
            if ($cols !== null) {
                return (int)trim($cols);
            }
        } catch (\Exception $e) {
            return self::DEFAULT_TERMINAL_WIDTH; // default
        }
    }

}