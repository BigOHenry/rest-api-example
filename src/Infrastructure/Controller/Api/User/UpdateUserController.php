<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\UpdateUser\UpdateUserCommand;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UpdateUserController extends BaseController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/users/{id}', name: 'api_users_put', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}',
        description: 'Update an existing user',
        summary: 'Update user',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'role'],
                properties: [
                    'name' => new OA\Property(
                        property: 'name',
                        description: 'Updated user name',
                        type: 'string',
                        minLength: 2,
                        example: 'Jane Doe'
                    ),
                    'email' => new OA\Property(
                        property: 'email',
                        description: 'Updated user email address',
                        type: 'string',
                        format: 'email',
                        example: 'jane@example.com'
                    ),
                    'role' => new OA\Property(
                        property: 'role',
                        description: 'Updated user role',
                        type: 'string',
                        enum: ['ROLE_AUTHOR', 'ROLE_ADMIN', 'ROLE_READER'],
                        example: 'ROLE_AUTHOR'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User updated successfully'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Entity not found'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Validation error or update failed',
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
                            example: ['name' => 'Name must be at least 2 characters long', 'email' => 'Invalid email format']
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(ref: '#/components/responses/NotAuthenticatedError', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/AccessDeniedError', response: Response::HTTP_FORBIDDEN),
            new OA\Response(ref: '#/components/responses/InternalServerError', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = UpdateUserCommand::fromApiArray(userId: $id, data: $data);
            $this->commandBus->handle(command: $command);

            return $this->success(message: 'User updated successfully');
        } catch (\JsonException) {
            return $this->invalidJson();
        } catch (ValidationErrorDomainException $e) {
            return $this->error(error: $e->getMessage(), message: $e->getErrors());
        } catch (UserAccessDeniedDomainException $e) {
            return $this->accessDenied(message: $e->getMessage());
        } catch (UserDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
