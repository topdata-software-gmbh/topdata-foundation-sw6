<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Util;

/**
 * Derives the Topdata webservice V2 API key deterministically from the v1
 * credentials (uid + security_key). Clients that already hold v1 credentials
 * (e.g. the TopdataConnectorSW6 plugin config) can compute their v2 key
 * without the user entering one (zero-touch v2 switch).
 *
 * The derivation MUST stay byte-identical to the t2-app migration helper
 * (Version20260811100000 / Version20260819152026, App\Migrations\Webservice):
 * the server backfills user_api_keys with the same algorithm. It is a pure-PHP
 * big-integer divmod loop (no gmp/bcmath dependency) so the output is
 * identical on every PHP installation.
 *
 * 08/2026 created
 */
final class UtilApiKeyDeriver
{
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';

    /**
     * Derives the v2 API key ("sk-tdws-" + 45 base-54 chars) from the v1 credentials.
     */
    public static function derive(int $uid, string $securityKey): string
    {
        return 'sk-tdws-' . self::_base54Encode(hash('sha256', $uid . ':' . $securityKey), 45);
    }

    /**
     * Reads the v1 credentials from a TopdataConnectorSW6 plugin config array
     * (keys apiUid/apiSecurityKey) and derives the v2 key.
     * Returns '' when the v1 credentials are missing or empty.
     *
     * @param ?array<string, mixed> $connectorConfig
     */
    public static function deriveFromConnectorConfig(?array $connectorConfig): string
    {
        $uid         = (int)($connectorConfig['apiUid'] ?? 0);
        $securityKey = (string)($connectorConfig['apiSecurityKey'] ?? '');
        if ($uid <= 0 || $securityKey === '') {
            return '';
        }

        return self::derive($uid, $securityKey);
    }

    /**
     * Base-54 encodes a hex digest, left-padded to $length chars with the
     * first alphabet char. Identical algorithm to the migration helper
     * (see class docblock), implemented as divmod-54 over the raw bytes.
     */
    private static function _base54Encode(string $hex, int $length): string
    {
        $bytes = array_values(unpack('C*', hex2bin($hex)));
        $out   = '';
        do {
            $quotient  = [];
            $remainder = 0;
            foreach ($bytes as $byte) {
                $current       = $remainder * 256 + $byte;
                $quotientDigit = intdiv($current, 54);
                $remainder     = $current % 54;
                if ($quotientDigit !== 0 || $quotient !== []) {
                    $quotient[] = $quotientDigit;
                }
            }
            $out   = self::ALPHABET[$remainder] . $out;
            $bytes = $quotient;
        } while ($bytes !== []);

        return str_pad($out, $length, self::ALPHABET[0], STR_PAD_LEFT);
    }
}
