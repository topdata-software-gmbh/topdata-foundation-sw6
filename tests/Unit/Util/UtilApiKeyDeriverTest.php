<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Topdata\TopdataFoundationSW6\Util\UtilApiKeyDeriver;

/**
 * Tests the v2 API key derivation. The expected key for (uid 6, demo security
 * key) was verified against the t2-app migration output (Version20260819152026)
 * on the dev database — the SW6 deriver and the server-side backfill must stay
 * byte-identical.
 */
class UtilApiKeyDeriverTest extends TestCase
{
    public function testDeriveProducesKeyIdenticalToMigrationBackfill(): void
    {
        $this->assertSame(
            'sk-tdws-EAvAHSJZzYgCc2FptDzJjDHtpFKmYhxXW6cvzHwnRGcvo',
            UtilApiKeyDeriver::derive(6, 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox')
        );
    }

    public function testDeriveIsDeterministicAndHasExpectedShape(): void
    {
        $key = UtilApiKeyDeriver::derive(19, '53d756jqugsbhbeqjwgsuumfp5jino9fic0wm33l');

        $this->assertSame(53, strlen($key));
        $this->assertStringStartsWith('sk-tdws-', $key);
        $this->assertSame($key, UtilApiKeyDeriver::derive(19, '53d756jqugsbhbeqjwgsuumfp5jino9fic0wm33l'));
        $this->assertNotSame($key, UtilApiKeyDeriver::derive(19, 'other-security-key'));
    }

    public function testDeriveFromConnectorConfig(): void
    {
        $this->assertSame(
            'sk-tdws-EAvAHSJZzYgCc2FptDzJjDHtpFKmYhxXW6cvzHwnRGcvo',
            UtilApiKeyDeriver::deriveFromConnectorConfig([
                'apiUid'         => '6',
                'apiSecurityKey' => 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox',
            ])
        );
    }

    public function testDeriveFromConnectorConfigReturnsEmptyWithoutV1Credentials(): void
    {
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig(null));
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig([]));
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig(['apiUid' => 6]));
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig(['apiSecurityKey' => 'x']));
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig(['apiUid' => 0, 'apiSecurityKey' => 'x']));
        $this->assertSame('', UtilApiKeyDeriver::deriveFromConnectorConfig(['apiUid' => 6, 'apiSecurityKey' => '']));
    }
}
