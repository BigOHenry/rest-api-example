<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\CreateArticle\CreateArticleCommand;
use App\Application\Command\Article\CreateArticle\CreateArticleCommandHandler;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleAlreadyExistsDomainException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\Service\ArticleAuthorizationService;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateArticleCommandHandlerTest extends TestCase
{
    private ArticleRepositoryInterface|MockObject $articleRepository;
    private ArticleAuthorizationService|MockObject $articleAuthorizationService;
    private CreateArticleCommandHandler $handler;
    private User $authorUser;
    private User $readerUser;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->articleAuthorizationService = $this->createMock(ArticleAuthorizationService::class);

        $this->handler = new CreateArticleCommandHandler(
            $this->articleRepository,
            $this->articleAuthorizationService
        );

        $this->authorUser = User::create('author@example.com', 'password', 'Test Author', UserRole::AUTHOR);
        $this->readerUser = User::create('reader@example.com', 'password', 'Test Reader');
    }

    public function testHandleSuccessfulArticleCreationByAuthor(): void
    {
        // Arrange
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'New Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->authorUser);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canCreateArticle')
            ->with(user: $this->authorUser)
            ->willReturn(true)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('findByTitle')
            ->with(title: 'New Article')
            ->willReturn(null)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Article $article) {
                return $article->getTitle() === 'New Article'
                    && $article->getContent() === 'This is the test article content that should be at least 50 characters.'
                    && $article->getAuthor() === $this->authorUser;
            }))
        ;

        $this->mockArticleIdAfterSave(123);

        $result = $this->handler->handle($command);

        $this->assertSame(123, $result);
    }

    public function testHandleSuccessfulArticleCreationByAdmin(): void
    {
        $adminUser = User::create('admin@example.com', 'password', 'Admin User', UserRole::ADMIN);
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Admin Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $adminUser);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(true);
        $this->articleRepository->method('findByTitle')->willReturn(null);
        $this->articleRepository->expects($this->once())->method('save');
        $this->mockArticleIdAfterSave(456);

        $result = $this->handler->handle($command);

        $this->assertSame(456, $result);
    }

    public function testHandleFailsWhenUserLacksCreateArticlePermission(): void
    {
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Unauthorized Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->readerUser);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canCreateArticle')
            ->with(user: $this->readerUser)
            ->willReturn(false)
        ;

        $this->articleRepository->expects($this->never())->method('findByTitle');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenArticleWithTitleAlreadyExists(): void
    {
        $existingArticle = $this->createMock(Article::class);
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Existing Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->authorUser);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(true);

        $this->articleRepository
            ->expects($this->once())
            ->method('findByTitle')
            ->with(title: 'Existing Title')
            ->willReturn($existingArticle)
        ;

        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAlreadyExistsDomainException::class);

        $this->handler->handle($command);
    }

    public function testHandleCallsMethodsInCorrectOrder(): void
    {
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Order Test',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->authorUser);

        $this->articleAuthorizationService
            ->expects($this->once())
            ->method('canCreateArticle')
            ->willReturn(true)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('findByTitle')
            ->willReturn(null)
        ;

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
        ;

        $this->mockArticleIdAfterSave(789);

        $result = $this->handler->handle($command);

        $this->assertSame(789, $result);
    }

    public function testHandleDoesNotCallRepositoryMethodsWhenAuthorizationFails(): void
    {
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Test Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->readerUser);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(false);

        // Critical check: no repository methods must be called on authorization failure
        $this->articleRepository->expects($this->never())->method('findByTitle');
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotSaveWhenTitleAlreadyExists(): void
    {
        $existingArticle = $this->createMock(Article::class);
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Duplicate Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->authorUser);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(true);
        $this->articleRepository->method('findByTitle')->willReturn($existingArticle);

        // Critical check: save MUST NOT be called when title exists
        $this->articleRepository->expects($this->never())->method('save');

        $this->expectException(ArticleAlreadyExistsDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @dataProvider provideHandleWithDifferentAuthorizedUsersCases
     */
    public function testHandleWithDifferentAuthorizedUsers(User $user): void
    {
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Role Test Article',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $user);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(true);
        $this->articleRepository->method('findByTitle')->willReturn(null);
        $this->articleRepository->expects($this->once())->method('save');
        $this->mockArticleIdAfterSave(999);

        $result = $this->handler->handle($command);

        $this->assertSame(999, $result);
    }

    public static function provideHandleWithDifferentAuthorizedUsersCases(): iterable
    {
        return [
            'Author user' => [User::create('author@test.com', 'pass', 'Author', UserRole::AUTHOR)],
            'Admin user' => [User::create('admin@test.com', 'pass', 'Admin', UserRole::ADMIN)],
        ];
    }

    public function testHandleArticleCreationParametersAreCorrect(): void
    {
        $command = CreateArticleCommand::fromApiArray([
            'title' => 'Parameter Test',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ], $this->authorUser);

        $this->articleAuthorizationService->method('canCreateArticle')->willReturn(true);
        $this->articleRepository->method('findByTitle')->willReturn(null);

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Article $article) {
                return $article->getTitle() === 'Parameter Test'
                    && $article->getContent() === 'This is the test article content that should be at least 50 characters.'
                    && $article->getAuthor() === $this->authorUser;
            }))
        ;

        $this->mockArticleIdAfterSave(555);

        $this->handler->handle($command);
    }

    /**
     * Helper method for mocking the article ID after saving.
     */
    private function mockArticleIdAfterSave(int $articleId): void
    {
        $this->articleRepository
            ->method('save')
            ->willReturnCallback(function (Article $article) use ($articleId): void {
                $reflection = new \ReflectionClass($article);
                $idProperty = $reflection->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($article, $articleId);
            })
        ;
    }
}
