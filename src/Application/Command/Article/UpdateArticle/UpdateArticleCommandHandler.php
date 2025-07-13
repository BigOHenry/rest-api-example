<?php

declare(strict_types=1);

namespace App\Application\Command\Article\UpdateArticle;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

readonly class UpdateArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof UpdateArticleCommand);

        $article = $this->articleRepository->findById($command->id);
        if (!$article) {
            throw ArticleNotFoundException::withId($command->id);
        }

        $article->setTitle($command->title);
        $article->setContent($command->content);

        $this->articleRepository->save($article);
    }
}
