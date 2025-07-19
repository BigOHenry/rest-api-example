<?php

declare(strict_types=1);

namespace App\Application\Command\Article\UpdateArticle;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
use App\Domain\Article\Validator\ArticleValidator;

class UpdateArticleCommand implements CommandInterface
{
    protected function __construct(
        public int $id,
        public string $title,
        public string $content,
    ) {
    }

    /**
     * @param array<string, string> $data
     *
     * @throws ValidationErrorException
     */
    public static function fromApiArray(int $articleId, array $data): self
    {
        $requiredFields = ['title', 'content'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            } else {
                $data[$field] = trim($data[$field]);
            }
        }

        if (!empty($missingFields)) {
            throw new ValidationErrorException(message: 'Missing required fields: ' . implode(', ', $missingFields));
        }

        $errors = ArticleValidator::validate(title: $data['title'], content: $data['content']);

        if (!empty($errors)) {
            throw ValidationErrorException::withErrors(errors: $errors);
        }

        return new self(
            id: $articleId,
            title: $data['title'],
            content: $data['content'],
        );
    }
}
