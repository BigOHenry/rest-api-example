<?php

declare(strict_types=1);

namespace App\Application\Command\Article\DeleteArticle;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleNotFoundDomainException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\Service\ArticleAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class DeleteArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private ArticleAuthorizationService $articleAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof DeleteArticleCommand);

        $article = $this->articleRepository->findById(id: $command->id);
        if ($article === null) {
            throw ArticleNotFoundDomainException::withId(id: $command->id);
        }

        if (!$this->articleAuthorizationService->canModifyArticle(user: $this->security->getUser(), article: $article)) {
            throw ArticleAccessDeniedDomainException::forArticleManagement();
        }

        $this->articleRepository->remove(article: $article);
    }
}
