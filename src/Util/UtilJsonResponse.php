<?php

namespace Topdata\TopdataFoundationSW6\Util;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * 04/2025 created
 */
class UtilJsonResponse
{
    public static function error(string $message, int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR, mixed $payload = null): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'payload' => $payload
        ], $httpCode);
    }

    public static function success(?string $message = null, mixed $payload = null): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'payload' => $payload
        ]);
    }
}