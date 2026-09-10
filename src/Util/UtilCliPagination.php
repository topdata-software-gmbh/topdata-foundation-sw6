<?php

namespace Topdata\TopdataFoundationSW6\Util;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renders the "Showing X-Y of Z total ..." pagination summary line used by
 * the list commands (e.g. topdata:enhanced-search:list-synonyms, list-suggestions).
 *
 * 09/2026 created
 */
final class UtilCliPagination
{
    /**
     * Formats the "Showing X-Y of Z total <label>." summary line.
     */
    public static function formatSummary(int $offset, int $limit, int $total, string $label): string
    {
        if ($total <= 0) {
            return sprintf('Showing 0-0 of 0 total %s.', $label);
        }

        return sprintf('Showing %d-%d of %d total %s.', $offset + 1, min($offset + $limit, $total), $total, $label);
    }

    /**
     * Writes the formatted summary line to the console.
     */
    public static function renderSummary(OutputInterface $output, int $offset, int $limit, int $total, string $label): void
    {
        $output->writeln(self::formatSummary($offset, $limit, $total, $label));
    }
}