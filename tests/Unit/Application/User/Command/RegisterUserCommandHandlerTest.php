<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\RegisterUser\RegisterUserCommand;
use App\Application\Command\User\RegisterUser\RegisterUserCommandHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserAlreadyExistsDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHashingServiceInterface;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegisterUserCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordHashingServiceInterface|MockObject $passwordHashingService;
    private RegisterUserCommandHandler $handler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHashingService = $this->createMock(PasswordHashingServiceInterface::class);
        $this->handler = new RegisterUserCommandHandler(
            $this->userRepository,
            $this->passwordHashingService
        );
    }

    public function testHandleSuccessfulReaderRegistration(): void
    {
        $command = RegisterUserCommand::fromApiArray([
            'email' => 'reader@example.com',
            'password' => 'ReaderPass123!',
            'name' => 'Test Reader',
            'role' => 'ROLE_READER',
        ]);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('reader@example.com')
            ->willReturn(null)
        ;

        $this->passwordHashingService
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_reader_password')
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                return $user->getEmail() === 'reader@example.com'
                    && $user->getName() === 'Test Reader'
                    && $user->getRole() === UserRole::READER
                    && $user->getPassword() === 'hashed_reader_password';
            }))
        ;

        $this->mockUserIdAfterSave(42);

        $result = $this->handler->handle($command);

        $this->assertSame(42, $result);
    }

    public function testHandleSuccessfulAuthorRegistration(): void
    {
        $command = RegisterUserCommand::fromApiArray([
            'email' => 'author@example.com',
            'password' => 'AuthorPass123!',
            'name' => 'Test Author',
            'role' => 'ROLE_AUTHOR',
        ]);

        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHashingService->method('hashPassword')->willReturn('hashed_author_password');
        $this->userRepository->expects($this->once())->method('save');
        $this->mockUserIdAfterSave(123);

        $result = $this->handler->handle($command);

        $this->assertSame(123, $result);
    }

    public function testHandleSuccessfulFirstAdminRegistration(): void
    {
        $command = RegisterUserCommand::fromApiArray([
            'email' => 'admin@example.com',
            'password' => 'AdminPass123!',
            'name' => 'First Admin',
            'role' => 'ROLE_ADMIN',
        ]);

        $this->userRepository->method('findByEmail')->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('findAllByRole')
            ->with(UserRole::ADMIN)
            ->willReturn([])
        ;

        $this->passwordHashingService->method('hashPassword')->willReturn('hashed_admin_password');
        $this->userRepository->expects($this->once())->method('save');
        $this->mockUserIdAfterSave(1);

        $result = $this->handler->handle($command);

        $this->assertSame(1, $result);
    }

    public function testHandleFailsWhenUserAlreadyExists(): void
    {
        $existingUser = $this->createMock(User::class);
        $command = RegisterUserCommand::fromApiArray([
            'email' => 'existing@example.com',
            'password' => 'ExistingPass123!',
            'name' => 'Existing User',
            'role' => 'ROLE_READER',
        ]);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser)
        ;

        $this->userRepository->expects($this->never())->method('save');
        $this->passwordHashingService->expects($this->never())->method('hashPassword');

        $this->expectException(UserAlreadyExistsDomainException::class);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenTryingToRegisterSecondAdmin(): void
    {
        $existingAdmin = $this->createMock(User::class);
        $command = RegisterUserCommand::fromApiArray([
            'email' => 'secondadmin@example.com',
            'password' => 'SecondAdminPass123!',
            'name' => 'Second Admin',
            'role' => 'ROLE_ADMIN',
        ]);

        $this->userRepository->method('findByEmail')->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('findAllByRole')
            ->with(UserRole::ADMIN)
            ->willReturn([$existingAdmin])
        ;

        $this->userRepository->expects($this->never())->method('save');
        $this->passwordHashingService->expects($this->never())->method('hashPassword');

        $this->expectException(UserAccessDeniedDomainException::class);
        $this->expectExceptionMessage(
            'Only the first administrator can be registered, other administrators must be created by a logged-in administrator'
        );

        $this->handler->handle($command);
    }

    public function testHandlePasswordIsHashedCorrectly(): void
    {
        $plainPassword = 'MyPlainPassword123!';
        $hashedPassword = 'my_hashed_password_result';

        $command = RegisterUserCommand::fromApiArray([
            'email' => 'hash@example.com',
            'password' => $plainPassword,
            'name' => 'Hash User',
            'role' => 'ROLE_READER',
        ]);

        $this->userRepository->method('findByEmail')->willReturn(null);

        $this->passwordHashingService
            ->expects($this->once())
            ->method('hashPassword')
            ->with(
                $this->callback(function (User $user) {
                    return $user->getPassword() === 'tmp'
                        && $user->getEmail() === 'hash@example.com';
                }),
                $this->equalTo($plainPassword)
            )
            ->willReturn($hashedPassword)
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) use ($hashedPassword) {
                return $user->getPassword() === $hashedPassword;
            }))
        ;

        $this->mockUserIdAfterSave(999);

        $this->handler->handle($command);
    }

    private function mockUserIdAfterSave(int $userId): void
    {
        $this->userRepository
            ->method('save')
            ->willReturnCallback(function (User $user) use ($userId): void {
                $reflection = new \ReflectionClass($user);
                $idProperty = $reflection->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($user, $userId);
            });
    }
}
