<?php

declare(strict_types=1);

namespace App\Application\Command\Article\CreateArticle;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Exception\ArticleAlreadyExistsException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

readonly class CreateArticleCommandHandler implements CreationCommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(CommandInterface $command): int
    {
        \assert($command instanceof CreateArticleCommand);

        $existingArticle = $this->articleRepository->findByTitle($command->title);
        if ($existingArticle) {
            throw ArticleAlreadyExistsException::withTitle();
        }

        $user = $this->userRepository->findByEmail($command->authorEmail);
        if ($user === null) {
            throw UserNotFoundException::withEmail($command->authorEmail);
        }

        $article = Article::create(
            $command->title,
            $command->content,
            $user,
        );

        $this->articleRepository->save($article);

        return (int) $article->getId();
    }
}
