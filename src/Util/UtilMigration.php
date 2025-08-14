<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util;

use Doctrine\DBAL\Connection;

/**
 * A helper class for migrations to set default plugin configurations
 * without overriding existing values.
 *
 * 08/2025 created
 */
class UtilMigration
{
    /**
     * Ensures that a default value for a single plugin configuration key is set in the database,
     * but only if the key does not already exist.
     *
     * example usage:
     *
     *       // --- Option 1: Set a single default value ---
     *       UtilMigration::ensureDefaultConfig(
     *           $connection,
     *           'TopdataTopFinderProSW6', // plugin's technical name
     *           'finderBarPosition', // The config key from config.xml
     *           'belowNavigation' // The default value
     *       );
     *
     * @param Connection $connection The database connection from the migration.
     * @param string $pluginName The technical name of the plugin (e.g., 'TopdataTopFinderProSW6').
     * @param string $configKey The specific configuration key (e.g., 'finderBarPosition').
     * @param mixed $defaultValue The default value to set (will be JSON encoded).
     */
    public static function ensureDefaultConfig(Connection $connection, string $pluginName, string $configKey, mixed $defaultValue): void
    {
        $fullConfigKey = sprintf('%s.config.%s', $pluginName, $configKey);

        // --- Verbose Output ---
//        echo "--- Checking configuration key: $fullConfigKey ---\n";
        $sqlCheck = 'SELECT HEX(id) as id FROM `system_config` WHERE `configuration_key` = :configKey LIMIT 1';
        $paramsCheck = ['configKey' => $fullConfigKey];

//        echo "Query: $sqlCheck\n";
//        echo "Params: \n";
//        print_r($paramsCheck);
//        echo "\n";
        // ----------------------

        // Check if the configuration key already exists for any sales channel or as a default (NULL)
        $exists = $connection->executeQuery($sqlCheck, $paramsCheck)->fetchAssociative();

        // If the key already exists, do nothing to avoid overwriting user settings.
        if ($exists) {
//            echo "-> Key already exists (id: " . $exists['id'] . "). Skipping.\n\n";
            return;
        }

        // If the key does not exist, insert the default value.
//        echo "-> Key does not exist. Inserting default value.\n";

        // --- Verbose Output ---
        $sqlInsert = "INSERT INTO `system_config` (`id`, `configuration_key`, `configuration_value`, `sales_channel_id`, `created_at`)
             VALUES (UNHEX(REPLACE(UUID(),'-','')), :configKey, :configValue, NULL, NOW())";
        $paramsInsert = [
            'configKey'   => $fullConfigKey,
            'configValue' => json_encode(['_value' => $defaultValue]),
        ];

//        echo "Query: $sqlInsert\n";
//        echo "Params: \n";
//        print_r($paramsInsert);
//        echo "\n\n";
        // ----------------------

        $connection->executeStatement(
            $sqlInsert,
            $paramsInsert
        );
    }

    /**
     * Ensures that default values for multiple plugin configuration keys are set.
     *
     *       // --- Option 2: Set multiple default values at once ---
     *       UtilMigration::ensureDefaultConfigs(
     *           $connection,
     *           'TopdataTopFinderProSW6', // plugin's technical name
     *           [
     *               'finderBarPosition' => 'belowNavigation',
     *               'anotherConfigKey'  => true,
     *               'someOtherSetting'  => 10
     *           ]
     *       );
     *
     * @param Connection $connection The database connection from the migration.
     * @param string $pluginName The technical name of the plugin.
     * @param array<string, mixed> $configs An associative array where keys are the config keys
     *                                      and values are their default values.
     */
    public static function ensureDefaultConfigs(Connection $connection, string $pluginName, array $configs): void
    {
        foreach ($configs as $configKey => $defaultValue) {
            self::ensureDefaultConfig($connection, $pluginName, $configKey, $defaultValue);
        }
    }
}