# AGENTS.md — Topdata Foundation SW6

## Project

Shared utility library for Topdata Shopware 6 plugins (TopFeed, TopFinder, ...).

- **Plugin class**: `Topdata\TopdataFoundationSW6\TopdataFoundationSW6`
- **PSR-4 namespace**: `Topdata\TopdataFoundationSW6\` → `src/`
- **Requires**: `php ^8.2`, `shopware/core 6.5.* || 6.6.* || 6.7.*` (see `composer.json`)
- **Consumed by**: `topdata-topfeed-sw6-v9`, `topdata-topfinder-pro-sw6`

## Distribution: build-time injection, not the Shopware Store (IMPORTANT)

**This plugin is not distributed through the Shopware Store and is not installed as a runtime
dependency in production.** On release, `topdata-package-release-builder` (`sw-build`,
`foundation_injector.py`) injects a *tree-shaken copy* of the used classes into each consumer
plugin ZIP:

- only the foundation PHP classes that the consumer `use`s (plus their transitive foundation
  dependencies) are copied;
- matching service definitions are extracted from `src/Resources/config/services.xml` and injected
  with rewritten IDs;
- copied namespaces are rewritten from `Topdata\TopdataFoundationSW6\...` to
  `Topdata\<ConsumerPlugin>\Foundation\...` and autoloaded from the consumer's `src/Foundation/`;
- the `topdata/topdata-foundation-sw6` entry is removed from the consumer's built `composer.json`;
- **not** copied: `src/Migration/` and any other `Resources/` files (`config.xml`, snippets, views,
  routes).

In development the plugin is installed normally (e.g. into `custom/plugins/`), so classes autoload
under their original namespace.

Consequences for code placed here:

- **No `config.xml`, no own SystemConfig.** Never read `TopdataFoundationSW6.config.*` via
  `SystemConfigService` — those keys only exist while the dev plugin is installed, are namespaced
  per plugin, and there is no shared/global Shopware config. Accept settings as constructor or
  method parameters instead. Consumer-facing config stays in the consumer's `config.xml`; truly
  deployment-level values (e.g. an external service URL) belong in environment variables
  (`%env(...)%`), not in foundation.
- **Keep everything stateless.** Every consumer gets its own class copy under its own namespace —
  no shared class identity, no cross-plugin service instance, no runtime state registry.

## Migrations (IMPORTANT)

**Do NOT put database migrations in this plugin** — `src/Migration/` is never copied by the
injector (see above), so a migration placed here would silently be absent from the released ZIP
and the table would never be created.

Instead:

- **Shared tables** are created by an **idempotent migration in EACH consumer plugin** that needs the
  table (`CREATE TABLE IF NOT EXISTS ...` or a `hasTable()` guard), so whichever plugin installs first
  creates the table and the others no-op. Keep the schema identical across all copies.
- **Foundation** hosts only the shared *service logic* (DbHelper, matchers, webservice clients, CLI
  helpers) — those classes ARE merged into release builds because consumers `use` them.

Example: `topdata_topid_sw6id` (generic Topdata-ID ↔ SW6-ID mapping, `entity_type` = `product` | `brand`)
is created by idempotent migrations in both TopFeed and TopFinder; the DbHelper + matcher services live
here.

## Commands

| Command (cwd) | Action |
|---|---|
| `php php-cs-fixer.phar fix` (plugin root) | Fix PHP coding standards (dry-run: add `--dry-run`) |
| `./vendor/bin/phpunit --configuration="custom/plugins/topdata-foundation-sw6"` (Shopware root) | Run the unit test suite; see `ai_docs/HOWTO__phpunit.md` |

There is no `composer test` / `composer lint` script — `composer.json` only declares the platform dependency.

## Coding conventions (PHP)

See `ai_docs/CONVENTIONS-PHP.md`. Highlights:

- Private methods prefixed with `_` (e.g. `_myPrivateMethod()`)
- Class + method docblocks required (exception: getters/setters unless special)
- No redundant `@return void` or `@param` without extra info
- Type hints on all parameters and return types
- Constructor property promotion with `private readonly`
- `match` over `switch` when possible

## Debug workflow

Plugin is bind-mounted into the Docker container. Cache clear:
`docker exec focus-www rm -rf /www/var/cache/*`. For ad-hoc debug logging:
`file_put_contents('/tmp/debug.log', $msg, FILE_APPEND)` (inside container).
