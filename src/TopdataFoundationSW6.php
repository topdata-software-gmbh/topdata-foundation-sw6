<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class TopdataFoundationSW6 extends Plugin
{
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        // ---- Check if user data should be kept
        if ($context->keepUserData()) {
            return;
        }

        // ---- Get the database connection
        $connection = $this->container->get(Connection::class);

        // ---- Drop plugin-related tables if they exist
        $connection->executeStatement('DROP TABLE IF EXISTS `topdata_report`');
    }
}