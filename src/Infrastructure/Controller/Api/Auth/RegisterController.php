<?php

declare(strict_types = 1);

namespace App\Infrastructure\Controller\Api\Auth;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/auth/register', name: 'auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/register', description: 'Register a new user account in the system', summary: 'User registration', requestBody: new OA\RequestBody(
            description: 'User registration data',
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'name'], properties: [
                    'email' => new OA\Property(
                        property: 'email', description: 'User email address', type: 'string', format: 'email', example: 'user@example.com'
                    ),
                    'password' => new OA\Property(
                        property: 'password',
                        description: 'User password (minimum 6 characters)',
                        type: 'string',
                        minLength: 6,
                        example: 'securePassword123'
                    ),
                    'name' => new OA\Property(
                        property: 'name', description: 'Full name of the user', type: 'string', minLength: 2, example: 'John Doe'
                    ),
                    'role' => new OA\Property(
                        property: 'role',
                        description: 'User role (optional, defaults to ROLE_USER)',
                        type: 'string',
                        enum: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_READER'],
                        example: 'ROLE_USER'
                    )
                ], type: 'object'
            )
        ), tags: ['Authentication'], responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully registered',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User registered successfully'
                        ),
                        'user' => new OA\Property(
                            property: 'user', properties: [
                                'id' => new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                )
                            ], type: 'object'
                        )
                    ], type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error or bad request',
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
                            example: ['Email is already in use', 'Password must be at least 6 characters long']
                        )
                    ], type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Unprocessable entity - invalid data format',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid data format'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Invalid email format'
                        )
                    ], type: 'object'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Internal server error'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Unable to create user account'
                        )
                    ], type: 'object'
                )
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = CreateUserCommand::fromApiArray(data: $data);
            $userId = $this->commandBus->handle($command);

            return new JsonResponse(
                [
                    'message' => 'User registered successfully',
                    'user'    => ['id' => $userId],
                ], status: 201
            );
        } catch (\JsonException $e) {
            return new JsonResponse(
                [
                    'error'   => 'Invalid JSON format',
                    'message' => $e->getMessage(),
                ], status: 400
            );
        } catch (ValidationErrorException $e) {
            return new JsonResponse(
                [
                    'error'   => $e->getMessage(),
                    'message' => $e->getErrors(),
                ], status: 400
            );
        } catch (UserDomainException $e) {
            return new JsonResponse(
                data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode()
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'error'   => 'User registration failed',
                    'message' => $e->getMessage(),
                ], status: 400
            );
        }
    }
}
