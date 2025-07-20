<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Service\UserAuthorizationService;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAuthorizationServiceTest extends TestCase
{
    private UserAuthorizationService $authorizationService;

    protected function setUp(): void
    {
        $this->authorizationService = new UserAuthorizationService();
    }

    public function testNullUserCannotManageUsers(): void
    {
        $result = $this->authorizationService->canManageUsers(null);

        $this->assertFalse($result);
    }

    public function testNullUserCannotReadUsers(): void
    {
        $result = $this->authorizationService->canReadUsers(null);

        $this->assertFalse($result);
    }

    public function testAuthorizationServiceConsistencyBetweenMethods(): void
    {
        // Arrange
        $adminUser = $this->createUserWithSingleRole(UserRole::ADMIN->value);
        $readerUser = $this->createUserWithSingleRole(UserRole::READER->value);
        $authorUser = $this->createUserWithSingleRole(UserRole::AUTHOR->value);

        // Assert - Admin can both read and manage
        $this->assertTrue($this->authorizationService->canManageUsers($adminUser), 'ADMIN should be able to manage users');
        $this->assertTrue($this->authorizationService->canReadUsers($adminUser), 'ADMIN should be able to read users');

        // Assert - Reader can neither read nor manage
        $this->assertFalse($this->authorizationService->canManageUsers($readerUser), 'READER should NOT be able to manage users');
        $this->assertFalse($this->authorizationService->canReadUsers($readerUser), 'READER should NOT be able to read users');

        // Assert - Author can neither read nor manage
        $this->assertFalse($this->authorizationService->canManageUsers($authorUser), 'AUTHOR should NOT be able to manage users');
        $this->assertFalse($this->authorizationService->canReadUsers($authorUser), 'AUTHOR should NOT be able to read users');
    }

    // ========== Edge Cases ==========

    public function testUserWithEmptyRoleCannotDoAnything(): void
    {
        $userWithEmptyRole = $this->createUserWithSingleRole('');

        $this->assertFalse($this->authorizationService->canManageUsers($userWithEmptyRole), 'User with empty role should NOT manage users');
        $this->assertFalse($this->authorizationService->canReadUsers($userWithEmptyRole), 'User with empty role should NOT read users');
    }

    public function testUserWithInvalidRoleCannotDoAnything(): void
    {
        $userWithInvalidRole = $this->createUserWithSingleRole('INVALID_ROLE');

        $this->assertFalse($this->authorizationService->canManageUsers($userWithInvalidRole), 'User with invalid role should NOT manage users');
        $this->assertFalse($this->authorizationService->canReadUsers($userWithInvalidRole), 'User with invalid role should NOT read users');
    }

    // ========== Helper Methods ==========

    /**
     * Creates a mock user with one role (as requested).
     */
    private function createUserWithSingleRole(string $role): UserInterface|MockObject
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn([$role]);

        return $user;
    }
}
