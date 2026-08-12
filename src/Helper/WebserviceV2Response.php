<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Helper;

use Topdata\TopdataFoundationSW6\Exception\WebserviceResponseException;

/**
 * Unwraps the v2 webservice response envelope ({success, payload} / {success, error}).
 *
 * 08/2026 created — the webservice v2 API wraps every response in a
 * tradeguard-style envelope; the clients unwrap it so plugin call sites keep
 * working on the raw payload objects.
 */
final class WebserviceV2Response
{
    public static function unwrap(mixed $response): mixed
    {
        if (!is_object($response)) {
            return $response; // null / scalar (cannot be a v2 envelope)
        }
        if (($response->success ?? false) === true && property_exists($response, 'payload')) {
            return $response->payload;
        }

        // ---- error envelope arrived unchecked (e.g. non-200 body parsed in getMultiple)
        if (isset($response->error)) {
            throw new WebserviceResponseException(self::extractErrorMessage($response->error));
        }

        return $response; // pre-envelope responses (defensive)
    }

    public static function extractErrorMessage(mixed $error): string
    {
        if (is_array($error)) {
            return $error[0]->error_message ?? 'unknown webservice error';
        }

        return $error->error_message ?? 'unknown webservice error';
    }
}