<?php

declare(strict_types=1);

namespace App\Application\Command\Article\DeleteArticle;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

readonly class DeleteArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof DeleteArticleCommand);

        $article = $this->articleRepository->findById($command->id);
        if ($article) {
            throw ArticleNotFoundException::withId($command->id);
        }

        $this->articleRepository->remove($article);
    }
}
