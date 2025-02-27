<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1740672420CreateImportReportTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1740672420;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `topdata_report` (
                `id` BINARY(16) NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                `command_line` LONGTEXT NOT NULL,
                `started_at` DATETIME(3) NOT NULL,
                `succeeded_at` DATETIME(3) NULL,
                `report_data` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // No destructive changes
    }
}
