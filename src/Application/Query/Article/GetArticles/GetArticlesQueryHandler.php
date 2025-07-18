<?php

declare(strict_types=1);

namespace App\Application\Query\Article\GetArticles;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

readonly class GetArticlesQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function handle(QueryInterface $query): GetArticlesQueryResult
    {
        return new GetArticlesQueryResult(articles: $this->articleRepository->findAll());
    }
}
