<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\DeleteUser\DeleteUserCommand;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Domain\User\Exception\UserNotFoundDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeleteUserController extends BaseController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/{id}',
        description: 'Delete an existing user from the system',
        summary: 'Delete user',
        security: [['Bearer' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'User successfully deleted',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User deleted successfully'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'User deletion failed',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'User deletion failed'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Database error occurred'
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
            new OA\Response(ref: '#/components/responses/NotAuthenticatedError', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/AccessDeniedError', response: Response::HTTP_FORBIDDEN),
            new OA\Response(ref: '#/components/responses/InternalServerError', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        try {
            $command = new DeleteUserCommand(id: $id);
            $this->commandBus->handle(command: $command);

            return $this->success(message: 'User deleted successfully');
        } catch (UserNotFoundDomainException) {
            return $this->notFound();
        } catch (UserAccessDeniedDomainException $e) {
            return $this->accessDenied(message: $e->getMessage());
        } catch (UserDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
