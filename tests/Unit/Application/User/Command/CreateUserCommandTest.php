<?php

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Domain\User\Exception\UserValidationDomainDomainException;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    public function testFromApiArrayWithValidReaderData(): void
    {
        $data = [
            'email' => 'newreader@example.com',
            'password' => 'CreatePass123!',
            'name' => 'New Reader',
            'role' => 'ROLE_READER'
        ];

        $command = CreateUserCommand::fromApiArray($data);

        $this->assertEquals('newreader@example.com', $command->email);
        $this->assertEquals('CreatePass123!', $command->password);
        $this->assertEquals('New Reader', $command->name);
        $this->assertEquals(UserRole::READER, $command->role);
    }

    public function testFromApiArrayWithValidAuthorData(): void
    {
        $data = [
            'email' => 'newauthor@example.com',
            'password' => 'AuthorCreate123!',
            'name' => 'New Author',
            'role' => 'ROLE_AUTHOR'
        ];

        $command = CreateUserCommand::fromApiArray($data);

        $this->assertEquals('newauthor@example.com', $command->email);
        $this->assertEquals('ROLE_AUTHOR', $command->role->value);
    }

    public function testFromApiArrayWithValidAdminData(): void
    {
        $data = [
            'email' => 'newadmin@example.com',
            'password' => 'AdminCreate123!',
            'name' => 'New Admin',
            'role' => 'ROLE_ADMIN'
        ];

        $command = CreateUserCommand::fromApiArray($data);

        $this->assertEquals(UserRole::ADMIN, $command->role);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        $data = [
            'email' => '  create@example.com  ',
            'password' => '  CreatePass123!  ',
            'name' => '  Create User  ',
            'role' => '  ROLE_READER  '
        ];

        $command = CreateUserCommand::fromApiArray($data);

        $this->assertEquals('create@example.com', $command->email);
        $this->assertEquals('CreatePass123!', $command->password);
        $this->assertEquals('Create User', $command->name);
        $this->assertEquals(UserRole::READER, $command->role);
    }

    /**
     * @dataProvider missingFieldsProvider
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        CreateUserCommand::fromApiArray($data);
    }

    public static function missingFieldsProvider(): array
    {
        return [
            'missing email' => [
                ['password' => 'Pass123!', 'name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: email'
            ],
            'missing password' => [
                ['email' => 'test@example.com', 'name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: password'
            ],
            'missing name' => [
                ['email' => 'test@example.com', 'password' => 'Pass123!', 'role' => 'ROLE_READER'],
                'Missing required fields: name'
            ],
            'missing role' => [
                ['email' => 'test@example.com', 'password' => 'Pass123!', 'name' => 'User'],
                'Missing required fields: role'
            ],
            'multiple missing fields' => [
                ['name' => 'User Only'],
                'Missing required fields: email, password, role'
            ],
        ];
    }

    public function testFromApiArrayFailsWithInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email-format',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER'
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        CreateUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithWeakPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'weak',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER'
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        CreateUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithInvalidRole(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'INVALID_ROLE'
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        CreateUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithEmptyStringFields(): void
    {
        $data = [
            'email' => '',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER'
        ];

        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage('Missing required fields: email');

        CreateUserCommand::fromApiArray($data);
    }
}
