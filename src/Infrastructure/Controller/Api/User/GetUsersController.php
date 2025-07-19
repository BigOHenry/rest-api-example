<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUsers\GetUsersQuery;
use App\Application\Query\User\GetUsers\GetUsersQueryResult;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetUsersController extends BaseController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/users', name: 'api_users_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        description: 'Retrieve a list of users from the system with optional pagination and filtering',
        summary: 'Get list of users',
        security: [['Bearer' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Users retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Users retrieved successfully'
                        ),
                        'users' => new OA\Property(
                            property: 'users',
                            type: 'array',
                            items: new OA\Items(
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
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid query parameters',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid query parameters'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Message why something went wrong'
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
    public function __invoke(): JsonResponse
    {
        try {
            $query = new GetUsersQuery();
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetUsersQueryResult);

            return $this->success(
                response_data: [
                    'users' => $result->toArray(),
                    'count' => $result->count(),
                ]
            );
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
