<?php

declare(strict_types=1);

namespace App\Application\Query\Article\GetArticles;

use App\Application\Bus\Query\QueryResultInterface;
use App\Domain\Article\Entity\Article;

readonly class GetArticlesQueryResult implements QueryResultInterface
{
    /**
     * @param Article[] $articles
     */
    public function __construct(
        private array $articles,
    ) {
    }

    public function count(): int
    {
        return \count($this->articles);
    }

    /**
     * @return array<array<string, int|string>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn ($article) => [
                'id' => (int) $article->getId(),
                'title' => $article->getTitle(),
                'content' => $article->getContent(),
                'author' => $article->getAuthor()->getName(),
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $article->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            $this->articles
        );
    }
}
