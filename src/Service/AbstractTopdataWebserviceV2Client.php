<?php
declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\Helper\CurlHttpClient;

abstract class AbstractTopdataWebserviceV2Client
{
    public const API_VERSION = '108';

    private CurlHttpClient $curlHttpClient;
    private string $apiBaseUrl = '';
    private string $apiKey = '';

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
     * 08/2026 created
     */
    public function reloadConfig(): void
    {
        $pluginConfig = $this->systemConfigService->get($this->pluginConfigKey);
        if ($pluginConfig) {
            $this->apiBaseUrl = rtrim($pluginConfig['apiBaseUrl'] ?? '', '/') ?? '';
            $this->apiKey = $pluginConfig['apiKey'] ?? '';
        }
    }

    /**
     * Performs a lightweight authenticated request against the ping endpoint
     * to verify that the API credentials are valid.
     * Uses a short-timeout client without retries so a failed test fails fast.
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
     * @param string $language ISO-2 language to request (shop-derived, never from config)
     */
    protected function httpGet(string $endpoint, array $params = [], string $language = 'de'): mixed
    {
        return $this->curlHttpClient->get($this->_buildUrl($endpoint, $params, $language));
    }

    /**
     * Fetch multiple endpoints concurrently with curl_multi_*.
     * Each entry in $requests must be [endpoint, params] or [endpoint].
     * Returns array indexed parallel to $requests; null on per-request failure.
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

        return $this->curlHttpClient->getMultiple($urls, $concurrency);
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
     * (`_` → `-`), api_key auth, mandatory version/filter/language.
     *
     * 08/2026 created (V2-only rewrite of the legacy _buildUrl)
     */
    private function _buildUrl(string $endpoint, array $params = [], string $language = 'de'): string
    {
        $params = array_merge($params, [
            'api_key'  => $this->apiKey,
            'version'  => static::API_VERSION,
            'language' => $language,
            'filter'   => 'all',
        ]);

        $path = '/v2' . str_replace('_', '-', $endpoint);

        return $this->apiBaseUrl . $path . '?' . http_build_query($params);
    }
}