<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use App\Application\Command\Article\DeleteArticle\DeleteArticleCommandHandler;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleNotFoundDomainException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\Service\ArticleAuthorizationService;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class DeleteArticleCommandHandlerTest extends TestCase
{
    private ArticleRepositoryInterface|MockObject $articleRepository;
    private ArticleAuthorizationService|MockObject $articleAuthorizationService;
    private Security|MockObject $security;
    private DeleteArticleCommandHandler $handler;
    private User $authorUser;
    private User $adminUser;
    private User $readerUser;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->articleAuthorizationService = $this->createMock(ArticleAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new DeleteArticleCommandHandler(
            $this->articleRepository,
            $this->articleAuthorizationService,
            $this->security
        );

        $this->authorUser = User::create('author@example.com', 'password', 'Test Author', UserRole::AUTHOR);
        $this->adminUser = User::create('admin@example.com', 'password', 'Admin User', UserRole::ADMIN);
        $this->readerUser = User::create('reader@example.com', 'password', 'Reader User');
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulArticleDeletionByAuthor(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->authorUser)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 123)
            ->willReturn($article)
        ;

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(user: $this->authorUser, article: $article)
            ->willReturn(true)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('remove')
            ->with(article: $article)
        ;

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulArticleDeletionByAdmin(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(456);

        $this->security->method('getUser')->willReturn($this->adminUser);
        $this->articleRepository->method('findById')->with(id: 456)->willReturn($article);
        $this->articleAuthorizationService->method('canModifyArticle')->willReturn(true);
        $this->articleRepository->expects($this->once())->method('remove')->with(article: $article);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenArticleNotFound(): void
    {
        $command = new DeleteArticleCommand(999);

        $this->security->method('getUser')->willReturn($this->authorUser);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 999)
            ->willReturn(null)
        ;

        $this->articleAuthorizationService->expects($this->never())->method('canModifyArticle');
        $this->articleRepository->expects($this->never())->method('remove');

        $this->expectException(ArticleNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksModifyArticlePermission(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security->method('getUser')->willReturn($this->readerUser);
        $this->articleRepository->method('findById')->willReturn($article);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(user: $this->readerUser, article: $article)
            ->willReturn(false)
        ;

        $this->articleRepository->expects($this->never())->method('remove');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $this->articleRepository->method('findById')->willReturn($article);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(user: null, article: $article)
            ->willReturn(false)
        ;

        $this->articleRepository->expects($this->never())->method('remove');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleCallsMethodsInCorrectOrder(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security->method('getUser')->willReturn($this->authorUser);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 123)
            ->willReturn($article)
        ;

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(user: $this->authorUser, article: $article)
            ->willReturn(true)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('remove')
            ->with(article: $article)
        ;

        $this->handler->handle($command);
    }

    public function testHandleDoesNotCallRemoveWhenArticleNotFound(): void
    {
        $command = new DeleteArticleCommand(999);

        $this->security->method('getUser')->willReturn($this->authorUser);
        $this->articleRepository->method('findById')->willReturn(null);

        // Critical check: remove MUST NOT be called when the article does not exist
        $this->articleRepository
            ->expects($this->never())
            ->method('remove')
        ;

        $this->expectException(ArticleNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotCallRemoveWhenAuthorizationFails(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security->method('getUser')->willReturn($this->readerUser);
        $this->articleRepository->method('findById')->willReturn($article);
        $this->articleAuthorizationService->method('canModifyArticle')->willReturn(false);

        // Critical check: remove MUST NOT be called on authorization failure
        $this->articleRepository
            ->expects($this->never())
            ->method('remove')
        ;

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleAuthorizationReceivesCorrectParameters(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $this->security->method('getUser')->willReturn($this->authorUser);
        $this->articleRepository->method('findById')->willReturn($article);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(
                $this->callback(function ($user) {
                    return $user === $this->authorUser;
                }),
                $this->callback(function ($articleParam) use ($article) {
                    return $articleParam === $article;
                })
            )
            ->willReturn(true)
        ;

        $this->articleRepository->expects($this->once())->method('remove');

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleWithDifferentUserRoles(): void
    {
        $article = $this->createMock(Article::class);
        $command = new DeleteArticleCommand(123);

        $users = [
            'author' => $this->authorUser,
            'admin' => $this->adminUser,
            'reader' => $this->readerUser,
        ];

        foreach ($users as $roleName => $user) {
            $this->setUp();

            $this->security->method('getUser')->willReturn($user);
            $this->articleRepository->method('findById')->willReturn($article);

            $hasPermission = \in_array($roleName, ['author', 'admin'], true);
            $this->articleAuthorizationService->method('canModifyArticle')->willReturn($hasPermission);

            if ($hasPermission) {
                $this->articleRepository->expects($this->once())->method('remove');
            } else {
                $this->articleRepository->expects($this->never())->method('remove');
                $this->expectException(ArticleAccessDeniedDomainException::class);
            }
            $this->handler->handle($command);
        }
    }
}
