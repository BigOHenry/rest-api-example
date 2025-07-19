<?php

declare(strict_types=1);

namespace App\Domain\Article\Service;

use App\Domain\Article\Entity\Article;
use App\Domain\User\ValueObject\UserRole;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleAuthorizationService
{
    public function canCreateArticle(?UserInterface $user): bool
    {
        if ($user === null) {
            return false;
        }

        return \in_array(UserRole::AUTHOR->value, $user->getRoles(), true)
            || \in_array(UserRole::ADMIN->value, $user->getRoles(), true);
    }

    public function canModifyArticle(?UserInterface $user, Article $article): bool
    {
        if ($user === null) {
            return false;
        }

        if (\in_array(UserRole::ADMIN->value, $user->getRoles(), true)) {
            return true;
        }

        if (\in_array(UserRole::AUTHOR->value, $user->getRoles(), true)) {
            return $article->getAuthor()->getEmail() === $user->getUserIdentifier();
        }

        return false;
    }
}
