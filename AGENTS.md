# AGENTS.md — Topdata Foundation SW6

## Project

Shared utility library for Topdata Shopware 6 plugins (TopFeed, TopFinder, ...).

- **Plugin class**: `Topdata\TopdataFoundationSW6\TopdataFoundationSW6`
- **PSR-4 namespace**: `Topdata\TopdataFoundationSW6\` → `src/`
- **Requires**: `php ^8.2`, `shopware/core 6.7.*`
- **Consumed by**: `topdata-topfeed-sw6-v9`, `topdata-topfinder-pro-sw6`

## Migrations (IMPORTANT)

**Do NOT put database migrations in this plugin.**

On release, `topdata-package-release-builder` (`sw-build`, `foundation_injector.py`) merges foundation
code into each consumer plugin by *tree-shaking*: it copies only the foundation PHP classes that are
`use`d by the consumer (plus their service definitions from `src/Resources/config/services.xml`). It
**never** copies `src/Migration/` — a migration placed here would silently be absent from the released
ZIP and the table would never be created.

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

| Command | Action |
|---|---|
| `php php-cs-fixer.phar fix` | Fix PHP coding standards (dry-run: add `--dry-run`) |

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
