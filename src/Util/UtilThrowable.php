<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util;

use Throwable;

class UtilThrowable
{
    /**
     * Converts an exception into an associative array for logging or storage.
     *
     * @param Throwable $exception The exception to convert
     * @return array<string, mixed> The structured exception data
     */
    public static function toArray(Throwable $exception): array
    {
        return [
            'message'  => $exception->getMessage(),
            'code'     => $exception->getCode(),
            'file'     => $exception->getFile(),
            'line'     => $exception->getLine(),
            'trace'    => self::formatTrace($exception->getTrace()),
            'previous' => $exception->getPrevious() ? self::toArray($exception->getPrevious()) : null,
        ];
    }

    /**
     * Formats a stack trace into a more readable format.
     *
     * @param array<int, array<string, mixed>> $trace The stack trace
     * @return array<int, string> Readable stack trace lines
     */
    private static function formatTrace(array $trace): array
    {
        $formatted = [];

        foreach ($trace as $index => $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? '?';
            $function = $frame['function'] ?? 'unknown';
            $class = $frame['class'] ?? null;
            $type = $frame['type'] ?? '';

            $formatted[] = sprintf(
                '#%d %s(%s): %s%s%s()',
                $index,
                $file,
                $line,
                $class,
                $type,
                $function
            );
        }

        return $formatted;
    }
}
