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
            'role' => 'ROLE_READER'
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertEquals(123, $command->id);
        $this->assertEquals('updated@example.com', $command->email);
        $this->assertEquals('Updated Reader', $command->name);
        $this->assertEquals(UserRole::READER, $command->role);
    }

    public function testFromApiArrayWithValidAuthorData(): void
    {
        $userId = 456;
        $data = [
            'email' => 'updatedauthor@example.com',
            'name' => 'Updated Author',
            'role' => 'ROLE_AUTHOR'
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertEquals(456, $command->id);
        $this->assertEquals('updatedauthor@example.com', $command->email);
        $this->assertEquals('ROLE_AUTHOR', $command->role->value);
    }

    public function testFromApiArrayWithValidAdminData(): void
    {
        $userId = 789;
        $data = [
            'email' => 'updatedadmin@example.com',
            'name' => 'Updated Admin',
            'role' => 'ROLE_ADMIN'
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertEquals(789, $command->id);
        $this->assertEquals(UserRole::ADMIN, $command->role);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        $userId = 999;
        $data = [
            'email' => '  trimmed@example.com  ',
            'name' => '  Trimmed User  ',
            'role' => '  ROLE_READER  '
        ];

        $command = UpdateUserCommand::fromApiArray($userId, $data);

        $this->assertEquals(999, $command->id);
        $this->assertEquals('trimmed@example.com', $command->email);
        $this->assertEquals('Trimmed User', $command->name);
        $this->assertEquals(UserRole::READER, $command->role);
    }

    /**
     * @dataProvider missingFieldsProvider
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public static function missingFieldsProvider(): array
    {
        return [
            'missing email' => [
                ['name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: email'
            ],
            'missing name' => [
                ['email' => 'test@example.com', 'role' => 'ROLE_READER'],
                'Missing required fields: name'
            ],
            'missing role' => [
                ['email' => 'test@example.com', 'name' => 'User'],
                'Missing required fields: role'
            ],
            'missing multiple fields' => [
                ['email' => 'test@example.com'],
                'Missing required fields: name, role'
            ],
            'all fields missing' => [
                [],
                'Missing required fields: email, name, role'
            ],
        ];
    }

    public function testFromApiArrayFailsWithInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email-format',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER'
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayFailsWithInvalidRole(): void
    {
        $data = [
            'email' => 'test@example.com',
            'name' => 'Valid Name',
            'role' => 'INVALID_ROLE'
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        UpdateUserCommand::fromApiArray(123, $data);
    }

    public function testFromApiArrayFailsWithEmptyStringFields(): void
    {
        $data = [
            'email' => '',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER'
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
            'role' => 'ROLE_READER'
        ];

        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage('Missing required fields: email');

        UpdateUserCommand::fromApiArray(123, $data);
    }

    /**
     * @dataProvider validRoleProvider
     */
    public function testFromApiArrayWithDifferentValidRoles(string $roleString, UserRole $expectedRole): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => $roleString
        ];

        // Act
        $command = UpdateUserCommand::fromApiArray(123, $data);

        // Assert
        $this->assertEquals($expectedRole, $command->role);
    }

    public static function validRoleProvider(): array
    {
        return [
            'Reader role' => ['ROLE_READER', UserRole::READER],
            'Author role' => ['ROLE_AUTHOR', UserRole::AUTHOR],
            'Admin role' => ['ROLE_ADMIN', UserRole::ADMIN],
        ];
    }
}
