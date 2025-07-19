<?php

declare(strict_types=1);

namespace App\Domain\Article\Exception;

class ArticleAccessDeniedDomainException extends ArticleDomainException
{
    public static function forArticleCreation(): self
    {
        return new self(message: 'User has no permission to create articles', code: 403);
    }

    public static function forArticleManagement(): self
    {
        return new self(message: 'User has no permission to manage articles', code: 403);
    }
}
