<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Topdata\TopdataFoundationSW6\Service\AbstractTopdataWebserviceV2Client;

/**
 * Covers hasValidConfig() and reloadConfig() of the V2-only client.
 * Uses an in-memory SystemConfigService fake; no network involved.
 */
class AbstractTopdataWebserviceV2ClientTest extends TestCase
{
    public function testValidConfigRequiresBaseUrlAndApiKey(): void
    {
        $cases = [
            [['apiBaseUrl' => 'https://ws.example.com'], false],                                  // no api key
            [['apiKey' => 'sk-' . str_repeat('a', 32)], false],                                   // no base url
            [[], false],
            [['apiBaseUrl' => 'https://ws.example.com', 'apiKey' => 'sk-' . str_repeat('a', 32)], true],
        ];

        foreach ($cases as [$config, $expected]) {
            $client = new _FakeWebserviceV2Client(new _FakeSystemConfigService(['TestPlugin.config' => $config]));
            $this->assertSame($expected, $client->hasValidConfig(), 'config: ' . json_encode($config));
        }
    }

    public function testReloadConfigPicksUpChangedValues(): void
    {
        $configService = new _FakeSystemConfigService(['TestPlugin.config' => [
            'apiBaseUrl' => 'https://ws.example.com',
            'apiKey'     => 'sk-' . str_repeat('a', 32),
        ]]);
        $client = new _FakeWebserviceV2Client($configService);
        $this->assertTrue($client->hasValidConfig());

        $configService->setMultiple(['TestPlugin.config.apiKey' => '']);
        $this->assertTrue($client->hasValidConfig(), 'stale in-memory values before reload');
        $client->reloadConfig();
        $this->assertFalse($client->hasValidConfig(), 'reloadConfig must re-read system config');
    }

    public function testReloadConfigDerivesKeyFromConnectorV1Credentials(): void
    {
        $configService = new _FakeSystemConfigService([
            'TestPlugin.config' => [
                'apiBaseUrl' => 'https://ws.example.com',
                'apiKey'     => '',
            ],
            'TopdataConnectorSW6.config' => [
                'apiUid'         => 6,
                'apiSecurityKey' => 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox',
            ],
        ]);
        $client = new _FakeWebserviceV2Client($configService);

        $this->assertTrue($client->hasValidConfig(), 'key must be derived from connector v1 credentials');
        $this->assertSame('sk-tdws-EAvAHSJZzYgCc2FptDzJjDHtpFKmYhxXW6cvzHwnRGcvo', $client->getApiKey());
    }

    public function testPluginApiKeyTakesPrecedenceOverDerivation(): void
    {
        $configService = new _FakeSystemConfigService([
            'TestPlugin.config' => [
                'apiBaseUrl' => 'https://ws.example.com',
                'apiKey'     => 'sk-tdws-' . str_repeat('x', 45),
            ],
            'TopdataConnectorSW6.config' => [
                'apiUid'         => 6,
                'apiSecurityKey' => 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox',
            ],
        ]);
        $client = new _FakeWebserviceV2Client($configService);

        $this->assertSame('sk-tdws-' . str_repeat('x', 45), $client->getApiKey());
    }

    public function testReloadConfigWithoutConnectorCredentialsStaysInvalid(): void
    {
        $configService = new _FakeSystemConfigService(['TestPlugin.config' => [
            'apiBaseUrl' => 'https://ws.example.com',
            'apiKey'     => '',
        ]]);
        $client = new _FakeWebserviceV2Client($configService);

        $this->assertFalse($client->hasValidConfig());
    }
}

final class _FakeSystemConfigService extends SystemConfigService
{
    public array $values = [];

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function get(string $key, ?string $salesChannelId = null)
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, $value, ?string $salesChannelId = null): void
    {
        $this->values[$key] = $value;
    }

    public function setMultiple(array $values, ?string $salesChannelId = null): void
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }
    }

    public function delete(string $key, ?string $salesChannelId = null): void
    {
        unset($this->values[$key]);
    }
}

final class _FakeWebserviceV2Client extends AbstractTopdataWebserviceV2Client
{
    public function __construct(SystemConfigService $systemConfigService)
    {
        parent::__construct($systemConfigService, 'TestPlugin.config');
    }
}
