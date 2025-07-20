<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\UpdateArticle\UpdateArticleCommand;
use App\Domain\Article\Exception\ArticleValidationDomainDomainException;
use PHPUnit\Framework\TestCase;

class UpdateArticleCommandTest extends TestCase
{
    public function testFromApiArrayWithValidData(): void
    {
        // Arrange
        $articleId = 123;
        $data = [
            'title' => 'Updated Article Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ];

        // Act
        $command = UpdateArticleCommand::fromApiArray($articleId, $data);

        // Assert
        $this->assertSame(123, $command->id);
        $this->assertSame('Updated Article Title', $command->title);
        $this->assertSame('This is the test article content that should be at least 50 characters.', $command->content);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        // Arrange
        $articleId = 456;
        $data = [
            'title' => '  Updated Article with spaces  ',
            'content' => '  This is the test article content that should be at least 50 characters.  ',
        ];

        // Act
        $command = UpdateArticleCommand::fromApiArray($articleId, $data);

        // Assert
        $this->assertSame(456, $command->id);
        $this->assertSame('Updated Article with spaces', $command->title);
        $this->assertSame('This is the test article content that should be at least 50 characters.', $command->content);
    }

    /**
     * @dataProvider provideFromApiArrayFailsWithMissingFieldsCases
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(ArticleValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        UpdateArticleCommand::fromApiArray(123, $data);
    }

    public static function provideFromApiArrayFailsWithMissingFieldsCases(): iterable
    {
        return [
            'missing title' => [
                ['content' => 'Updated content'],
                'Missing required fields: title',
            ],
            'missing content' => [
                ['title' => 'Updated title'],
                'Missing required fields: content',
            ],
            'missing both fields' => [
                [],
                'Missing required fields: title, content',
            ],
            'empty title' => [
                ['title' => '', 'content' => 'Updated content'],
                'Missing required fields: title',
            ],
            'empty content' => [
                ['title' => 'Updated title', 'content' => ''],
                'Missing required fields: content',
            ],
            'whitespace only title' => [
                ['title' => '   ', 'content' => 'Updated content'],
                'Missing required fields: title',
            ],
            'whitespace only content' => [
                ['title' => 'Updated title', 'content' => '   '],
                'Missing required fields: content',
            ],
        ];
    }

    public function testFromApiArrayFailsWithArticleValidatorErrors(): void
    {
        $data = [
            'title' => 'A', // Too short title
            'content' => 'Short', // Too short content
        ];

        $this->expectException(ArticleValidationDomainDomainException::class);

        UpdateArticleCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayWithLongValidContent(): void
    {
        $longContent = str_repeat('This is a long updated article content.', 100);
        $data = [
            'title' => 'Article with Updated Long Content',
            'content' => $longContent,
        ];

        $command = UpdateArticleCommand::fromApiArray(789, $data);

        $this->assertSame(789, $command->id);
        $this->assertSame('Article with Updated Long Content', $command->title);
        $this->assertSame($longContent, $command->content);
    }

    public function testFromApiArrayDoesNotIncludeAuthorField(): void
    {
        $data = [
            'title' => 'Updated Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
            'author' => 'should be ignored',
            'extraField' => 'should be ignored',
        ];

        $command = UpdateArticleCommand::fromApiArray(123, $data);

        $this->assertSame('Updated Title', $command->title);
        $this->assertSame('This is the test article content that should be at least 50 characters.', $command->content);

        $reflection = new \ReflectionClass($command);
        $properties = $reflection->getProperties();
        $propertyNames = array_map(static fn ($prop) => $prop->getName(), $properties);

        $this->assertContains('id', $propertyNames);
        $this->assertContains('title', $propertyNames);
        $this->assertContains('content', $propertyNames);
        $this->assertNotContains('author', $propertyNames);
    }
}
