<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticles\GetArticlesQueryResult;
use App\Domain\Article\Entity\Article;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class GetArticlesQueryResultTest extends TestCase
{
    private User $author1;
    private User $author2;

    protected function setUp(): void
    {
        $this->author1 = User::create('author1@example.com', 'password', 'First Author', UserRole::AUTHOR);
        $this->author2 = User::create('author2@example.com', 'password', 'Second Author', UserRole::AUTHOR);
    }

    public function testCreateResultWithArticles(): void
    {
        $articles = [
            Article::create('Article 1', 'This is the test article content that should be at least 100 characters v1.', $this->author1),
            Article::create('Article 2', 'This is the test article content that should be at least 100 characters v2.', $this->author2),
        ];

        $result = new GetArticlesQueryResult(articles: $articles);

        $array = $result->toArray();
        $this->assertCount(2, $array);
        $this->assertIsArray($array);
    }

    public function testCreateResultWithEmptyArray(): void
    {
        $result = new GetArticlesQueryResult(articles: []);

        $array = $result->toArray();
        $this->assertCount(0, $array);
        $this->assertEmpty($array);
        $this->assertSame([], $array);
    }

    public function testToArrayWithMultipleArticles(): void
    {
        $article1 = Article::create('First Article', 'This is the test article content that should be at least 100 characters v1.', $this->author1);
        $article2 = Article::create('Second Article', 'This is the test article content that should be at least 100 characters v2.', $this->author2);
        $article3 = Article::create('Third Article', 'This is the test article content that should be at least 100 characters v3.', $this->author1);

        $this->setArticleId($article1, 1);
        $this->setArticleId($article2, 2);
        $this->setArticleId($article3, 3);

        $this->setArticleTimestamps($article1, '2023-01-01 10:00:00', '2023-01-01 11:00:00');
        $this->setArticleTimestamps($article2, '2023-01-02 12:00:00', '2023-01-02 13:00:00');
        $this->setArticleTimestamps($article3, '2023-01-03 14:00:00', '2023-01-03 15:00:00');

        $articles = [$article1, $article2, $article3];
        $result = new GetArticlesQueryResult(articles: $articles);

        $array = $result->toArray();

        $this->assertCount(3, $array);

        $expectedStructure = [
            [
                'id' => 1,
                'title' => 'First Article',
                'content' => 'This is the test article content that should be at least 100 characters v1.',
                'author' => 'First Author',
                'createdAt' => '2023-01-01 10:00:00',
                'updatedAt' => '2023-01-01 11:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Second Article',
                'content' => 'This is the test article content that should be at least 100 characters v2.',
                'author' => 'Second Author',
                'createdAt' => '2023-01-02 12:00:00',
                'updatedAt' => '2023-01-02 13:00:00',
            ],
            [
                'id' => 3,
                'title' => 'Third Article',
                'content' => 'This is the test article content that should be at least 100 characters v3.',
                'author' => 'First Author',
                'createdAt' => '2023-01-03 14:00:00',
                'updatedAt' => '2023-01-03 15:00:00',
            ],
        ];

        $this->assertSame($expectedStructure, $array);
    }

    public function testToArrayWithSingleArticle(): void
    {
        $article = Article::create('Single Article', 'This is the test article content that should be at least 100 characters.', $this->author1);
        $this->setArticleId($article, 123);
        $this->setArticleTimestamps($article, '2023-01-01 10:00:00', '2023-01-01 11:00:00');

        $result = new GetArticlesQueryResult(articles: [$article]);

        $array = $result->toArray();

        $this->assertCount(1, $array);
        $this->assertSame([
            [
                'id' => 123,
                'title' => 'Single Article',
                'content' => 'This is the test article content that should be at least 100 characters.',
                'author' => 'First Author',
                'createdAt' => '2023-01-01 10:00:00',
                'updatedAt' => '2023-01-01 11:00:00',
            ],
        ], $array);
    }

    public function testToArrayStructureIsCorrectForAllFields(): void
    {
        $article = Article::create('Structure Test', 'This is the test article content that should be at least 100 characters.', $this->author1);
        $this->setArticleId($article, 777);
        $this->setArticleTimestamps($article, '2023-01-01 12:00:00', '2023-01-01 13:00:00');

        $result = new GetArticlesQueryResult(articles: [$article]);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertIsArray($array[0]);

        $this->assertArrayHasKey('id', $array[0]);
        $this->assertArrayHasKey('title', $array[0]);
        $this->assertArrayHasKey('content', $array[0]);
        $this->assertArrayHasKey('author', $array[0]);
        $this->assertArrayHasKey('createdAt', $array[0]);
        $this->assertArrayHasKey('updatedAt', $array[0]);

        $this->assertCount(6, $array[0]);

        $this->assertIsInt($array[0]['id']);
        $this->assertIsString($array[0]['title']);
        $this->assertIsString($array[0]['content']);
        $this->assertIsString($array[0]['author']);
        $this->assertIsString($array[0]['createdAt']);
        $this->assertIsString($array[0]['updatedAt']);
    }

    public function testToArrayAuthorFieldShowsAuthorName(): void
    {
        $specificAuthor = User::create('specific@example.com', 'pass', 'Specific Author Name', UserRole::AUTHOR);
        $article = Article::create('Author Test', 'This is the test article content that should be at least 100 characters.', $specificAuthor);
        $this->setArticleId($article, 888);
        $this->setArticleTimestamps($article, '2023-01-01 10:00:00', '2023-01-01 11:00:00');

        $result = new GetArticlesQueryResult(articles: [$article]);

        $array = $result->toArray();

        $this->assertSame('Specific Author Name', $array[0]['author']);
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
