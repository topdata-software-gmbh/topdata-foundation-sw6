# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.6.0] - 2026-09-11
### Added
- `UtilBatchDatabaseOperations`: accumulates `upsertOne()` calls and flushes them as multi-row `INSERT ... ON DUPLICATE KEY UPDATE` statements (auto-flush on row count/byte thresholds; bound parameters for binary-safe UUID handling)
- `UtilApiKeyDeriver`: derives the Topdata webservice V2 API key (`sk-tdws-...`) deterministically from the v1 credentials (`uid` + `security_key`) — byte-identical to the server-side backfill migration (pure-PHP base-54 encoding, no gmp/bcmath dependency)
- `AbstractTopdataWebserviceV2Client::reloadConfig()`: when the plugin config holds no `apiKey`, the key is derived on the fly from the TopdataConnectorSW6 v1 credentials (`apiUid`/`apiSecurityKey`) — zero-touch v2 switch for existing connector users
- `CliApiCredentialPrompter`: offers to derive the v2 API key automatically from the v1 credentials (primary path) when they are present; manual entry remains as fallback
- `WebserviceV2Response` helper: unwraps the v2 response envelope (`{success, payload}` / `{success, error}`) so client call sites keep working on the raw payload objects, and throws `WebserviceResponseException` on unchecked error envelopes
- `UtilMigration::forceConfig()` / `forceConfigs()`: upsert global `system_config` rows (insert when absent, overwrite when present) via explicit SELECT-then-UPDATE/INSERT — chosen over `ON DUPLICATE KEY` because the unique index never deduplicates global rows (MySQL treats `NULL` `sales_channel_id` as distinct)
- `UtilCliPagination`: renders the "Showing X-Y of Z total ..." summary line used by the list commands

### Fixed
- `AbstractTopdataWebserviceV2Client`: removed the unneeded `version=108` parameter from V2 request URLs
- `CliApiCredentialPrompter`: API key input is displayed and validated correctly in the CLI prompter

### Changed
- `AbstractTopdataWebserviceV2Client`: `filter` is no longer a mandatory request URL parameter

## [1.5.0] - 2026-08-11
### Added
- `AbstractTopdataWebserviceV2Client` (renamed from `AbstractTopdataWebserviceClient`): V2-only webservice access — `api_key` (sk-...) auth, `/v2` path rewriting (`_` → `-`), per-request `language` parameter (shop-driven, no config language)
- `CliApiCredentialPrompter` service: interactive CLI collection of missing API credentials (base URL + `sk-` API key) with connection test and retry
- `AbstractTopdataWebserviceV2Client::reloadConfig()` to re-read plugin config at runtime
- `AbstractTopdataWebserviceV2Client::testConnection()` lightweight credential verification via `/revision` (endpoint overridable via `getPingEndpoint()`)

### Removed
- `AbstractTopdataWebserviceClient` (renamed; V1 auth fields `uid`/`security_key`/`password` and the dual-mode flag removed — V2-only)

## [1.3.0] - 2026-07-10
### Added
- `WebserviceRequestException` and `WebserviceResponseException` exception classes
- `CurlHttpClient` helper (moved from webservice-connector plugin)
- `AbstractTopdataWebserviceClient` abstract base class for webservice access

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

[1.6.0]: https://github.com/topdata-software-gmbh/topdata-foundation-sw6/compare/1.5.0...1.6.0
