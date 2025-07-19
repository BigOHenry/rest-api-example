<?php

declare(strict_types=1);

namespace App\Application\Command\Article\UpdateArticle;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleNotFoundDomainException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\Service\ArticleAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UpdateArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private ArticleAuthorizationService $articleAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof UpdateArticleCommand);

        $article = $this->articleRepository->findById(id: $command->id);
        if (!$article) {
            throw ArticleNotFoundDomainException::withId(id: $command->id);
        }

        if ($this->articleAuthorizationService->canModifyArticle(user: $this->security->getUser(), article: $article)) {
            throw ArticleAccessDeniedDomainException::forArticleManagement();
        }

        $article->setTitle(title: $command->title);
        $article->setContent(content: $command->content);

        $this->articleRepository->save(article: $article);
    }
}
