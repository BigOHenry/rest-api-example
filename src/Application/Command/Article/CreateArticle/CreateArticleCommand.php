<?php

declare(strict_types=1);

namespace App\Application\Command\Article\CreateArticle;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;

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

        $errors = [];

        if (!\in_array($author->getRole(), [UserRole::AUTHOR, UserRole::ADMIN], strict: true)) {
            throw new ValidationErrorException(message: 'Author has not permission to create article');
        }

        if (mb_strlen($data['title']) < 10 || mb_strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be between 2 and 255 characters long';
        }

        if (mb_strlen($data['content']) < 100) {
            $errors['content'] = 'Content must be at least 100 characters long';
        }

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
