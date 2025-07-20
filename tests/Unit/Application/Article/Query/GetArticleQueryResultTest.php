<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticle\GetArticleQueryResult;
use App\Domain\Article\Entity\Article;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class GetArticleQueryResultTest extends TestCase
{
    private User $author;

    protected function setUp(): void
    {
        $this->author = User::create('author@example.com', 'password', 'Test Author', UserRole::AUTHOR);
    }

    public function testCreateResultWithArticle(): void
    {
        $article = Article::create('Test Article', 'Test content', $this->author);

        $result = new GetArticleQueryResult(article: $article);

        $this->assertTrue($result->isArticleFound());
    }

    public function testCreateResultWithNullArticle(): void
    {
        $result = new GetArticleQueryResult(article: null);

        $this->assertFalse($result->isArticleFound());
    }

    public function testToArrayWithExistingArticle(): void
    {
        $article = Article::create('Sample Article', 'This is the test article content that should be at least 50 characters.', $this->author);

        $this->setArticleId($article, 123);
        $this->setArticleTimestamps($article, '2023-01-01 10:00:00', '2023-01-02 15:30:00');

        $result = new GetArticleQueryResult(article: $article);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('article', $array);

        $expectedStructure = [
            'article' => [
                'id' => 123,
                'title' => 'Sample Article',
                'content' => 'This is the test article content that should be at least 50 characters.',
                'author' => 'Test Author',
                'createdAt' => '2023-01-01 10:00:00',
                'updatedAt' => '2023-01-02 15:30:00',
            ],
        ];

        $this->assertSame($expectedStructure, $array);
    }

    public function testToArrayWithNullArticle(): void
    {
        $result = new GetArticleQueryResult(article: null);

        $array = $result->toArray();

        $this->assertNull($array);
    }

    public function testToArrayStructureIsCorrect(): void
    {
        $article = Article::create('Structure Test', 'This is the test article content that should be at least 50 characters.', $this->author);
        $this->setArticleId($article, 777);
        $this->setArticleTimestamps($article, '2023-01-01 12:00:00', '2023-01-01 13:00:00');

        $result = new GetArticleQueryResult(article: $article);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('article', $array);
        $this->assertIsArray($array['article']);

        $this->assertArrayHasKey('id', $array['article']);
        $this->assertArrayHasKey('title', $array['article']);
        $this->assertArrayHasKey('content', $array['article']);
        $this->assertArrayHasKey('author', $array['article']);
        $this->assertArrayHasKey('createdAt', $array['article']);
        $this->assertArrayHasKey('updatedAt', $array['article']);

        $this->assertCount(6, $array['article']);

        $this->assertIsInt($array['article']['id']);
        $this->assertIsString($array['article']['title']);
        $this->assertIsString($array['article']['content']);
        $this->assertIsString($array['article']['author']);
        $this->assertIsString($array['article']['createdAt']);
        $this->assertIsString($array['article']['updatedAt']);
    }

    public function testIsArticleFoundReturnsTrueForValidArticle(): void
    {
        $article = Article::create('Valid Article', 'Valid content', $this->author);
        $result = new GetArticleQueryResult(article: $article);

        $this->assertTrue($result->isArticleFound());
    }

    public function testIsArticleFoundReturnsFalseForNullArticle(): void
    {
        $result = new GetArticleQueryResult(article: null);

        $this->assertFalse($result->isArticleFound());
    }

    public function testToArrayAuthorFieldShowsAuthorName(): void
    {
        $specificAuthor = User::create('specific@example.com', 'pass', 'Specific Author Name', UserRole::AUTHOR);
        $article = Article::create('Author Test', 'This is the test article content that should be at least 50 characters.', $specificAuthor);
        $this->setArticleId($article, 888);
        $this->setArticleTimestamps($article, '2023-01-01 10:00:00', '2023-01-01 11:00:00');

        $result = new GetArticleQueryResult(article: $article);

        $array = $result->toArray();

        $this->assertSame('Specific Author Name', $array['article']['author']);
    }

    /**
     * Auxiliary method for setting ID via reflection
     * (because setId is protected in the Article entity).
     */
    private function setArticleId(Article $article, int $id): void
    {
        $reflection = new \ReflectionClass($article);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($article, $id);
    }

    /**
     * Helper method for setting timestamps via reflection
     * (because createdAt and updatedAt are private/protected).
     */
    private function setArticleTimestamps(Article $article, string $createdAt, string $updatedAt): void
    {
        $reflection = new \ReflectionClass($article);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($article, new \DateTime($createdAt));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($article, new \DateTime($updatedAt));
    }
}
