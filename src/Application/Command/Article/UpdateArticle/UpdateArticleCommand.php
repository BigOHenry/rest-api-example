<?php

declare(strict_types=1);

namespace App\Application\Command\Article\UpdateArticle;

use App\Application\Bus\Command\CommandInterface;

class UpdateArticleCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content
    ) {
    }
}
