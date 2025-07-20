<?php

declare(strict_types=1);

namespace App\Application\Query\Article\GetArticle;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

final readonly class GetArticleQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function handle(QueryInterface $query): GetArticleQueryResult
    {
        \assert($query instanceof GetArticleQuery);

        return new GetArticleQueryResult(article: $this->articleRepository->findById($query->id));
    }
}
