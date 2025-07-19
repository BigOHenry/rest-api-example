<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\Article\GetArticle\GetArticleQuery;
use App\Application\Query\Article\GetArticle\GetArticleQueryResult;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetArticleController extends BaseController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/articles/{id}', name: 'api_article', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles/{id}',
        description: 'Retrieve details of a specific article from the system',
        summary: 'Get article by ID',
        security: [['Bearer' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Article ID to retrieve',
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
                description: 'Article retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'article' => new OA\Property(
                            property: 'article',
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
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'Article not found',
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
            $query = new GetArticleQuery(id: $id);
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetArticleQueryResult);

            if (!$result->isArticleFound()) {
                return $this->notFound();
            }

            return $this->success(response_data: (array) $result->toArray());
        } catch (ValidationErrorDomainException $e) {
            return $this->error(error: $e->getMessage(), message: $e->getErrors());
        } catch (ArticleAccessDeniedDomainException $e) {
            return $this->accessDenied(message: $e->getMessage());
        } catch (ArticleDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
