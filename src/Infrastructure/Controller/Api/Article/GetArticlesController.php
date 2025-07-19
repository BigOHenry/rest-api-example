<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\Article\GetArticles\GetArticlesQuery;
use App\Application\Query\Article\GetArticles\GetArticlesQueryResult;
use App\Domain\Article\Exception\ArticleDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetArticlesController extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $query = new GetArticlesQuery();
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetArticlesQueryResult);

            return new JsonResponse(data: [
                'articles' => $result->toArray(),
                'count' => $result->count(),
            ]);
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
