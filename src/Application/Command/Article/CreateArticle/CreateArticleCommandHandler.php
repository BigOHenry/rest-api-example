<?php

declare(strict_types=1);

namespace App\Application\Command\Article\CreateArticle;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Exception\ArticleAlreadyExistsException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

readonly class CreateArticleCommandHandler implements CreationCommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function handle(CommandInterface $command): int
    {
        \assert($command instanceof CreateArticleCommand);

        $existingArticle = $this->articleRepository->findByTitle(title: $command->title);
        if ($existingArticle) {
            throw ArticleAlreadyExistsException::withTitle();
        }

        $article = Article::create(
            title: $command->title,
            content: $command->content,
            author: $command->user,
        );

        $this->articleRepository->save(article: $article);

        return (int) $article->getId();
    }
}
