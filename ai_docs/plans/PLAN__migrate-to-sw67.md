# Shopware 6.7 Migration Plan for TopdataFoundationSW6

**Objective:** Update the `TopdataFoundationSW6` plugin to be fully compatible with Shopware 6.7, addressing all relevant breaking changes.

**Overall Strategy:**
*   A new Git branch will be created for Shopware 6.7 compatibility (e.g., `feat/shopware-6.7-compat`).
*   Due to the Webpack to Vite migration for administration assets, this version of the plugin will likely **only** be compatible with Shopware 6.7+. A separate version/branch will need to be maintained for Shopware 6.6 and older if backward compatibility is required.
*   Changes will be implemented phase by phase. Each step should be atomic and testable where possible.
*   Refer to official Shopware 6.7 migration guides, Doctrine DBAL upgrade documentation, and PHPUnit upgrade documentation as primary resources.
*   Log all changes made and any encountered issues meticulously.

---

## Phase 1: Initial Setup & Environment

1.  **Create New Git Branch:**
    *   Action: Create and switch to a new branch named `feat/shopware-6.7-compat` from your main development branch.

2.  **Shopware 6.7 Development Environment:**
    *   Action: Ensure a fully functional Shopware 6.7 development environment (e.g., using `dockware/dev-flex:6.7.x` or a manual setup) is available. The plugin will be tested within this environment.

3.  **Update `composer.json`:**
    *   Action: Modify `composer.json`:
        *   Change `"shopware/core": "6.5.* || 6.6.*"` to `"shopware/core": "~6.7.0"` (or the latest stable 6.7.x version).
        *   Add/Update PHP requirement: `"php": "^8.2"`.
    *   File: `composer.json`

4.  **Run Composer Update:**
    *   Action: Execute `composer update topdata/topdata-foundation-sw6 --with-dependencies` (or a full `composer update` if appropriate for the dev environment) to fetch new dependencies. Resolve any immediate conflicts.

---

## Phase 2: Backend & Core Library Adjustments

1.  **PHP 8.2+ Compatibility Review:**
    *   Action: While the plugin uses modern PHP, perform a brief scan for any syntax or functions that might have issues specifically between the current PHP target and PHP 8.2+. Pay attention to potential changes in behavior of core PHP functions. (This is likely minimal given the current codebase).

2.  **Doctrine DBAL 4.0 Upgrade:**
    *   Affected Files:
        *   `src/Migration/Migration*.php` (all migration files)
        *   `src/Service/LocaleHelperService.php`
    *   Action:
        *   Review all direct SQL queries and DBAL method calls in the identified files.
        *   Consult the Doctrine DBAL 3.x to 4.x upgrade guide for breaking changes related to:
            *   `$connection->executeStatement()`
            *   `$connection->fetchOne()`
            *   Type handling (especially for `JSON` and `DATETIME` types).
            *   Schema manipulation methods if any were used outside of `executeStatement`.
        *   Update queries and method calls as necessary.
    *   Testing: After changes, attempt to run all plugin migrations on a fresh Shopware 6.7 database. Test the functionality of `LocaleHelperService`.

3.  **PHPUnit 11 Upgrade:**
    *   Affected Files/Directories:
        *   `phpunit.xml`
        *   `tests/TestBootstrap.php`
        *   `tests/Unit/DataStructure/IntSetTest.php`
        *   `tests/Unit/DataStructure/StringSetTest.php`
    *   Action:
        *   Update `phpunit.xml` to the PHPUnit 11 schema and configuration.
        *   Review `tests/TestBootstrap.php` for any necessary adjustments for PHPUnit 11 or Shopware 6.7 test bootstrapping.
        *   Refactor the unit tests in `tests/Unit/` to be compatible with PHPUnit 11. This may include:
            *   Changes to assertion methods (e.g., `assertCount` usage, type-specific assertions).
            *   Updates to annotations or test structure.
            *   Changes in how test doubles (mocks, stubs) are created or configured if applicable (though current tests seem direct).
    *   Testing: Run all unit tests and ensure they pass in the Shopware 6.7 environment.

---

## Phase 3: Administration Frontend Migration (Webpack to Vite)

This is the most significant change for this plugin.

1.  **Remove Old Build Output (Optional but Recommended for Cleanliness):**
    *   Action: Delete the pre-compiled JavaScript file: `src/Resources/public/administration/js/topdata-foundation-s-w6.js`. Vite will generate its own output.

2.  **Create Vite Configuration:**
    *   Action: Create a new Vite configuration file.
    *   File: `src/Resources/app/administration/vite.config.js` (or `vite.config.ts`)
    *   Content:
        *   Import necessary functions from Vite and any Shopware Vite plugins/helpers.
        *   Define the entry point: `src/main.ts` (relative to `src/Resources/app/administration/`).
        *   Configure the output directory and filename to be similar to the old path (e.g., `build: { outDir: '../../public/administration/js', emptyOutDir: true, rollupOptions: { output: { entryFileNames: 'topdata-foundation-sw6.js' } } }`).
        *   Ensure it correctly handles TypeScript.
        *   Refer to Shopware 6.7 developer documentation for examples of plugin Vite configurations.

3.  **Update/Create `package.json` for Build Scripts:**
    *   Action: If a `package.json` exists at `src/Resources/app/administration/`, update its build scripts. If not, create one.
    *   File: `src/Resources/app/administration/package.json`
    *   Content (example scripts):
        ```json
        {
          "name": "topdata-foundation-sw6-admin",
          "version": "1.0.0",
          "scripts": {
            "dev": "vite",
            "build": "vite build"
          },
          "devDependencies": {
            "vite": "^5.x.x", // Use appropriate Vite version
            "@vitejs/plugin-vue": "^5.x.x", // If needed, though this plugin doesn't define Vue components
            "typescript": "^5.x.x" // Or your project's TS version
            // Add any other necessary Shopware Vite tooling/plugins
          }
        }
        ```
    *   Action: Run `npm install` (or `yarn install`) within `src/Resources/app/administration/`.

4.  **Verify TypeScript Service Compatibility:**
    *   Affected Files:
        *   `src/Resources/app/administration/src/main.ts`
        *   `src/Resources/app/administration/src/init/services.init.ts`
        *   `src/Resources/app/administration/src/service/TopdataAdminApiClient.ts`
    *   Action:
        *   Review `TopdataAdminApiClient.ts` regarding `Shopware.State.dispatch('notification/createNotification', ...)`. While this might still work as an abstraction over Pinia, verify if there's a new recommended way to interact with core Shopware Pinia stores from a service or if this remains valid.
        *   Ensure all imports and Shopware core service interactions (`Shopware.Classes.ApiService`, `Shopware.Application.getContainer('init').httpClient`, `Shopware.Service().register`) are correctly handled by Vite and remain functional in the Vue 3/Pinia context.

5.  **Build Administration Assets:**
    *   Action: Navigate to `src/Resources/app/administration/` and run the Vite build command (e.g., `npm run build`).
    *   Verify: Ensure the JavaScript output is generated in the expected location (`src/Resources/public/administration/js/`).

6.  **Test Administration Client:**
    *   Action: Manually test the functionality of `TopdataAdminApiClient`. Since this foundation plugin doesn't have its own admin UI, this might involve:
        *   Using it from another Topdata plugin that *does* have an admin UI.
        *   Temporarily adding a simple test admin module that calls this client.
        *   Checking browser console for errors when navigating the admin.

---

## Phase 4: Storefront & Twig Verification

1.  **Storefront Template Review:**
    *   Affected Files: `src/Resources/views/storefront/layout/base.html.twig` and all templates in `src/Resources/views/storefront/page/content/`.
    *   Action:
        *   Render all custom report pages (`reports.html.twig`, `detailed_report.html.twig`, `login.html.twig`) in a Shopware 6.7 storefront.
        *   Verify layout, styling, and functionality (especially login, navigation to details).
        *   Check for any errors related to Twig syntax or deprecated Shopware Twig functions/filters.

2.  **Twig Extension Review:**
    *   Affected File: `src/Twig/TopConfigTwigExtension.php`
    *   Action:
        *   Test all custom Twig functions (`topConfigGet`, `topConfigGetString`, etc.) in your storefront templates.
        *   Ensure the `TopConfigRegistry` service is correctly injected and functions as expected.
        *   Check for any changes in how Twig extensions are registered or how services are accessed within them in Shopware 6.7.

---

## Phase 5: Comprehensive Testing & Refinement

1.  **Deprecated Code Check:**
    *   Action: Perform a global search in your plugin's codebase for any calls to Shopware core methods or services that were marked `@deprecated` in versions up to 6.6. These are likely removed or will cause issues in 6.7. Update to use the recommended alternatives.

2.  **CLI Command Testing:**
    *   Affected Commands:
        *   `CheckCrashedJobsCommand.php`
        *   `DumpPluginConfigCommand.php`
        *   `SetPluginConfigCommand.php`
        *   `SetReportsPasswordCommand.php`
    *   Action: Execute each CLI command with various options and arguments in the Shopware 6.7 environment. Verify expected output and behavior.

3.  **Full Plugin Functionality Testing:**
    *   Action: Conduct end-to-end testing of all features provided by `TopdataFoundationSW6`. This includes:
        *   Report creation and lifecycle (simulating job runs).
        *   Report viewing and authentication.
        *   Configuration access through `TopConfigRegistry` (both CLI and Twig).
        *   Any other services or utilities provided.

4.  **Error Log Monitoring:**
    *   Action: During all testing phases, continuously monitor Shopware's system logs (`var/log/dev.log`, `var/log/prod.log`) and the browser's developer console for any new errors or warnings related to the plugin.

---

## Phase 6: Documentation & Release

1.  **Update `CHANGELOG.md`:**
    *   Action: Document the changes made for Shopware 6.7 compatibility and increment the plugin version (e.g., to `1.3.0` or a version scheme that denotes 6.7+ compatibility).

2.  **Update `README.md`:**
    *   Action: Update the "Requirements" section to specify Shopware 6.7.* or newer.

3.  **Update `composer.json` Version:**
    *   Action: Update the `"version"` field in `composer.json` to match the new release version.

4.  **Tag and Release:**
    *   Action: Commit all changes, merge the `feat/shopware-6.7-compat` branch (e.g., into a `6.7/main` branch or similar, depending on your branching strategy for supporting multiple Shopware major versions).
    *   Tag a new release specifically for Shopware 6.7.

---
This plan provides a structured approach. The AI agent should proceed step-by-step, thoroughly testing after each significant modification.
