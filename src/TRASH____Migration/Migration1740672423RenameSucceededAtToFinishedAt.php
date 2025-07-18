<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1740672423RenameSucceededAtToFinishedAt extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1740672423;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE topdata_report 
            RENAME COLUMN succeeded_at TO finished_at
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
