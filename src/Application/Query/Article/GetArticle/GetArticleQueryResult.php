<?php

declare(strict_types=1);

namespace App\Application\Query\Article\GetArticle;

use App\Application\Bus\Query\QueryResultInterface;
use App\Domain\Article\Entity\Article;

readonly class GetArticleQueryResult implements QueryResultInterface
{
    public function __construct(
        private ?Article $article,
    ) {
    }

    public function isArticleFound(): bool
    {
        return $this->article instanceof Article;
    }

    /**
     * @return array<array<string, string|int>>|null
     */
    public function toArray(): ?array
    {
        if ($this->article === null) {
            return null;
        }

        return [
            'article' => [
                'id' => (int) $this->article->getId(),
                'title' => $this->article->getTitle(),
                'content' => $this->article->getContent(),
                'author' => $this->article->getAuthor()->getName(),
                'createdAt' => $this->article->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $this->article->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
