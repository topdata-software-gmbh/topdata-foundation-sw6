<?php
declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\Helper\CurlHttpClient;

abstract class AbstractTopdataWebserviceClient
{
    public const API_VERSION = '108';

    private CurlHttpClient $curlHttpClient;
    private string $apiBaseUrl = '';
    private string $apiUid = '';
    private string $apiPassword = '';
    private string $apiSecurityKey = '';
    private string $apiLanguage = 'de';

    public function __construct(
        SystemConfigService $systemConfigService,
        string $pluginConfigKey,
    ) {
        $pluginConfig = $systemConfigService->get($pluginConfigKey);
        if ($pluginConfig) {
            $this->apiBaseUrl = rtrim($pluginConfig['apiBaseUrl'] ?? '', '/') ?? '';
            $this->apiUid = $pluginConfig['apiUid'] ?? '';
            $this->apiPassword = $pluginConfig['apiPassword'] ?? '';
            $this->apiSecurityKey = $pluginConfig['apiSecurityKey'] ?? '';
            $this->apiLanguage = $pluginConfig['apiLanguage'] ?? 'de';
        }
        $this->curlHttpClient = new CurlHttpClient();
    }

    protected function httpGet(string $endpoint, array $params = []): mixed
    {
        $params = array_merge($params, [
            'uid'          => $this->apiUid,
            'security_key' => $this->apiSecurityKey,
            'password'     => $this->apiPassword,
            'version'      => static::API_VERSION,
            'language'     => $this->apiLanguage,
            'filter'       => 'all',
        ]);
        $url = $this->apiBaseUrl . $endpoint . '?' . http_build_query($params);

        return $this->curlHttpClient->get($url);
    }

    /**
     * Fetch multiple endpoints concurrently with curl_multi_*.
     * Each entry in $requests must be [endpoint, params] or [endpoint].
     * Returns array indexed parallel to $requests; null on per-request failure.
     *
     * @param array<int, array{0:string, 1?: array<string, mixed>}> $requests
     * @param int $concurrency max simultaneous connections
     * @return array<int, mixed|null>
     */
    protected function httpGetMultiple(array $requests, int $concurrency = 20): array
    {
        $urls = [];
        foreach ($requests as $req) {
            $endpoint = $req[0];
            $params = array_merge($req[1] ?? [], [
                'uid'          => $this->apiUid,
                'security_key' => $this->apiSecurityKey,
                'password'     => $this->apiPassword,
                'version'      => static::API_VERSION,
                'language'     => $this->apiLanguage,
                'filter'       => 'all',
            ]);
            $urls[] = $this->apiBaseUrl . $endpoint . '?' . http_build_query($params);
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

    public function hasValidConfig(): bool
    {
        return !empty($this->apiUid) && !empty($this->apiPassword) && !empty($this->apiBaseUrl);
    }
}
