<?php

namespace Topdata\TopdataFoundationSW6\Util;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Core\Content\TopdataReport\TopdataReportEntity;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * static class for logging .. basically a facade for the CliStyle class
 *
 * 03/2025 created
 */
class CliLogger
{
    private static ?CliStyle $_cliStyle = null;
    /**
     * see self::lap()
     */
    private static float $microtime;


    /**
     * prints to stdout
     *
     * 06/2024 created
     */
    public static function info(string $msg): void
    {
        // self::getCliStyle()->writeln("[info]\t<green>$msg</green>");
        self::writeln("[I]\t\033[34m" . $msg . "\033[0m");
    }


    public static function note(string $msg): void
    {
        // self::getCliStyle()->writeln("[notice]\t<blue>$msg</blue>");

        // blue background, white text
        self::writeln("[N]\t\033[44m\033[37m" . $msg . "\033[0m");

    }

    public static function warning(string $msg): void
    {
        // yellow background, black text
        // self::writeln("[W]\t\033[43m\033[30m" . $msg . "\033[0m");

        self::getCliStyle()->writeln("⚠️ [Warning] $msg");
    }

    public static function error(string $msg): void
    {
        // red background, white text
        // self::writeln("[E]\t\033[41m\033[37m" . $msg . "\033[0m");

        self::getCliStyle()->writeln("❌ [Error] $msg");
    }

    public static function success(string $msg): void
    {
        self::getCliStyle()->writeln("✅ Success: $msg\n");
    }


    public static function setCliStyle(CliStyle $cliStyle): void
    {
        self::$_cliStyle = $cliStyle;
    }



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
     * 02/2025 created
     */
    public static function dumpReport(TopdataReportEntity $report): void
    {
        self::getCliStyle()->dumpDict($report->getReportData());
    }


    /**
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

    public static function red(string $msg): void
    {
        self::writeln("\033[31m" . $msg . "\033[0m");
    }

    public static function blue(string $msg): void
    {
        self::writeln("\033[34m" . $msg . "\033[0m");
    }

    public static function yellow(string $msg)
    {
        self::writeln("\033[33m" . $msg . "\033[0m");
    }

    /**
     * print the progress of a task
     *
     * 01/2025 created
     * TODO: use console's progress bar:
     *       use $label as identifier in self::$mapProgressBars... if 100%, remove the progressBar instance from self::$mapProgressBars to save memory
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


    public static function debug(string $msg): void
    {
        if (self::getCliStyle()->getVerbosity() < OutputInterface::VERBOSITY_DEBUG) {
            return;
        }

        self::getCliStyle()->writeln("[debug]\t<gray>$msg</gray>");
    }

    public static function section(string $msg): void
    {
        self::getCliStyle()->section($msg);
    }


    public static function title(string $msg): void
    {
        self::getCliStyle()->title($msg);
    }


    /**
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
     * 03/2025 extracted from ProgressLoggingService
     */
    private static function isCLi(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
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
        // Get terminal width, default to 80 if can't determine
        $terminalWidth = (int)(`tput cols` ?? 80);

        // Get caller information from one level up in the stack
        $caller = self::getCallerInfo();
        $callerLength = strlen($caller);

        // Calculate padding needed
        $messageLength = strlen($message);
        $padding = max(0, $terminalWidth - $messageLength - $callerLength);

        return [
            'message' => $message,
            'padding' => str_repeat(' ', $padding),
            'caller'  => $caller
        ];
    }

    /**
     * 03/2025 extracted from ProgressLoggingService
     *
     * Helper method for logging stuff to stdout with right-aligned caller information.
     */
    public static function activity(string $msg = '.', bool $newLine = false): void
    {
        if(self::getCliStyle()->getVerbosity() < OutputInterface::VERBOSITY_DEBUG) {
            CliLogger::getCliStyle()->write($msg, $newLine);
        } else {
            // Write the message with padding and called
            $formatted = self::formatWithCaller($msg);
            CliLogger::getCliStyle()->write($formatted['message']);
            CliLogger::getCliStyle()->write($formatted['padding']);
            CliLogger::getCliStyle()->write($formatted['caller']);
        }
    }

    /**
     * 03/2025 extracted from ProgressLoggingService
     */
    public static function mem(): void
    {
        self::writeln('[' . round(memory_get_usage(true) / 1024 / 1024) . 'Mb]');
    }

    /**
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
     * 04/2025 created
     */
    public static function write(string $msg, bool $bNewLine = false): void
    {
        self::getCliStyle()->write($msg, $bNewLine);
    }

    public static function newLine(int $count = 1): void
    {
        self::getCliStyle()->newLine($count);
    }

    public function done(): void
    {
        self::getCliStyle()->writeln('✨ 🌟 ✨ DONE ✨ 🌟 ✨');
    }

}