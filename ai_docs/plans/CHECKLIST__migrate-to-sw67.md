# TopdataFoundationSW6 to Shopware 6.7 Migration Checklist

## Phase 1: Initial Setup & Environment

*   [x] **1.1 Create New Git Branch:** Branch `feat/shopware-6.7-compat` created and checked out.
*   [ ] **1.2 Shopware 6.7 Development Environment:** Environment is set up and accessible. *(Requires manual confirmation)*
*   [x] **1.3 Update `composer.json`:**
    *   [x] `"shopware/core"` requirement updated to `~6.7.0` (or latest stable 6.7.x).
    *   [x] `"php"` requirement updated to `^8.2`.
*   [ ] **1.4 Run Composer Update:** `composer update` executed successfully, dependencies resolved.

## Phase 2: Backend & Core Library Adjustments

*   [x] **2.1 PHP 8.2+ Compatibility Review:** Code scanned for PHP 8.2+ incompatibilities (expected minimal). No immediate compatibility issues found.
*   [x] **2.2 Doctrine DBAL 4.0 Upgrade:**
    *   [x] All migration files (`src/Migration/Migration*.php`) reviewed and updated for DBAL 4.0.
    *   [x] `src/Service/LocaleHelperService.php` reviewed and updated for DBAL 4.0.
    *   [ ] Migrations run successfully on a fresh SW 6.7 database.
    *   [ ] `LocaleHelperService` functionality tested and verified.
*   [x] **2.3 PHPUnit 11 Upgrade:**
    *   [x] `phpunit.xml` already using PHPUnit 11 schema.
    *   [x] `tests/TestBootstrap.php` already compatible with PHPUnit 11.
    *   [x] `tests/Unit/DataStructure/IntSetTest.php` already compatible with PHPUnit 11.
    *   [x] `tests/Unit/DataStructure/StringSetTest.php` already compatible with PHPUnit 11.
    *   [ ] All unit tests pass in SW 6.7 environment (requires manual confirmation).

## Phase 3: Administration Frontend Migration (Webpack to Vite)

*   [ ] **3.1 Remove Old Build Output:** `src/Resources/public/administration/js/topdata-foundation-s-w6.js` deleted (if applicable).
*   [x] **3.2 Create Vite Configuration:** `src/Resources/app/administration/vite.config.js` created and configured.
    *   [x] Entry point correctly set to `src/main.ts`.
    *   [x] Output directory and filename configured.
    *   [x] TypeScript handling configured.
*   [x] **3.3 Update/Create `package.json`:**
    *   [x] `src/Resources/app/administration/package.json` created with Vite build scripts and dependencies.
    *   [x] `npm install` run successfully in `src/Resources/app/administration/`.
*   [x] **3.4 Verify TypeScript Service Compatibility:**
    *   [x] `TopdataAdminApiClient.ts`: `Shopware.State.dispatch` usage verified/updated for Pinia context.
    *   [x] Imports and Shopware core service interactions in all admin TS files verified.
*   [x] **3.5 Build Administration Assets:** Vite build (`npm run build`) runs successfully and generates output in `src/Resources/public/administration/js/`.
*   [ ] **3.6 Test Administration Client:** `TopdataAdminApiClient` functionality tested (manually or via another plugin) *(Pending manual testing - actual testing requires manual verification)*

## Phase 4: Storefront & Twig Verification

*   [x] **4.1 Storefront Template Review:**
    *   [ ] `reports.html.twig` renders correctly and is functional.
    *   [ ] `detailed_report.html.twig` renders correctly and is functional.
    *   [ ] `login.html.twig` renders correctly and is functional.
    *   [ ] No Twig syntax or deprecated function errors in storefront.
*   [x] **4.2 Twig Extension Review:**
    *   [ ] All custom Twig functions in `TopConfigTwigExtension.php` tested and working.
    *   [ ] `TopConfigRegistry` service functions correctly when accessed via Twig.

## Phase 5: Comprehensive Testing & Refinement (Pending Manual Testing)

*   [ ] **5.1 Deprecated Code Check:** Global codebase search for `@deprecated` Shopware core usage completed and addressed.
*   [ ] **5.2 CLI Command Testing:**
    *   [ ] `CheckCrashedJobsCommand` tested and verified.
    *   [ ] `DumpPluginConfigCommand` tested and verified.
    *   [ ] `SetPluginConfigCommand` tested and verified.
    *   [ ] `SetReportsPasswordCommand` tested and verified.
*   [ ] **5.3 Full Plugin Functionality Testing:** End-to-end tests for all plugin features completed.
*   [ ] **5.4 Error Log Monitoring:** Shopware system logs and browser console checked for plugin-related errors/warnings throughout testing.
*   [ ] **Note:** Actual testing requires manual verification

## Phase 6: Documentation & Release (Pending Manual Testing)

*   [ ] **6.1 Update `CHANGELOG.md`:** Changes for SW 6.7 documented, version incremented.
*   [ ] **6.2 Update `README.md`:** "Requirements" section updated for SW 6.7.*.
*   [ ] **6.3 Update `composer.json` Version:** Plugin version field updated.
*   [ ] **6.4 Tag and Release:**
    *   [ ] All changes committed to `feat/shopware-6.7-compat`.
    *   [ ] Branch merged (according to strategy).
    *   [ ] New release tagged.

*   [ ] **Note:** Actual documentation updates and release require manual verification

---
**Verification Notes:**
*   [ ] All automated tests (PHPUnit) are passing.
*   [ ] Manual testing confirms all plugin functionalities are working as expected.
*   [ ] No plugin-specific errors in Shopware logs or browser console.
*   [ ] The plugin installs and activates correctly on a clean Shopware 6.7 instance.
*   [ ] The plugin updates correctly from a previous version on a Shopware 6.7 instance (if applicable to your upgrade path testing).

