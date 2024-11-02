<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides common response methods for API controllers.
 *
 * 10/2024 created
 */
abstract class AbstractTopdataApiController extends AbstractController
{
    /**
     * Creates a JSON response with a success status and payload.
     *
     * @param mixed $payload The payload to include in the response.
     * @return JsonResponse The JSON response with the payload.
     */
    protected function payloadResponse($payload): JsonResponse
    {
        $body = [
            'success' => true,
            'payload' => $payload,
        ];

        return new JsonResponse($body, Response::HTTP_OK);
    }

    /**
     * Creates a JSON response with a success status, message, and payload.
     *
     * @param string|null $message The success message to include in the response.
     * @param mixed $payload The payload to include in the response.
     * @return JsonResponse The JSON response with the message and payload.
     */
    protected function successResponse(?string $message = null, $payload = null): JsonResponse
    {
        $body = [
            'success' => true,
            'message' => $message,
            'payload' => $payload,
        ];

        return new JsonResponse($body, Response::HTTP_OK);
    }

    /**
     * Creates a JSON response with an error status, message, and payload.
     *
     * @param string|null $errorMessage The error message to include in the response.
     * @param int $httpCode The HTTP status code for the response.
     * @param mixed $payload The payload to include in the response.
     * @return JsonResponse The JSON response with the error message and payload.
     */
    protected function errorResponse(?string $errorMessage = null, int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR, $payload = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $errorMessage,
            'payload' => $payload,
        ];

        return new JsonResponse($body, $httpCode);
    }
}