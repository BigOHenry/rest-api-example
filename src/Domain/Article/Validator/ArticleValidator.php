<?php

declare(strict_types=1);

namespace App\Domain\Article\Validator;

class ArticleValidator
{
    /**
     * @return array<string,string>
     */
    public static function validate(string $title, string $content): array
    {
        return array_merge(
            self::validateTitle(title: $title),
            self::validateContent(content: $content)
        );
    }

    /**
     * @return array<string,string>
     */
    public static function validateTitle(string $title): array
    {
        $errors = [];
        if (mb_strlen($title) < 10 || mb_strlen($title) > 255) {
            $errors['title'] = 'Title must be between 10 and 255 characters long';
        }

        return $errors;
    }

    /**
     * @return array<string,string>
     */
    public static function validateContent(string $content): array
    {
        $errors = [];
        if (mb_strlen($content) < 50) {
            $errors['content'] = 'Content must be at least 50 characters long';
        }

        return $errors;
    }
}
