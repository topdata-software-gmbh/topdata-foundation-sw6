# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- `UtilBatchDatabaseOperations`: accumulates `upsertOne()` calls and flushes them as multi-row `INSERT ... ON DUPLICATE KEY UPDATE` statements (auto-flush on row count/byte thresholds; bound parameters for binary-safe UUID handling)

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
