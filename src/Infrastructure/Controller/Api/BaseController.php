<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    public function invalidJson(): JsonResponse
    {
        return new JsonResponse(
            data: ['error' => 'Invalid JSON format'],
            status: Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string|array<string, string>|null $message
     */
    public function error(string $error, string|array|null $message = null, int $code = 400): JsonResponse
    {
        $data = ['error' => $error];

        if (!empty($message)) {
            $data['message'] = $message;
        }

        return new JsonResponse(
            data: $data,
            status: $code
        );
    }

    /**
     * @param array<string, array<array<string, int|string>>|int>|array<array<string, int|string>> $response_data
     */
    public function success(?string $message = null, array $response_data = []): JsonResponse
    {
        $data = [];
        if (!empty($message)) {
            $data = ['message' => $message];
        }

        if (!empty($response_data)) {
            $data = array_merge($data, $response_data);
        }

        return new JsonResponse(
            data: $data,
            status: Response::HTTP_OK
        );
    }

    public function notFound(): JsonResponse
    {
        return new JsonResponse(
            data: ['error' => 'Entity not found'],
            status: Response::HTTP_NOT_FOUND
        );
    }

    public function accessDenied(string $message): JsonResponse
    {
        return new JsonResponse(
            data: ['error' => 'You do not have permission for this operation', 'message' => $message],
            status: Response::HTTP_FORBIDDEN
        );
    }

    protected function exception(string $message): JsonResponse
    {
        return new JsonResponse(
            data: ['error' => 'Unexpected internal error occurred', 'message' => $message],
            status: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
