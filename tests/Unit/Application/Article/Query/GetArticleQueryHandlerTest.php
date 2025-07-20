<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticle\GetArticleQuery;
use App\Application\Query\Article\GetArticle\GetArticleQueryHandler;
use App\Application\Query\Article\GetArticle\GetArticleQueryResult;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetArticleQueryHandlerTest extends TestCase
{
    private ArticleRepositoryInterface|MockObject $articleRepository;
    private GetArticleQueryHandler $handler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticleQueryHandler($this->articleRepository);
    }

    public function testHandleSuccessfulArticleRetrieval(): void
    {
        $author = User::create('author@example.com', 'pass', 'Test Author', UserRole::AUTHOR);
        $article = Article::create('Test Article', 'Test content', $author);
        $query = new GetArticleQuery(123);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 123)
            ->willReturn($article)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticleQueryResult::class, $result);
        $this->assertTrue($result->isArticleFound());
    }

    public function testHandleReturnsResultWithNullArticleWhenNotFound(): void
    {
        $query = new GetArticleQuery(999);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 999)
            ->willReturn(null)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticleQueryResult::class, $result);
        $this->assertFalse($result->isArticleFound());
    }

    public function testHandleCallsRepositoryWithCorrectId(): void
    {
        $query = new GetArticleQuery(456);

        $this->articleRepository
            ->expects($this->once())
            ->method('findById')
            ->with(id: 456)
            ->willReturn(null)
        ;

        $this->handler->handle($query);
    }

    /**
     * @throws Exception
     */
    public function testHandleAlwaysReturnsGetArticleQueryResult(): void
    {
        $query = new GetArticleQuery(123);

        $article = $this->createMock(Article::class);
        $this->articleRepository->method('findById')->willReturn($article);

        $resultWithArticle = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticleQueryResult::class, $resultWithArticle);

        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticleQueryHandler($this->articleRepository);
        $this->articleRepository->method('findById')->willReturn(null);

        $resultWithNull = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticleQueryResult::class, $resultWithNull);
    }

    public function testHandleDoesNotThrowExceptionWhenArticleNotFound(): void
    {
        $query = new GetArticleQuery(999);
        $this->articleRepository->method('findById')->willReturn(null);

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticleQueryResult::class, $result);
        $this->assertFalse($result->isArticleFound());
    }
}
