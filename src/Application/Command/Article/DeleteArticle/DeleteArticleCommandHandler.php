<?php

declare(strict_types=1);

namespace App\Application\Command\Article\DeleteArticle;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\Article\ArticleAccessDeniedException;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\User\ValueObject\UserRole;

readonly class DeleteArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    /**
     * @throws ArticleAccessDeniedException
     */
    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof DeleteArticleCommand);

        $article = $this->articleRepository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::withId($command->id);
        }

        if (
            $command->author->getRole() === UserRole::READER
            || (
                $command->author->getRole() === UserRole::AUTHOR
                && $article->getAuthor()->getId() !== $command->author->getId()
            )
        ) {
            throw new ArticleAccessDeniedException();
        }

        $this->articleRepository->remove($article);
    }
}
