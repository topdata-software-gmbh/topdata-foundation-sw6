# TopData Foundation SW6


## About 

Utility classes used in other TopData plugins

Notable services (since 1.5.0):
- `AbstractTopdataWebserviceV2Client` — V2-only webservice access (`api_key` sk-... auth, `/v2` path rewriting, per-request `language`). Renamed from `AbstractTopdataWebserviceClient`.
- `Helper\WebserviceV2Response` — unwraps the v2 response envelope (`{success, payload}` / `{success, error}`): `httpGet()`/`httpGetMultiple()` return the raw payload (e.g. `$response->page`, `$response->data`, `$response->match`) and throw `WebserviceResponseException` with the real error message on errors (since 08/2026 the webservice v2 API returns real HTTP status codes + the envelope).
- `CliApiCredentialPrompter` — interactive CLI collection of missing API credentials (base URL + `sk-` API key) with connection test and retry.


## Distribution (important)

This plugin is **not distributed through the Shopware Store**. In production it is not
installed as a runtime dependency — at release time the *Topdata Package Release Builder*
(`sw-build`, `foundation_injector.py`) merges the foundation code into every consumer
plugin ZIP:

- only the classes a consumer actually `use`s (plus their transitive foundation
  dependencies and service definitions) are copied — *tree-shaking*;
- the copied classes are rewritten from `Topdata\TopdataFoundationSW6\...` to
  `Topdata\<ConsumerPlugin>\Foundation\...` and autoloaded from the consumer's
  `src/Foundation/`;
- the `topdata/topdata-foundation-sw6` requirement is removed from the consumer's built
  `composer.json`;
- only PHP class files and their service definitions (from `services.xml`) are injected — other
  `Resources/` files (`config.xml`, snippets, views, routes) and `src/Migration/` are not.

For development the plugin is installed normally (e.g. into `custom/plugins/`).

### Consequences

- Every consumer gets its **own copy** of the classes — no shared runtime identity between
  plugins. Foundation code must stay stateless.
- Database migrations cannot live here; shared tables are created by idempotent migrations in
  each consumer plugin (see `AGENTS.md`).

## Configuration

This plugin intentionally has **no user-facing configuration**.

`config.xml` is never injected into release builds, and Shopware system config keys are
namespaced per plugin — there is no shared/global config that foundation code could read.
Foundation classes therefore receive all settings as constructor/method parameters and never
read their own SystemConfig keys. Configuration lives in the **consumer** plugin's
`config.xml`; deployment-level values (e.g. external service URLs) belong in environment
variables.


## Requirements

- Shopware 6.5.*, 6.6.* or 6.7.*


## License

MIT