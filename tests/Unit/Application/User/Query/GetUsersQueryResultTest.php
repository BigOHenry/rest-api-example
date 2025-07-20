<?php

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUsers\GetUsersQueryResult;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class GetUsersQueryResultTest extends TestCase
{
    public function testCreateResultWithUsers(): void
    {
        // Arrange
        $user1 = User::create('user1@example.com', 'password', 'User 1');
        $user2 = User::create('user2@example.com', 'password', 'User 2', UserRole::AUTHOR);
        $this->setUserId($user1, 1);
        $this->setUserId($user2, 2);

        $result = new GetUsersQueryResult(users: [$user1, $user2]);

        $array = $result->toArray();
        $this->assertCount(2, $array);
        $this->assertIsArray($array);

        $this->assertEquals([
            [
                'id' => 1,
                'email' => 'user1@example.com',
                'name' => 'User 1',
                'role' => 'ROLE_READER',
            ],
            [
                'id' => 2,
                'email' => 'user2@example.com',
                'name' => 'User 2',
                'role' => 'ROLE_AUTHOR',
            ]
        ], $array);
    }

    public function testCreateResultWithEmptyArray(): void
    {
        $result = new GetUsersQueryResult(users: []);

        $array = $result->toArray();
        $this->assertCount(0, $array);
        $this->assertEmpty($array);
        $this->assertEquals([], $array);
    }

    public function testToArrayWithSingleUser(): void
    {
        $user = User::create('single@example.com', 'password', 'Single User', UserRole::AUTHOR);
        $this->setUserId($user, 123);

        $result = new GetUsersQueryResult(users: [$user]);

        $array = $result->toArray();

        $this->assertCount(1, $array);
        $this->assertEquals([
            [
                'id' => 123,
                'email' => 'single@example.com',
                'name' => 'Single User',
                'role' => 'ROLE_AUTHOR',
            ]
        ], $array);
    }

    /**
     * @dataProvider userRoleProvider
     */
    public function testToArrayWithDifferentUserRoles(UserRole $role, string $expectedRoleString): void
    {
        $user = User::create('test@example.com', 'password', 'Test User', $role);
        $this->setUserId($user, 999);
        $result = new GetUsersQueryResult(users: [$user]);

        $array = $result->toArray();

        $this->assertEquals($expectedRoleString, $array[0]['role']);
    }

    public static function userRoleProvider(): array
    {
        return [
            'Reader role' => [UserRole::READER, 'ROLE_READER'],
            'Author role' => [UserRole::AUTHOR, 'ROLE_AUTHOR'],
            'Admin role' => [UserRole::ADMIN, 'ROLE_ADMIN'],
        ];
    }

    public function testToArrayStructureIsCorrectForAllFields(): void
    {
        $user = User::create('structure@example.com', 'password', 'Structure Test', UserRole::READER);
        $this->setUserId($user, 777);
        $result = new GetUsersQueryResult(users: [$user]);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertIsArray($array[0]);

        $this->assertArrayHasKey('id', $array[0]);
        $this->assertArrayHasKey('email', $array[0]);
        $this->assertArrayHasKey('name', $array[0]);
        $this->assertArrayHasKey('role', $array[0]);

        $this->assertCount(4, $array[0]);

        $this->assertIsInt($array[0]['id']);
        $this->assertIsString($array[0]['email']);
        $this->assertIsString($array[0]['name']);
        $this->assertIsString($array[0]['role']);
    }

    public function testToArrayDoesNotExposePasswordOrOtherFields(): void
    {
        $user = User::create('secure@example.com', 'secret_password', 'Secure User');
        $this->setUserId($user, 888);
        $result = new GetUsersQueryResult(users: [$user]);

        $array = $result->toArray();

        $this->assertArrayNotHasKey('password', $array[0]);

        $values = array_values($array[0]);
        $this->assertNotContains('secret_password', $values);
    }

    public function testUsersPropertyIsNotPubliclyAccessible(): void
    {
        $users = [User::create('private@example.com', 'password', 'Private User')];
        $result = new GetUsersQueryResult(users: $users);

        $reflection = new \ReflectionClass($result);
        $usersProperty = $reflection->getProperty('users');

        $this->assertTrue($usersProperty->isPrivate());
    }

    public function testOnlyToArrayMethodIsPublic(): void
    {
        // Arrange
        $result = new GetUsersQueryResult(users: []);

        $this->assertTrue(method_exists($result, 'toArray'));

        $reflection = new \ReflectionClass($result);
        $toArrayMethod = $reflection->getMethod('toArray');
        $this->assertTrue($toArrayMethod->isPublic());

        $constructor = $reflection->getMethod('__construct');
        $this->assertTrue($constructor->isPublic()); // konstruktor je vždy public
    }

    /**
     * Auxiliary method for setting ID via reflection
     * (because setId is protected in the User entity)
     */
    private function setUserId(User $user, int $id): void
    {
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, $id);
    }
}
