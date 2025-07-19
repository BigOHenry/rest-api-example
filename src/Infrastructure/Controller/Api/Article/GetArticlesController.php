<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\Article\GetArticles\GetArticlesQuery;
use App\Application\Query\Article\GetArticles\GetArticlesQueryResult;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetArticlesController extends BaseController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/articles', name: 'api_articles_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles',
        description: 'Retrieve a list of articles',
        summary: 'Get list of articles',
        security: [['Bearer' => []]],
        tags: ['Articles'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Articles retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'articles' => new OA\Property(
                            property: 'articles',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    'id' => new OA\Property(
                                        property: 'id',
                                        type: 'integer',
                                        example: 1
                                    ),
                                    'title' => new OA\Property(
                                        property: 'title',
                                        type: 'string',
                                        example: 'What is Lorem Ipsum?'
                                    ),
                                    'content' => new OA\Property(
                                        property: 'content',
                                        type: 'string',
                                        example: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
                                    ),
                                    'author' => new OA\Property(
                                        property: 'role',
                                        type: 'string',
                                        example: 'John Doe'
                                    ),
                                    'createdAt' => new OA\Property(
                                        property: 'createdAt',
                                        type: 'datetime',
                                        example: '2025-07-18 16:45:20'
                                    ),
                                    'updatedAt' => new OA\Property(
                                        property: 'updatedAt',
                                        type: 'datetime',
                                        example: '2025-07-19 14:05:25'
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
            $query = new GetArticlesQuery();
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetArticlesQueryResult);

            return $this->success(response_data: [
                'articles' => $result->toArray(),
                'count' => $result->count(),
            ]);
        } catch (ArticleDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
