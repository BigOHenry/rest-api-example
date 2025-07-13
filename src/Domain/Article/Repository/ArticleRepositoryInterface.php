<?php

declare(strict_types=1);

namespace App\Domain\Article\Repository;

use App\Domain\Article\Entity\Article;

interface ArticleRepositoryInterface
{
    public function save(Article $article): void;

    public function findById(int $id): ?Article;

    public function findByTitle(string $title): ?Article;

    /**
     * @return array<int, Article>
     */
    public function findAll(): array;

    public function remove(Article $article): void;
}
