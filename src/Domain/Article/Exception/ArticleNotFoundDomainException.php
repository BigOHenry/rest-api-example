<?php

declare(strict_types=1);

namespace App\Domain\Article\Exception;

class ArticleNotFoundDomainException extends ArticleDomainException
{
    public static function withId(int $id): self
    {
        return new self("Article with id '{$id}' not found", code: 404);
    }
}
