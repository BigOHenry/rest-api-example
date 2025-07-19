<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeleteArticleController extends BaseController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/articles/{id}', name: 'api_articles_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/articles/{id}',
        description: 'Delete an existing article',
        summary: 'Delete article',
        security: [['Bearer' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Article ID to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Article successfully deleted',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article deleted successfully'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Article deletion failed',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Article deletion failed'
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
            $command = new DeleteArticleCommand(id: $id);
            $this->commandBus->handle(command: $command);

            return $this->success(message: 'Article deleted successfully');
        } catch (ArticleAccessDeniedDomainException $e) {
            return $this->accessDenied(message: $e->getMessage());
        } catch (ArticleDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
