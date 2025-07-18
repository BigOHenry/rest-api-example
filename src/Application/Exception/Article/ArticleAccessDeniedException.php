<?php

declare(strict_types=1);

namespace App\Application\Exception\Article;

use App\Application\Exception\HandleProcessErrorException;

class ArticleAccessDeniedException extends HandleProcessErrorException
{
    public function __construct()
    {
        parent::__construct('Access denied: User is not authorized to article', 401);
    }
}
