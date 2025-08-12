# Implementation Plan: Plugin Version Retrieval Service

**Objective:** Implement a reliable service to retrieve the version of any installed Shopware plugin and integrate it into existing CLI commands. This plan provides all necessary code and file modifications for automated implementation.

## Phase 1: Core Logic and Service Implementation

**Goal:** Create the foundational components for version retrieval.

### 1.1. Create Plugin Utility Class

*   **Action:** Create a new file.
*   **File Path:** `src/Util/UtilPlugin.php`
*   **Rationale:** This utility class will provide helper functions for handling plugin names (e.g., converting a class name to a technical name), which keeps the main service logic cleaner and more focused.

*   **File Content:**
    ```php
    <?php
    
    namespace Topdata\TopdataFoundationSW6\Util;
    
    /**
     * 11/2024 created
     */
    class UtilPlugin
    {
    
        /**
         * Extract the plugin name from a fully qualified class name
         *
         * @param string $pluginClass The fully qualified class name of the plugin
         * @return string The plugin name
         * @throws \InvalidArgumentException If the provided class name is not a valid plugin class name
         */
        public static function extractPluginName(string $pluginClass): string
        {
            $lastNamespaceSeparator = strrpos($pluginClass, '\\');
            if ($lastNamespaceSeparator === false) {
                throw new \InvalidArgumentException('Invalid plugin class name provided');
            }
    
            return substr($pluginClass, $lastNamespaceSeparator + 1);
        }
    
        public static function isClassName(string $pluginNameOrClass): bool
        {
            return str_contains($pluginNameOrClass, '\\');
        }
    
    }
    ```

### 1.2. Enhance `PluginHelperService`

*   **Action:** Replace the entire content of the existing file.
*   **File Path:** `src/Service/PluginHelperService.php`
*   **Rationale:** This modification adds the core version retrieval logic. It injects Shopware's `PluginService` for interacting with the plugin system and adds the `getPluginVersion` method.

*   **File Content:**
    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace Topdata\TopdataFoundationSW6\Service;
    
    use Shopware\Core\Framework\Context;
    use Shopware\Core\Framework\Plugin\PluginService;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Topdata\TopdataFoundationSW6\Util\UtilPlugin;
    
    /**
     * Service for handling plugin-related operations
     *
     * @since 04/2024 PluginHelper --> PluginHelperService
     * @since 11/2024 moved from TopdataConnectorSW6 to TopdataFoundationSW6
     * @since 11/2024 refactored to use ParameterBag
     */
    class PluginHelperService
    {
        public function __construct(
            private readonly ParameterBagInterface $parameterBag,
            private readonly PluginService         $pluginService,
        )
        {
        }
    
        /**
         * 11/2024 created
         */
        public function getPluginVersion(string $pluginNameOrClass): string
        {
            if(UtilPlugin::isClassName($pluginNameOrClass)) {
                $pluginNameOrClass = UtilPlugin::extractPluginName($pluginNameOrClass);
            }
    
            $pluginEntity = $this->pluginService->getPluginByName($pluginNameOrClass, Context::createDefaultContext());
    
            return $pluginEntity?->getVersion() ?? 'unknown';
        }
    
    
    
        /**
         * Check if a plugin is currently active
         *
         * @param string $pluginNameOrClass The fully qualified class name of the plugin or the plugin name
         * @return bool True if the plugin is active, false otherwise
         */
        public function isPluginActive(string $pluginNameOrClass): bool
        {
            $activePlugins = $this->parameterBag->get('kernel.active_plugins');
    
            if (UtilPlugin::isClassName($pluginNameOrClass)) {
                // ---- it is a plugin class
                return isset($activePlugins[$pluginNameOrClass]);
            } else {
                // ---- it is a plugin name (without namespace)
                foreach ($activePlugins as $cls => $struct) {
                    if ($struct['name'] === $pluginNameOrClass) {
                        return true;
                    }
                }
    
                return false;
            }
        }
    
        /**
         * Get all active plugins
         *
         * @return array<string, mixed> Array of active plugins where key is plugin class name
         */
        public function activePlugins(): array
        {
            return $this->parameterBag->get('kernel.active_plugins');
        }
    
        /**
         * 11/2024 created
         */
        public function isTopFeedPluginAvailable(): bool
        {
            return $this->isPluginActive('Topdata\TopdataTopFeedSW6\TopdataTopFeedSW6');
        }
    
        /**
         * 11/2024 created
         */
        public function isWebserviceConnectorPluginAvailable(): bool
        {
            return $this->isPluginActive('Topdata\TopdataConnectorSW6\TopdataConnectorSW6');
        }
    
    }
    ```

## Phase 2: Integration and Verification

**Goal:** Use the new functionality in an existing feature and verify its correctness.

### 2.1. Integrate into `CliDumpService`

*   **Action:** Replace the entire content of the existing file.
*   **File Path:** `src/Service/CliDumpService.php`
*   **Rationale:** This change updates the CLI dump command's helper service to call the new `getPluginVersion` method, adding the version information to its output table.

*   **File Content:**
    ```php
    <?php
    
    namespace Topdata\TopdataFoundationSW6\Service;
    
    
    use Topdata\TopdataFoundationSW6\Util\CliLogger;
    
    /**
     * 11/2024 created
     */
    class CliDumpService
    {
    
    
        public function __construct(
            private readonly TopConfigRegistry   $topConfigRegistry,
            private readonly PluginHelperService $pluginHelperService,
        )
        {
        }
    
        /**
         * list registered plugins in a table
         *
         * 11/2024 created
         */
        public function dumpRegisteredPlugins(): void
        {
            $table = [];
            foreach ($this->topConfigRegistry->getRegisteredTopConfigs() as $topConfig) {
                $table[] = [
                    'name'    => $topConfig->getPluginName(),
                    'version' => $this->pluginHelperService->getPluginVersion($topConfig->getPluginName()),
                    'configs' => count($topConfig->getFlatConfig()),
                ];
            }
    
            CliLogger::getCliStyle()->listOfDictsAsTable($table, 'Registered Plugins');
        }
    }
    ```

### 2.2. Verification Step

*   **Action:** This is a manual verification step to be performed after the code changes are applied.
*   **Rationale:** To confirm that the changes have been successfully implemented and are working as expected.
*   **Instructions:**
    1.  Execute the following command from the Shopware project root:
        ```bash
        bin/console topdata:foundation:dump-plugin-config
        ```
    2.  When the command runs without a plugin name argument, it will display a table of registered plugins.
    3.  **Expected Outcome:** The table displayed in the console must now contain a `version` column, showing the correct version number for each plugin listed.

## Phase 3: Documentation

**Goal:** Update project documentation to reflect the new feature.

### 3.1. Update `CHANGELOG.md`

*   **Action:** Replace the entire content of the existing file.
*   **File Path:** `CHANGELOG.md`
*   **Rationale:** To keep a log of all notable changes for this project, adhering to semantic versioning.

*   **File Content:**
    ```markdown
    # Changelog
    All notable changes to this project will be documented in this file.
    
    The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
    and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

    ## [1.2.4] - Unreleased
    ### Added
    - Added a `getPluginVersion` method to `PluginHelperService` to retrieve the version of any installed plugin.
    - Added `UtilPlugin` helper class for plugin name manipulation.
    ### Changed
    - `CliDumpService` now displays the plugin version in the registered plugins list.
    
    ## [1.2.3] - 2025-05-04
    ### Added
    - IntSet and StringSet helper classes
    
    
    ## [1.1.3] - 2025-03-05
    - improved topdata_report table structure
    - Added: command to check for crashed jobs and mark them in the report table
    - Added: option to delete reports with no PID using --delete-no-pid flag
    - Added: UtilThrowable
    
    
    ## [1.1.0] - 2025-02-27
    ### Added
    - Added: new database table `report_status` + related classes
    ```

## Phase 4: Release Preparation

**Goal:** Finalize the plugin for a new release.

### 4.1. Update `composer.json` Version

*   **Action:** Replace the entire content of the existing file.
*   **File Path:** `composer.json`
*   **Rationale:** The version number in `composer.json` must be updated to match the new version being released, as documented in the changelog.

*   **File Content:**
    ```json
    {
        "name": "topdata/topdata-foundation-sw6",
        "description": "Utility classes for Shopware 6",
        "version": "1.2.4",
        "type": "shopware-platform-plugin",
        "license": "MIT",
        "authors": [
            {
                "name": "TopData Software GmbH",
                "homepage": "https://www.topdata.de",
                "role": "Manufacturer"
            }
        ],
        "require": {
            "shopware/core": "6.5.* || 6.6.* || 6.7.*"
        },
        "extra": {
            "shopware-plugin-class": "Topdata\\TopdataFoundationSW6\\TopdataFoundationSW6",
            "plugin-icon": "src/Resources/config/plugin.png",
            "copyright": "(c) by TopData Software GmbH",
            "label": {
                "de-DE": "Topdata Foundation SW6",
                "en-GB": "Topdata Foundation SW6"
            },
            "description": {
                "de-DE": "Basisplugin für Topdata Plugins",
                "en-GB": "Base plugin for Topdata plugins"
            },
            "manufacturerLink": {
                "de-DE": "https://www.topdata.de",
                "en-GB": "https://www.topdata.de"
            }
        },
        "autoload": {
            "psr-4": {
                "Topdata\\TopdataFoundationSW6\\": "src/"
            }
        }
    }
    ```

