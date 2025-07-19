<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\Article\GetArticle\GetArticleQuery;
use App\Application\Query\Article\GetArticle\GetArticleQueryResult;
use App\Domain\Article\Exception\ArticleDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GetArticleController extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/articles/{id}', name: 'api_article', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        try {
            $query = new GetArticleQuery(id: $id);
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetArticleQueryResult);

            if (!$result->isArticleFound()) {
                return new JsonResponse(data: ['error' => 'Article not found'], status: 404);
            }

            return new JsonResponse(data: $result->toArray());
        } catch (ArticleDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Unexpected error',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
