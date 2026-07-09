<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response (200 OK).
     *
     * @param mixed $data Response data object
     * @param string $message Success message
     * @param mixed $meta Optional pagination or extra metadata
     */
    public static function success(mixed $data = null, string $message = 'Success', mixed $meta = null): JsonResponse
    {
        return self::respond(200, true, $message, $data, $meta);
    }

    /**
     * Created response (201 Created).
     */
    public static function created(mixed $data = null, string $message = 'Created successfully', mixed $meta = null): JsonResponse
    {
        return self::respond(201, true, $message, $data, $meta);
    }

    /**
     * Success response with no data (200 OK).
     */
    public static function ok(string $message = 'Success'): JsonResponse
    {
        return self::respond(200, true, $message);
    }

    /**
     * Error response.
     */
    public static function error(string $message = 'Error', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Unauthorized (401).
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Forbidden (403).
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Not found (404).
     */
    public static function notFound(string $message = 'Not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Validation error (422).
     *
     * @param string $message Error message
     * @param mixed $errors Validation errors object/array, e.g. {"email": ["The email field is required."]}
     */
    public static function validationError(string $message = 'Validation failed', mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, 422);
    }

    /**
     * Server error (500).
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return self::error($message, 500);
    }

    /**
     * Build a response from a service result array (success, message, data, status).
     * Used by AuthServices and similar service-layer patterns.
     */
    public static function fromServiceResult(array $result): JsonResponse
    {
        $response = [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
        ];

        if ($result['success'] ?? false) {
            if (isset($result['data'])) {
                $response['data'] = $result['data'];
            }
            $response['meta'] = $result['meta'] ?? null;
        }

        if (isset($result['errors'])) {
            $response['errors'] = $result['errors'];
        }

        return response()->json($response, $result['status'] ?? 200);
    }

    /**
     * Core response builder for successful responses.
     *
     * Always includes "meta": null to match the standard format.
     */
    private static function respond(int $statusCode, bool $success, string $message, mixed $data = null, mixed $meta = null): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $response['meta'] = $meta;

        return response()->json($response, $statusCode);
    }
}
