<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

class CreateUserController extends AbstractController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    #[Route('/api/users', name: 'api_users_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users', summary: 'Create new user', requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'], properties: [
                    'name' => new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'John Doe'
                    ),
                    'email' => new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com'
                    )
                ], type: 'object'
            )
        ), tags: ['Users'], responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User created successfully'
                        ),
                        'user' => new OA\Property(
                            property: 'user', properties: [
                                'id' => new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                            ], type: 'object'
                        )
                    ], type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Validation failed'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['Name is required', 'Email format is invalid']
                        )
                    ], type: 'object'
                )
            )
        ]
    )]

    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = CreateUserCommand::fromApiArray(data: $data);
            $userId = $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'User created successfully',
                'user' => ['id' => $userId],
            ], status: 201);
        } catch (\JsonException $e) {
            return new JsonResponse(data: [
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], status: 400);
        } catch (ValidationErrorException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
                'message' => $e->getErrors(),
            ], status: 400);
        } catch (UserDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'User creation failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
