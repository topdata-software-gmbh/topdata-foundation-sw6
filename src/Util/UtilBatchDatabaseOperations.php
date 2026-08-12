<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util;

use Doctrine\DBAL\Connection;

/**
 * Accumulates upserts in memory and flushes them as one multi-row
 * INSERT ... ON DUPLICATE KEY UPDATE statement per table/column-set group.
 *
 * Reduces the number of round trips and the lock window for bulk imports
 * (e.g. finder import commands). Adapted from t2-app's BatchDatabaseOperationsV2
 * with bound parameters instead of quote() so binary(16) UUID columns are handled safely.
 *
 * 08/2026 created
 */
class UtilBatchDatabaseOperations
{
    private int $updateCountThreshold = 5000;
    private int $currentUpdateCount   = 0;
    private int $updateByteThreshold  = 500000;
    private int $currentByteCount     = 0;

    private array $pendingRecords = [];

    public function __construct(
        private readonly Connection $connection,
        ?int $updateCountThreshold = null,
        ?int $updateByteThreshold = null,
    ) {
        if ($updateCountThreshold !== null) {
            $this->updateCountThreshold = $updateCountThreshold;
        }
        if ($updateByteThreshold !== null) {
            $this->updateByteThreshold = $updateByteThreshold;
        }
    }

    /**
     * Queues a single row for the next flush.
     *
     * Auto-flushes when the row count or byte thresholds are exceeded.
     * On duplicate keys the existing row is updated with the new values.
     *
     * @param array<string, mixed> $rowData
     */
    public function upsertOne(string $tableName, array $rowData): bool
    {
        ksort($rowData);
        $columnHash                                      = md5(implode('|', array_keys($rowData)));
        $this->pendingRecords[$tableName][$columnHash][] = $rowData;

        $this->currentByteCount += strlen(implode('', $rowData));
        $this->currentUpdateCount++;

        if ($this->currentByteCount > $this->updateByteThreshold || $this->currentUpdateCount > $this->updateCountThreshold) {
            $this->flush();
        }

        return true;
    }

    /**
     * Executes all queued rows as multi-row INSERT ... ON DUPLICATE KEY UPDATE statements.
     */
    public function flush(): void
    {
        foreach ($this->pendingRecords as $tableName => $columnGroups) {
            foreach ($columnGroups as $rows) {
                $this->_executeBatch($tableName, $rows);
            }
        }

        $this->_reset();
    }

    /**
     * Executes one multi-row INSERT ... ON DUPLICATE KEY UPDATE for a group of rows
     * with identical column sets. Values are bound as parameters (not quoted).
     *
     * @param array<array<string, mixed>> $rows
     */
    private function _executeBatch(string $tableName, array $rows): void
    {
        $columns    = array_keys($rows[0]);
        $columnList = implode(', ', array_map(static fn (string $c): string => '`' . $c . '`', $columns));

        $valueLists = [];
        $params     = [];
        foreach ($rows as $row) {
            $valueLists[] = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
            foreach ($columns as $column) {
                $params[] = $row[$column];
            }
        }

        $this->connection->executeStatement(
            'INSERT INTO `' . $tableName . '` (' . $columnList . ') VALUES ' . implode(', ', $valueLists)
                . ' ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(
                    static fn (string $c): string => '`' . $c . '`=VALUES(`' . $c . '`)',
                    $columns
                )),
            $params
        );
    }

    private function _reset(): void
    {
        $this->currentByteCount   = 0;
        $this->currentUpdateCount = 0;
        $this->pendingRecords     = [];
    }
}
