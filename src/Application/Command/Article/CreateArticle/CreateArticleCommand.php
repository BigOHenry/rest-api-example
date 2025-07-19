<?php

declare(strict_types=1);

namespace App\Application\Command\Article\CreateArticle;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
use App\Domain\Article\Validator\ArticleValidator;
use App\Domain\User\Entity\User;

class CreateArticleCommand implements CommandInterface
{
    protected function __construct(
        public string $title,
        public string $content,
        public User $user,
    ) {
    }

    /**
     * @param array<string, string> $data
     *
     * @throws ValidationErrorException
     */
    public static function fromApiArray(array $data, User $author): self
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
            title: $data['title'],
            content: $data['content'],
            user: $author,
        );
    }
}
