<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\UpdateUser\UpdateUserCommand;
use App\Domain\User\Exception\UserValidationDomainDomainException;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class UpdateUserCommandTest extends TestCase
{
    public function testFromApiArrayWithValidReaderData(): void
    {
        $userId = 123;
        $data = [
            'email' => 'updated@example.com',
            'name' => 'Updated Reader',
            'role' => 'ROLE_READER',
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertSame(123, $command->id);
        $this->assertSame('updated@example.com', $command->email);
        $this->assertSame('Updated Reader', $command->name);
        $this->assertSame(UserRole::READER, $command->role);
    }

    public function testFromApiArrayWithValidAuthorData(): void
    {
        $userId = 456;
        $data = [
            'email' => 'updatedauthor@example.com',
            'name' => 'Updated Author',
            'role' => 'ROLE_AUTHOR',
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertSame(456, $command->id);
        $this->assertSame('updatedauthor@example.com', $command->email);
        $this->assertSame('ROLE_AUTHOR', $command->role->value);
    }

    public function testFromApiArrayWithValidAdminData(): void
    {
        $userId = 789;
        $data = [
            'email' => 'updatedadmin@example.com',
            'name' => 'Updated Admin',
            'role' => 'ROLE_ADMIN',
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertSame(789, $command->id);
        $this->assertSame(UserRole::ADMIN, $command->role);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        $userId = 999;
        $data = [
            'email' => '  trimmed@example.com  ',
            'name' => '  Trimmed User  ',
            'role' => '  ROLE_READER  ',
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertSame(999, $command->id);
        $this->assertSame('trimmed@example.com', $command->email);
        $this->assertSame('Trimmed User', $command->name);
        $this->assertSame(UserRole::READER, $command->role);
    }

    /**
     * @dataProvider provideFromApiArrayFailsWithMissingFieldsCases
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public static function provideFromApiArrayFailsWithMissingFieldsCases(): iterable
    {
        return [
            'missing email' => [
                ['name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: email',
            ],
            'missing name' => [
                ['email' => 'test@example.com', 'role' => 'ROLE_READER'],
                'Missing required fields: name',
            ],
            'missing role' => [
                ['email' => 'test@example.com', 'name' => 'User'],
                'Missing required fields: role',
            ],
            'missing multiple fields' => [
                ['email' => 'test@example.com'],
                'Missing required fields: name, role',
            ],
            'all fields missing' => [
                [],
                'Missing required fields: email, name, role',
            ],
        ];
    }

    public function testFromApiArrayFailsWithInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email-format',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayFailsWithInvalidRole(): void
    {
        $data = [
            'email' => 'test@example.com',
            'name' => 'Valid Name',
            'role' => 'INVALID_ROLE',
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayFailsWithEmptyStringFields(): void
    {
        $data = [
            'email' => '',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage('Missing required fields: email');

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayFailsWithWhitespaceOnlyFields(): void
    {
        $data = [
            'email' => '   ',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage('Missing required fields: email');

        UpdateUserCommand::fromApiArray(123, $data);
    }

    /**
     * @dataProvider provideFromApiArrayWithDifferentValidRolesCases
     */
    public function testFromApiArrayWithDifferentValidRoles(string $roleString, UserRole $expectedRole): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => $roleString,
        ];

        // Act
        $command = UpdateUserCommand::fromApiArray(123, $data);

        // Assert
        $this->assertSame($expectedRole, $command->role);
    }

    public static function provideFromApiArrayWithDifferentValidRolesCases(): iterable
    {
        return [
            'Reader role' => ['ROLE_READER', UserRole::READER],
            'Author role' => ['ROLE_AUTHOR', UserRole::AUTHOR],
            'Admin role' => ['ROLE_ADMIN', UserRole::ADMIN],
        ];
    }
}
