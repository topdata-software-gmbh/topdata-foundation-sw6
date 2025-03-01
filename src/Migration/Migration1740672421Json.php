<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1740672421Json extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1740672421;
    }

    public function update(Connection $connection): void
    {
        // First, ensure all existing data is valid JSON
        $connection->executeStatement("
            UPDATE `topdata_report` 
            SET `report_data` = '{}' 
            WHERE `report_data` IS NULL OR `report_data` = ''
        ");

        // Then, update the column to be JSON
        $connection->executeStatement("
            ALTER TABLE `topdata_report`
            MODIFY COLUMN `report_data` JSON NOT NULL DEFAULT '{}';
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // No destructive changes
    }
}
