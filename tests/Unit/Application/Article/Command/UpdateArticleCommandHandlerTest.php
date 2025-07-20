<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\UpdateArticle\UpdateArticleCommand;
use App\Application\Command\Article\UpdateArticle\UpdateArticleCommandHandler;
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

class UpdateArticleCommandHandlerTest extends TestCase
{
    private ArticleRepositoryInterface|MockObject $articleRepository;
    private ArticleAuthorizationService|MockObject $articleAuthorizationService;
    private Security|MockObject $security;
    private UpdateArticleCommandHandler $handler;
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

        $this->handler = new UpdateArticleCommandHandler(
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
    public function testHandleSuccessfulArticleUpdateByAuthor(): void
    {
        // Arrange
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Updated Article Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

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

        $article->expects($this->once())->method('setTitle')->with(title: 'Updated Article Title');
        $article->expects($this->once())
                ->method('setContent')
                ->with(content: 'This is the test article content that should be at least 50 characters.')
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with(article: $article)
        ;

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulArticleUpdateByAdmin(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(456, [
            'title' => 'Admin Updated Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->adminUser);
        $this->articleRepository->method('findById')->with(id: 456)->willReturn($article);
        $this->articleAuthorizationService->method('canModifyArticle')->willReturn(true);

        $article->expects($this->once())->method('setTitle')->with(title: 'Admin Updated Title');
        $article->expects($this->once())
                ->method('setContent')
                ->with(content: 'This is the test article content that should be at least 50 characters.')
        ;
        $this->articleRepository->expects($this->once())->method('save')->with(article: $article);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenArticleNotFound(): void
    {
        $command = UpdateArticleCommand::fromApiArray(999, [
            'title' => 'Non-existent Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->authorUser);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 999)
            ->willReturn(null)
        ;

        $this->articleAuthorizationService->expects($this->never())->method('canModifyArticle');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksModifyArticlePermission(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Unauthorized Update',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->readerUser);
        $this->articleRepository->method('findById')->willReturn($article);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canModifyArticle')
            ->with(user: $this->readerUser, article: $article)
            ->willReturn(false)
        ;

        $article->expects($this->never())->method('setTitle');
        $article->expects($this->never())->method('setContent');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Anonymous Update',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

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

        $article->expects($this->never())->method('setTitle');
        $article->expects($this->never())->method('setContent');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleCallsMethodsInCorrectOrder(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Order Test Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

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

        $article->expects($this->once())->method('setTitle');
        $article->expects($this->once())->method('setContent');

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with(article: $article)
        ;

        $this->handler->handle($command);
    }

    public function testHandleDoesNotModifyArticleWhenNotFound(): void
    {
        $command = UpdateArticleCommand::fromApiArray(999, [
            'title' => 'Should not be used',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->authorUser);
        $this->articleRepository->method('findById')->willReturn(null);

        $this->articleRepository
            ->expects($this->never())
            ->method('save')
        ;

        $this->expectException(ArticleNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotModifyArticleWhenAuthorizationFails(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Should not be set',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->readerUser);
        $this->articleRepository->method('findById')->willReturn($article);
        $this->articleAuthorizationService->method('canModifyArticle')->willReturn(false);

        // Critical check: article MUST NOT be modified on authorization failure
        $article->expects($this->never())->method('setTitle');
        $article->expects($this->never())->method('setContent');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        // Act
        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleUpdatesOnlyTitleAndContent(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'New Title Only',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

        $this->security->method('getUser')->willReturn($this->authorUser);
        $this->articleRepository->method('findById')->willReturn($article);
        $this->articleAuthorizationService->method('canModifyArticle')->willReturn(true);

        $article->expects($this->once())->method('setTitle')->with(title: 'New Title Only');
        $article->expects($this->once())
                ->method('setContent')
                ->with(content: 'This is the test article content that should be at least 50 characters.')
        ;

        $this->articleRepository->expects($this->once())->method('save');

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleAuthorizationReceivesCorrectParameters(): void
    {
        $article = $this->createMock(Article::class);
        $command = UpdateArticleCommand::fromApiArray(123, [
            'title' => 'Authorization Test',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ]);

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

        $article->expects($this->once())->method('setTitle');
        $article->expects($this->once())->method('setContent');
        $this->articleRepository->expects($this->once())->method('save');

        $this->handler->handle($command);
    }
}
