<?php

declare(strict_types=1);

namespace App\Application\Command\Article\DeleteArticle;

use App\Application\Bus\Command\CommandInterface;

final readonly class DeleteArticleCommand implements CommandInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
