<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article;

use App\Domain\Article\Entity\Article;
use App\Domain\Article\Service\ArticleAuthorizationService;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleAuthorizationServiceTest extends TestCase
{
    private ArticleAuthorizationService $authorizationService;

    protected function setUp(): void
    {
        $this->authorizationService = new ArticleAuthorizationService();
    }

    /**
     * @dataProvider provideCreateArticlePermissionsByRoleCases
     */
    public function testCreateArticlePermissionsByRole(UserRole $role, bool $canCreate): void
    {
        $user = $this->createUserWithSingleRole($role->value);

        $result = $this->authorizationService->canCreateArticle($user);

        $this->assertSame($canCreate, $result);
    }

    public static function provideCreateArticlePermissionsByRoleCases(): iterable
    {
        return [
            'READER cannot create' => [UserRole::READER, false],
            'AUTHOR can create' => [UserRole::AUTHOR, true],
            'ADMIN can create' => [UserRole::ADMIN, true],
        ];
    }

    /**
     * @dataProvider provideModifyArticlePermissionsByRoleCases
     */
    public function testModifyArticlePermissionsByRole(
        UserRole $userRole,
        string $userEmail,
        string $articleAuthorEmail,
        bool $canModify,
    ): void {
        $user = $this->createUserWithSingleRole($userRole->value);
        $user->method('getUserIdentifier')->willReturn($userEmail);

        $articleAuthor = User::create($articleAuthorEmail, 'password', 'Article Author', UserRole::AUTHOR);
        $article = Article::create('Test Article', 'Test content', $articleAuthor);

        $result = $this->authorizationService->canModifyArticle($user, $article);

        $this->assertSame($canModify, $result);
    }

    public static function provideModifyArticlePermissionsByRoleCases(): iterable
    {
        return [
            'READER cannot modify own article' => [
                UserRole::READER, 'reader@example.com', 'reader@example.com', false,
            ],
            'READER cannot modify others article' => [
                UserRole::READER, 'reader@example.com', 'author@example.com', false,
            ],
            'AUTHOR can modify own article' => [
                UserRole::AUTHOR, 'author@example.com', 'author@example.com', true,
            ],
            'AUTHOR cannot modify others article' => [
                UserRole::AUTHOR, 'author1@example.com', 'author2@example.com', false,
            ],
            'ADMIN can modify own article' => [
                UserRole::ADMIN, 'admin@example.com', 'admin@example.com', true,
            ],
            'ADMIN can modify any article' => [
                UserRole::ADMIN, 'admin@example.com', 'author@example.com', true,
            ],
        ];
    }

    public function testUserWithEmptyRolesCannotCreateOrModify(): void
    {
        $userWithEmptyRoles = $this->createUserWithSingleRole('');
        $authorUser = User::create('author@example.com', 'password', 'Author User', UserRole::AUTHOR);
        $article = Article::create('Test Article', 'This is the test article content that should be at least 50 characters.', $authorUser);

        $this->assertFalse($this->authorizationService->canCreateArticle($userWithEmptyRoles));
        $this->assertFalse($this->authorizationService->canModifyArticle($userWithEmptyRoles, $article));
    }

    // ========== Helper Methods ==========

    /**
     * Creates a mock user with one role (as requested).
     */
    private function createUserWithSingleRole(string $role): UserInterface|MockObject
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn([$role]); // Pouze jedna role

        return $user;
    }
}
