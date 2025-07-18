<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\Article\GetArticles\GetArticlesQuery;
use App\Application\Query\Article\GetArticles\GetArticlesQueryResult;
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
        $query = new GetArticlesQuery();
        $result = $this->queryBus->handle(query: $query);
        \assert($result instanceof GetArticlesQueryResult);

        return new JsonResponse(data: [
            'articles' => $result->toArray(),
            'count' => $result->count(),
        ]);
    }
}
