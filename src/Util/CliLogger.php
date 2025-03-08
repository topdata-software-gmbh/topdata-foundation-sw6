<?php

namespace Topdata\TopdataFoundationSW6\Util;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Topdata\TopdataFoundationSW6\Core\Content\TopdataReport\TopdataReportEntity;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * static class for logging .. basically a facade for the CliStyle class
 *
 * 03/2025 created
 */
class CliLogger
{
    private static CliStyle $_cliStyle;
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


    public static function notice(string $msg): void
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


    public static function getCliStyle(): CliStyle
    {
        if (!isset(self::$_cliStyle)) {
            self::$_cliStyle = new CliStyle(new ArrayInput([]), new ConsoleOutput());
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
     */
    public static function progress(int $current, int $total): void
    {
        $percentFormatted = round($current / $total * 100, 1) . '%';
        $currentFormatted = number_format($current, 0, ',', '.');
        $totalFormatted   = number_format($total, 0, ',', '.');
        self::writeln("$currentFormatted / $totalFormatted - $percentFormatted");
    }





    public static function debug(string $msg): void
    {
        self::getCliStyle()->writeln("[debug]\t<gray>$msg</gray>");

        // gray text
        // self::writeln("[D]\t\033[30m" . $msg . "\033[0m");
    }

    public static function section(string $msg): void
    {
        // v1:
        // self::writeln('');
        // self::writeln("---- $msg ----");
        // self::writeln('');
        // v2:
//        self::getCliStyle()->write("\n\n" . str_repeat('=', strlen($msg)) . "\n");
//        self::getCliStyle()->write($msg . "\n");
//        self::getCliStyle()->write(str_repeat('=', strlen($msg)) . "\n\n");
        // v3:
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
        if(self::isCLi()) {
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
     * 03/2025 extracted from ProgressLoggingService
     */
    private static function _getCaller()
    {
        $ddSource = debug_backtrace()[1];

        return basename($ddSource['file']) . ':' . $ddSource['line'] . self::getNewline();
    }

    /**
     * 03/2025 extracted from ProgressLoggingService
     *
     * Helper method for logging stuff to stdout with right-aligned caller information.
     */
    public static function activity(string $str = '.', bool $newLine = false): void
    {
        // Get terminal width, default to 80 if can't determine
        $terminalWidth = (int) (`tput cols` ?? 80);
        // Get caller information
        $caller = self::_getCaller();
        $callerLength = strlen($caller);

        // Calculate padding needed
        $messageLength = strlen($str);
        $padding = max(0, $terminalWidth - $messageLength - $callerLength);

        // Write the message, padding, and caller
        CliLogger::getCliStyle()->write($str);
        CliLogger::getCliStyle()->write(str_repeat(' ', $padding));
        CliLogger::getCliStyle()->write($caller, $newLine);
    }

    /**
     * 03/2025 extracted from ProgressLoggingService
     */
    public static function mem(): void
    {
        self::activity('[' . round(memory_get_usage(true) / 1024 / 1024) . 'Mb]');
    }

    /**
     * 03/2025 extracted from ProgressLoggingService
     */
    public static  function lap($start = false): string
    {
        if ($start) {
            self::$microtime = microtime(true);

            return '';
        }
        $lapTime = microtime(true) - self::$microtime;
        self::$microtime = microtime(true);

        return (string)round($lapTime, 3);
    }

    public function done(): void
    {
        self::getCliStyle()->writeln('✨ 🌟 ✨ DONE ✨ 🌟 ✨');
    }


}
