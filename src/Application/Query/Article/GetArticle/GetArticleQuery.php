<?php

declare(strict_types=1);

namespace App\Application\Query\Article\GetArticle;

use App\Application\Bus\Query\QueryInterface;

class GetArticleQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
