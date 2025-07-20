<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUser\GetUserQueryResult;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class GetUserQueryResultTest extends TestCase
{
    public function testCreateResultWithUser(): void
    {
        $user = User::create('test@example.com', 'password', 'Test User');

        $result = new GetUserQueryResult($user);

        $this->assertTrue($result->isUserFound());
    }

    public function testCreateResultWithNullUser(): void
    {
        $result = new GetUserQueryResult(null);

        $this->assertFalse($result->isUserFound());
    }

    public function testToArrayWithExistingReaderUser(): void
    {
        $user = User::create('reader@example.com', 'password', 'Reader User');

        $this->setUserId($user, 123);

        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('user', $array);
        $this->assertSame([
            'user' => [
                'id' => 123,
                'email' => 'reader@example.com',
                'name' => 'Reader User',
                'role' => 'ROLE_READER',
            ],
        ], $array);
    }

    public function testToArrayWithExistingAuthorUser(): void
    {
        $user = User::create('author@example.com', 'password', 'Author User', UserRole::AUTHOR);
        $this->setUserId($user, 456);
        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertSame([
            'user' => [
                'id' => 456,
                'email' => 'author@example.com',
                'name' => 'Author User',
                'role' => 'ROLE_AUTHOR',
            ],
        ], $array);
    }

    public function testToArrayWithExistingAdminUser(): void
    {
        $user = User::create('admin@example.com', 'password', 'Admin User', UserRole::ADMIN);
        $this->setUserId($user, 1);
        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertSame([
            'user' => [
                'id' => 1,
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'role' => 'ROLE_ADMIN',
            ],
        ], $array);
    }

    public function testToArrayWithNullUser(): void
    {
        $result = new GetUserQueryResult(null);

        $array = $result->toArray();

        $this->assertNull($array);
    }

    /**
     * @dataProvider provideToArrayWithDifferentUserRolesCases
     */
    public function testToArrayWithDifferentUserRoles(UserRole $role, string $expectedRoleString): void
    {
        $user = User::create('test@example.com', 'password', 'Test User', $role);
        $this->setUserId($user, 999);
        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertSame($expectedRoleString, $array['user']['role']);
    }

    public static function provideToArrayWithDifferentUserRolesCases(): iterable
    {
        return [
            'Reader role' => [UserRole::READER, 'ROLE_READER'],
            'Author role' => [UserRole::AUTHOR, 'ROLE_AUTHOR'],
            'Admin role' => [UserRole::ADMIN, 'ROLE_ADMIN'],
        ];
    }

    public function testIsUserFoundReturnsTrueForValidUser(): void
    {
        $user = User::create('test@example.com', 'password', 'Test User');
        $result = new GetUserQueryResult($user);

        $this->assertTrue($result->isUserFound());
    }

    public function testIsUserFoundReturnsFalseForNullUser(): void
    {
        $result = new GetUserQueryResult(null);

        $this->assertFalse($result->isUserFound());
    }

    public function testToArrayStructureIsCorrect(): void
    {
        $user = User::create('structure@example.com', 'password', 'Structure Test');
        $this->setUserId($user, 777);
        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('user', $array);
        $this->assertIsArray($array['user']);
        $this->assertArrayHasKey('id', $array['user']);
        $this->assertArrayHasKey('email', $array['user']);
        $this->assertArrayHasKey('name', $array['user']);
        $this->assertArrayHasKey('role', $array['user']);

        $this->assertIsInt($array['user']['id']);
        $this->assertIsString($array['user']['email']);
        $this->assertIsString($array['user']['name']);
        $this->assertIsString($array['user']['role']);
    }

    public function testToArrayDoesNotExposePassword(): void
    {
        $user = User::create('secure@example.com', 'secret_password', 'Secure User');
        $this->setUserId($user, 888);
        $result = new GetUserQueryResult($user);

        $array = $result->toArray();

        $this->assertArrayNotHasKey('password', $array['user']);
        $this->assertArrayNotHasKey('pass', $array['user']);

        $flatValues = array_values($array['user']);
        $this->assertNotContains('secret_password', $flatValues);
    }

    public function testResultCannotDirectlyAccessUserProperty(): void
    {
        $user = User::create('private@example.com', 'password', 'Private User', UserRole::AUTHOR);
        $result = new GetUserQueryResult($user);

        $reflection = new \ReflectionClass($result);
        $userProperty = $reflection->getProperty('user');

        $this->assertTrue($userProperty->isPrivate());
    }

    public function testOnlyPublicMethodsAreAccessible(): void
    {
        $user = User::create('public@example.com', 'password', 'Public User');
        $result = new GetUserQueryResult($user);

        $this->assertTrue(method_exists($result, 'isUserFound'));
        $this->assertTrue(method_exists($result, 'toArray'));

        $reflection = new \ReflectionClass($result);
        $isUserFoundMethod = $reflection->getMethod('isUserFound');
        $toArrayMethod = $reflection->getMethod('toArray');

        $this->assertTrue($isUserFoundMethod->isPublic());
        $this->assertTrue($toArrayMethod->isPublic());
    }

    /**
     * Auxiliary method for setting ID via reflection
     * * (because setId is protected).
     */
    private function setUserId(User $user, int $id): void
    {
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, $id);
    }
}
