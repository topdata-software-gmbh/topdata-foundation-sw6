# TopData Foundation SW6


## About 

Utility classes used in other TopData plugins

Notable services (since 1.5.0):
- `AbstractTopdataWebserviceV2Client` — V2-only webservice access (`api_key` sk-... auth, `/v2` path rewriting, per-request `language`). Renamed from `AbstractTopdataWebserviceClient`.
- `Helper\WebserviceV2Response` — unwraps the v2 response envelope (`{success, payload}` / `{success, error}`): `httpGet()`/`httpGetMultiple()` return the raw payload (e.g. `$response->page`, `$response->data`, `$response->match`) and throw `WebserviceResponseException` with the real error message on errors (since 08/2026 the webservice v2 API returns real HTTP status codes + the envelope).
- `CliApiCredentialPrompter` — interactive CLI collection of missing API credentials (base URL + `sk-` API key) with connection test and retry.


## Installation

1. Download the plugin
2. Upload to your Shopware 6 installation
3. Install and activate the plugin


## Requirements

- Shopware 6.5.* or 6.6.*


## License

MIT