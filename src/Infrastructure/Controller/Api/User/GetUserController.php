<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUser\GetUserQuery;
use App\Application\Query\User\GetUser\GetUserQueryResult;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetUserController extends BaseController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/users/{id}', name: 'api_user', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        description: 'Retrieve details of a specific user from the system',
        summary: 'Get user by ID',
        security: [['Bearer' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID to retrieve',
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
                description: 'User retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User retrieved successfully'
                        ),
                        'user' => new OA\Property(
                            property: 'user',
                            properties: [
                                'id' => new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
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
                                ),
                                'role' => new OA\Property(
                                    property: 'role',
                                    type: 'string',
                                    example: 'ROLE_USER'
                                ),
                            ],
                            type: 'object'
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
            $query = new GetUserQuery(id: $id);
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetUserQueryResult);

            if (!$result->isUserFound()) {
                return $this->notFound();
            }

            return $this->success(response_data: (array) $result->toArray());
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
