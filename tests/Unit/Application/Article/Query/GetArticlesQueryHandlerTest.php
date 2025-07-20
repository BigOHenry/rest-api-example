<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticles\GetArticlesQuery;
use App\Application\Query\Article\GetArticles\GetArticlesQueryHandler;
use App\Application\Query\Article\GetArticles\GetArticlesQueryResult;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetArticlesQueryHandlerTest extends TestCase
{
    private ArticleRepositoryInterface|MockObject $articleRepository;
    private GetArticlesQueryHandler $handler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticlesQueryHandler($this->articleRepository);
    }

    public function testHandleSuccessfulArticlesRetrieval(): void
    {
        $author1 = User::create('author1@example.com', 'pass', 'Author One', UserRole::AUTHOR);
        $author2 = User::create('author2@example.com', 'pass', 'Author Two', UserRole::AUTHOR);

        $articles = [
            Article::create('First Article', 'This is the test article content that should be at least 50 characters v1.', $author1),
            Article::create('Second Article', 'This is the test article content that should be at least 50 characters v2.', $author2),
            Article::create('Third Article', 'This is the test article content that should be at least 50 characters v3.', $author1),
        ];

        $query = new GetArticlesQuery();

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($articles)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }

    public function testHandleSuccessfulEmptyArticlesRetrieval(): void
    {
        $query = new GetArticlesQuery();

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([])
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }

    public function testHandleSuccessfulSingleArticleRetrieval(): void
    {
        $author = User::create('single@example.com', 'pass', 'Single Author', UserRole::AUTHOR);
        $singleArticle = [Article::create('Only Article', 'This is the test article content that should be at least 50 characters.', $author)];
        $query = new GetArticlesQuery();

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($singleArticle)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleCallsRepositoryFindAll(): void
    {
        $articles = [$this->createMock(Article::class)];
        $query = new GetArticlesQuery();

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($articles)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleAlwaysReturnsGetArticlesQueryResult(): void
    {
        $query = new GetArticlesQuery();

        $articles = [$this->createMock(Article::class)];
        $this->articleRepository->method('findAll')->willReturn($articles);

        $resultWithArticles = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $resultWithArticles);

        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticlesQueryHandler($this->articleRepository);
        $this->articleRepository->method('findAll')->willReturn([]);

        $resultWithEmpty = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $resultWithEmpty);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotRequireAuthorization(): void
    {
        $articles = [$this->createMock(Article::class)];
        $query = new GetArticlesQuery();

        $this->articleRepository->method('findAll')->willReturn($articles);

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleCreatesResultWithNamedParameter(): void
    {
        $articles = [$this->createMock(Article::class)];
        $query = new GetArticlesQuery();

        $this->articleRepository->method('findAll')->willReturn($articles);

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetArticlesQueryResult::class, $result);
    }
}
