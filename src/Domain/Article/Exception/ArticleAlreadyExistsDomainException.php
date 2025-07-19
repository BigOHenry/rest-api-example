<?php

declare(strict_types=1);

namespace App\Domain\Article\Exception;

class ArticleAlreadyExistsDomainException extends ArticleDomainException
{
    public static function withTitle(): self
    {
        return new self('Article with this title already exists');
    }
}
