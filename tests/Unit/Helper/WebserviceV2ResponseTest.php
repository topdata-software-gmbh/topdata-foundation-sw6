<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Topdata\TopdataFoundationSW6\Exception\WebserviceResponseException;
use Topdata\TopdataFoundationSW6\Helper\WebserviceV2Response;

/**
 * Unit tests for the v2 envelope unwrapping (no network involved).
 *
 * 08/2026 created
 */
class WebserviceV2ResponseTest extends TestCase
{
    public function testSuccessEnvelopeReturnsPayload(): void
    {
        $payload  = (object)['page' => (object)['available_pages' => 2]];
        $response = (object)['success' => true, 'payload' => $payload];

        $this->assertSame($payload, WebserviceV2Response::unwrap($response));
    }

    public function testErrorEnvelopeThrowsWithMessage(): void
    {
        $response = (object)[
            'success' => false,
            'error'   => (object)[
                'error_code'    => 113,
                'error_name'    => 'MISSING_API_KEY_OR_INVALID',
                'error_message' => 'invalid api key',
            ],
        ];

        $this->expectException(WebserviceResponseException::class);
        $this->expectExceptionMessage('invalid api key');

        WebserviceV2Response::unwrap($response);
    }

    public function testNullIsPassedThrough(): void
    {
        $this->assertNull(WebserviceV2Response::unwrap(null));
    }

    public function testPreEnvelopeResponsePassesThrough(): void
    {
        $response = (object)['page' => (object)['available_pages' => 2]];

        $this->assertSame($response, WebserviceV2Response::unwrap($response));
    }

    public function testExtractErrorMessageHandlesAllShapes(): void
    {
        $legacyArrayShape = [(object)['error_message' => 'legacy v1 message']];
        $this->assertSame('legacy v1 message', WebserviceV2Response::extractErrorMessage($legacyArrayShape));

        $v2ObjectShape = (object)['error_message' => 'v2 message'];
        $this->assertSame('v2 message', WebserviceV2Response::extractErrorMessage($v2ObjectShape));

        $this->assertSame('unknown webservice error', WebserviceV2Response::extractErrorMessage((object)[]));
    }
}