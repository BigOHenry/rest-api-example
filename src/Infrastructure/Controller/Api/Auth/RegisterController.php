<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Auth;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\User\RegisterUser\RegisterUserCommand;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends BaseController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/auth/register', name: 'auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/register',
        description: 'Register a new user account in the system',
        summary: 'User registration',
        requestBody: new OA\RequestBody(
            description: 'User registration data',
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'name'],
                properties: [
                    'email' => new OA\Property(
                        property: 'email',
                        description: 'User email address',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    ),
                    'password' => new OA\Property(
                        property: 'password',
                        description: 'User password (minimum 6 characters)',
                        type: 'string',
                        minLength: 6,
                        example: 'securePassword123'
                    ),
                    'name' => new OA\Property(
                        property: 'name',
                        description: 'Full name of the user',
                        type: 'string',
                        minLength: 2,
                        example: 'John Doe'
                    ),
                    'role' => new OA\Property(
                        property: 'role',
                        description: 'User role (optional, defaults to ROLE_USER)',
                        type: 'string',
                        enum: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_READER'],
                        example: 'ROLE_USER'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User created successfully'
                        ),
                        'user' => new OA\Property(
                            property: 'user',
                            properties: [
                                'id' => new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
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
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(ref: '#/components/responses/NotAuthenticatedError', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/InternalServerError', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = RegisterUserCommand::fromApiArray(data: $data);
            $userId = $this->commandBus->handle($command);

            return $this->success('User registered successfully', ['user' => ['id' => $userId]]);
        } catch (\JsonException) {
            return $this->invalidJson();
        } catch (ValidationErrorDomainException $e) {
            return $this->error(error: $e->getMessage(), message: $e->getErrors());
        } catch (UserDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
