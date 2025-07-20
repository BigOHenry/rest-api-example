<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\CreateArticle\CreateArticleCommand;
use App\Domain\Article\Exception\ArticleValidationDomainDomainException;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class CreateArticleCommandTest extends TestCase
{
    private User $author;

    protected function setUp(): void
    {
        $this->author = User::create('author@example.com', 'password', 'Test Author', UserRole::AUTHOR);
    }

    public function testFromApiArrayWithValidData(): void
    {
        $data = [
            'title' => 'Test Article Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ];

        $command = CreateArticleCommand::fromApiArray($data, $this->author);

        $this->assertSame('Test Article Title', $command->title);
        $this->assertSame('This is the test article content that should be at least 50 characters.', $command->content);
        $this->assertSame($this->author, $command->user);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        $data = [
            'title' => '  Article with spaces  ',
            'content' => '  This is the test article content that should be at least 50 characters.  ',
        ];

        $command = CreateArticleCommand::fromApiArray($data, $this->author);

        $this->assertSame('Article with spaces', $command->title);
        $this->assertSame('This is the test article content that should be at least 50 characters.', $command->content);
    }

    /**
     * @dataProvider provideFromApiArrayFailsWithMissingFieldsCases
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(ArticleValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        CreateArticleCommand::fromApiArray($data, $this->author);
    }

    public static function provideFromApiArrayFailsWithMissingFieldsCases(): iterable
    {
        return [
            'missing title' => [
                ['content' => 'Test content'],
                'Missing required fields: title',
            ],
            'missing content' => [
                ['title' => 'Test title'],
                'Missing required fields: content',
            ],
            'missing both fields' => [
                [],
                'Missing required fields: title, content',
            ],
            'empty title' => [
                ['title' => '', 'content' => 'Test content'],
                'Missing required fields: title',
            ],
            'empty content' => [
                ['title' => 'Test title', 'content' => ''],
                'Missing required fields: content',
            ],
            'whitespace only title' => [
                ['title' => '   ', 'content' => 'Test content'],
                'Missing required fields: title',
            ],
            'whitespace only content' => [
                ['title' => 'Test title', 'content' => '   '],
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

        CreateArticleCommand::fromApiArray($data, $this->author);
    }

    public function testFromApiArrayWithDifferentAuthors(): void
    {
        $admin = User::create('admin@example.com', 'password', 'Admin User', UserRole::ADMIN);
        $reader = User::create('reader@example.com', 'password', 'Reader User');

        $data = [
            'title' => 'Article Title',
            'content' => 'This is the test article content that should be at least 50 characters.',
        ];

        $commandByAdmin = CreateArticleCommand::fromApiArray($data, $admin);
        $commandByReader = CreateArticleCommand::fromApiArray($data, $reader);

        $this->assertSame($admin, $commandByAdmin->user);
        $this->assertSame($reader, $commandByReader->user);
        $this->assertSame($data['title'], $commandByAdmin->title);
        $this->assertSame($data['title'], $commandByReader->title);
    }

    public function testFromApiArrayWithLongValidContent(): void
    {
        $longContent = str_repeat('This is a long article content.', 100);
        $data = [
            'title' => 'Article with Long Content',
            'content' => $longContent,
        ];

        $command = CreateArticleCommand::fromApiArray($data, $this->author);

        $this->assertSame('Article with Long Content', $command->title);
        $this->assertSame($longContent, $command->content);
    }
}
