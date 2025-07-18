<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1740672422AddJobFieldsToTopdataReport extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1740672422;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE topdata_report
            ADD COLUMN job_type VARCHAR(255) NOT NULL DEFAULT \'UNKNOWN\',
            ADD COLUMN pid INT DEFAULT NULL;
        ');

        $connection->executeStatement('
            ALTER TABLE topdata_report RENAME COLUMN `status` TO job_status;
        ');

    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
