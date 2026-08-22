<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\Helper\CurlHttpClient;
use Topdata\TopdataFoundationSW6\Helper\WebserviceV2Response;
use Topdata\TopdataFoundationSW6\Util\UtilApiKeyDeriver;

abstract class AbstractTopdataWebserviceV2Client
{
    private CurlHttpClient $curlHttpClient;
    private string $apiBaseUrl = '';
    private string $apiKey     = '';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly string $pluginConfigKey,
    ) {
        $this->reloadConfig();
        $this->curlHttpClient = new CurlHttpClient();
    }

    /**
     * Re-reads the plugin configuration from the system config.
     * Required after credentials were changed at runtime (e.g. by CliApiCredentialPrompter).
     *
     * Zero-touch v2 migration: when this plugin's config holds no apiKey,
     * the key is derived on the fly from the v1 credentials still present
     * in the TopdataConnectorSW6 plugin config (apiUid/apiSecurityKey).
     *
     * 08/2026 created
     */
    public function reloadConfig(): void
    {
        $pluginConfig = $this->systemConfigService->get($this->pluginConfigKey);
        if ($pluginConfig) {
            $this->apiBaseUrl = rtrim($pluginConfig['apiBaseUrl'] ?? '', '/') ?? '';
            $this->apiKey     = $pluginConfig['apiKey'] ?? '';
        }

        if (empty($this->apiKey)) {
            $this->apiKey = UtilApiKeyDeriver::deriveFromConnectorConfig(
                $this->systemConfigService->get('TopdataConnectorSW6.config')
            );
        }
    }

    /**
     * Performs a lightweight authenticated request against the ping endpoint
     * to verify that the API credentials are valid.
     * Uses a short-timeout client without retries so a failed test fails fast.
     * v2 errors arrive as 4xx/5xx with a JSON error envelope; CurlHttpClient
     * throws WebserviceResponseException with the real error message, which
     * CliApiCredentialPrompter displays to the user.
     *
     * @throws \Throwable when the connection fails or the credentials are rejected
     *
     * 08/2026 created
     */
    public function testConnection(string $language = 'de'): void
    {
        $client = new CurlHttpClient(5, 0, 1);
        $client->get($this->_buildUrl($this->getPingEndpoint(), [], $language));
    }

    /**
     * Endpoint used by testConnection() (/revision → /v2/revision, V2-exclusive).
     * Subclasses may override when the webservice does not provide this endpoint.
     *
     * 08/2026 created
     */
    protected function getPingEndpoint(): string
    {
        return '/revision';
    }

    /**
     * Performs a request and unwraps the v2 envelope: returns the payload
     * (e.g. `->page`, `->data`, `->match` reads keep working); errors throw
     * WebserviceResponseException with the real error message.
     *
     * @param string $language ISO-2 language to request (shop-derived, never from config)
     */
    protected function httpGet(string $endpoint, array $params = [], string $language = 'de'): mixed
    {
        $response = $this->curlHttpClient->get($this->_buildUrl($endpoint, $params, $language));

        return WebserviceV2Response::unwrap($response);
    }

    /**
     * Fetch multiple endpoints concurrently with curl_multi_*.
     * Each entry in $requests must be [endpoint, params] or [endpoint].
     * Returns array indexed parallel to $requests; null on per-request failure.
     * Each entry is unwrapped like httpGet(): payload returned, error envelopes throw.
     *
     * @param array<int, array{0:string, 1?: array<string, mixed>}> $requests
     * @param int $concurrency max simultaneous connections
     * @param string $language ISO-2 language to request (shop-derived, never from config)
     * @return array<int, mixed|null>
     */
    protected function httpGetMultiple(array $requests, int $concurrency = 20, string $language = 'de'): array
    {
        $urls = [];
        foreach ($requests as $req) {
            $urls[] = $this->_buildUrl($req[0], $req[1] ?? [], $language);
        }

        $responses = $this->curlHttpClient->getMultiple($urls, $concurrency);

        foreach ($responses as $idx => $response) {
            $responses[$idx] = WebserviceV2Response::unwrap($response);
        }

        return $responses;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->apiBaseUrl = $baseUrl;
    }

    public function getBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    /**
     * Returns the API key in use — either from the plugin config or derived
     * from the connector v1 credentials. May be '' when nothing is configured.
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Checks whether the credentials required for authenticated requests are configured.
     * The key's format/validity is verified by the connection test, not here.
     *
     * 08/2026 created
     */
    public function hasValidConfig(): bool
    {
        return !empty($this->apiBaseUrl)
            && !empty($this->apiKey);
    }

    /**
     * Builds the full webservice V2 request URL: /v2 path rewriting
     * (`_` → `-`), api_key auth, mandatory language.
     *
     * The `filter` param is intentionally NOT injected here: it is only
     * meaningful for product-payload endpoints and endpoint-specific,
     * so dedicated clients (e.g. TopFeed) pass it per request.
     *
     * 08/2026 created (V2-only rewrite of the legacy _buildUrl)
     */
    private function _buildUrl(string $endpoint, array $params = [], string $language = 'de'): string
    {
        $params = array_merge($params, [
            'api_key'  => $this->apiKey,
            'language' => $language,
        ]);

        $path = '/v2' . str_replace('_', '-', $endpoint);

        return $this->apiBaseUrl . $path . '?' . http_build_query($params);
    }
}
