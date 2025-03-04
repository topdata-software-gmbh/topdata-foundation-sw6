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
        if(php_sapi_name() !== 'cli') {
            dump(...func_get_args());
        }
    }

}
