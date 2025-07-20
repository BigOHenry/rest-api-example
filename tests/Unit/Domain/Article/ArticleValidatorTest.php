<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article;

use App\Domain\Article\Validator\ArticleValidator;
use PHPUnit\Framework\TestCase;

class ArticleValidatorTest extends TestCase
{
    // ========== Title Validation Tests ==========

    public function testValidateTitleWithValidTitle(): void
    {
        $errors = ArticleValidator::validateTitle('This is a valid article title');

        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideValidateTitleWithVariousValidTitlesCases
     */
    public function testValidateTitleWithVariousValidTitles(string $title): void
    {
        $errors = ArticleValidator::validateTitle($title);

        $this->assertEmpty($errors);
    }

    public static function provideValidateTitleWithVariousValidTitlesCases(): iterable
    {
        return [
            'minimum length' => ['1234567890'],
            'normal title' => ['This is a normal article title'],
            'maximum length' => [str_repeat('A', 255)],
            'title with numbers' => ['Article 123 about something interesting'],
            'title with special chars' => ['Article: The Ultimate Guide (2023)'],
        ];
    }

    public function testValidateTitleWithTooShortTitle(): void
    {
        $shortTitle = '123456789';

        $errors = ArticleValidator::validateTitle($shortTitle);

        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Title must be between 10 and 255 characters long', $errors['title']);
    }

    public function testValidateTitleWithTooLongTitle(): void
    {
        $longTitle = str_repeat('A', 256);

        $errors = ArticleValidator::validateTitle($longTitle);

        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Title must be between 10 and 255 characters long', $errors['title']);
    }

    /**
     * @dataProvider provideValidateTitleWithInvalidTitlesCases
     */
    public function testValidateTitleWithInvalidTitles(string $title): void
    {
        $errors = ArticleValidator::validateTitle($title);

        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Title must be between 10 and 255 characters long', $errors['title']);
    }

    public static function provideValidateTitleWithInvalidTitlesCases(): iterable
    {
        return [
            'empty string' => [''],
            'too short' => ['Short'],
            'exactly 9 chars' => ['123456789'],
            'exactly 256 chars' => [str_repeat('X', 256)],
            'way too long' => [str_repeat('Y', 500)],
        ];
    }

    // ========== Content Validation Tests ==========

    public function testValidateContentWithValidContent(): void
    {
        $validContent = str_repeat('This is valid content. ', 10);

        $errors = ArticleValidator::validateContent($validContent);

        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideValidateContentWithVariousValidContentCases
     */
    public function testValidateContentWithVariousValidContent(string $content): void
    {
        $errors = ArticleValidator::validateContent($content);

        $this->assertEmpty($errors);
    }

    public static function provideValidateContentWithVariousValidContentCases(): iterable
    {
        return [
            'minimum length' => [str_repeat('A', 50)],
            'normal content' => ['This is a normal article content that is long enough to pass validation rules.'],
            'long content' => [str_repeat('This is very long content. ', 50)],
            'content with newlines' => ["This is content\nwith multiple lines\nthat should be valid."],
            'content with special chars' => ['Content with special characters: !@#$%^&*()_+-={}|[]\\:";\'<>?,./ and more text to make it long.'],
        ];
    }

    public function testValidateContentWithTooShortContent(): void
    {
        $shortContent = str_repeat('A', 49);

        $errors = ArticleValidator::validateContent($shortContent);

        $this->assertArrayHasKey('content', $errors);
        $this->assertSame('Content must be at least 50 characters long', $errors['content']);
    }

    /**
     * @dataProvider provideValidateContentWithInvalidContentCases
     */
    public function testValidateContentWithInvalidContent(string $content): void
    {
        $errors = ArticleValidator::validateContent($content);

        $this->assertArrayHasKey('content', $errors);
        $this->assertSame('Content must be at least 50 characters long', $errors['content']);
    }

    public static function provideValidateContentWithInvalidContentCases(): iterable
    {
        return [
            'empty string' => [''],
            'very short' => ['Short'],
            'exactly 49 chars' => [str_repeat('X', 49)],
            'whitespace only' => [str_repeat(' ', 60)],
        ];
    }

    // ========== Combined Validation Tests ==========

    public function testValidateWithAllValidData(): void
    {
        $errors = ArticleValidator::validate(
            'This is a valid article title',
            'This is valid article content that is definitely long enough to pass validation rules and requirements.'
        );

        $this->assertEmpty($errors);
    }

    public function testValidateWithBothFieldsInvalid(): void
    {
        $errors = ArticleValidator::validate(
            'Short',
            'Short content'
        );

        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('content', $errors);
        $this->assertSame('Title must be between 10 and 255 characters long', $errors['title']);
        $this->assertSame('Content must be at least 50 characters long', $errors['content']);
    }

    public function testValidateWithOnlyTitleInvalid(): void
    {
        $errors = ArticleValidator::validate(
            'Short', // invalid title
            'This is valid article content that is definitely long enough to pass validation rules and requirements.'
        );

        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayNotHasKey('content', $errors);
    }

    public function testValidateWithOnlyContentInvalid(): void
    {
        $errors = ArticleValidator::validate(
            'This is a valid article title',
            'Short content' // invalid content
        );

        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('content', $errors);
        $this->assertArrayNotHasKey('title', $errors);
    }

    // ========== Edge Case Tests ==========

    public function testValidateTitleWithExactBoundaryValues(): void
    {
        // Test exact minimum (10 characters)
        $minTitle = '1234567890';
        $minErrors = ArticleValidator::validateTitle($minTitle);
        $this->assertEmpty($minErrors);

        // Test one below minimum (9 characters)
        $belowMinTitle = '123456789';
        $belowMinErrors = ArticleValidator::validateTitle($belowMinTitle);
        $this->assertArrayHasKey('title', $belowMinErrors);

        // Test exact maximum (255 characters)
        $maxTitle = str_repeat('A', 255);
        $maxErrors = ArticleValidator::validateTitle($maxTitle);
        $this->assertEmpty($maxErrors);

        // Test one above maximum (256 characters)
        $aboveMaxTitle = str_repeat('A', 256);
        $aboveMaxErrors = ArticleValidator::validateTitle($aboveMaxTitle);
        $this->assertArrayHasKey('title', $aboveMaxErrors);
    }

    public function testValidateContentWithExactBoundaryValues(): void
    {
        // Test exact minimum (50 characters)
        $minContent = str_repeat('A', 50);
        $minErrors = ArticleValidator::validateContent($minContent);
        $this->assertEmpty($minErrors);

        // Test one below minimum (49 characters)
        $belowMinContent = str_repeat('A', 49);
        $belowMinErrors = ArticleValidator::validateContent($belowMinContent);
        $this->assertArrayHasKey('content', $belowMinErrors);
    }
}
