<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Application\Command\User\CreateUser\CreateUserCommandHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserAlreadyExistsDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHashingServiceInterface;
use App\Domain\User\Service\UserAuthorizationService;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CreateUserCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordHashingServiceInterface|MockObject $passwordHashingService;
    private UserAuthorizationService|MockObject $userAuthorizationService;
    private Security|MockObject $security;
    private CreateUserCommandHandler $handler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHashingService = $this->createMock(PasswordHashingServiceInterface::class);
        $this->userAuthorizationService = $this->createMock(UserAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new CreateUserCommandHandler(
            $this->userRepository,
            $this->passwordHashingService,
            $this->userAuthorizationService,
            $this->security
        );
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulUserCreationByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'created@example.com',
            'password' => 'CreatedPass123!',
            'name' => 'Created User',
            'role' => 'ROLE_READER',
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($adminUser)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($adminUser)
            ->willReturn(true)
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('created@example.com')
            ->willReturn(null)
        ;

        $this->passwordHashingService
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_created_password')
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                return $user->getEmail() === 'created@example.com'
                    && $user->getName() === 'Created User'
                    && $user->getRole() === UserRole::READER
                    && $user->getPassword() === 'hashed_created_password';
            }))
        ;

        $this->mockUserIdAfterSave(456);

        $result = $this->handler->handle($command);

        $this->assertSame(456, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulAuthorCreationByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'newauthor@example.com',
            'password' => 'AuthorPass123!',
            'name' => 'New Author',
            'role' => 'ROLE_AUTHOR',
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHashingService->method('hashPassword')->willReturn('hashed_author_password');
        $this->userRepository->expects($this->once())->method('save');
        $this->mockUserIdAfterSave(789);

        $result = $this->handler->handle($command);

        $this->assertSame(789, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulAdminCreationByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'newadmin@example.com',
            'password' => 'NewAdminPass123!',
            'name' => 'New Admin',
            'role' => 'ROLE_ADMIN',
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHashingService->method('hashPassword')->willReturn('hashed_new_admin_password');
        $this->userRepository->expects($this->once())->method('save');
        $this->mockUserIdAfterSave(1);

        $result = $this->handler->handle($command);

        $this->assertSame(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksManageUsersPermission(): void
    {
        $readerUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'unauthorized@example.com',
            'password' => 'UnauthorizedPass123!',
            'name' => 'Unauthorized User',
            'role' => 'ROLE_READER',
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($readerUser)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($readerUser)
            ->willReturn(false)
        ;

        $this->userRepository->expects($this->never())->method('findByEmail');
        $this->userRepository->expects($this->never())->method('save');
        $this->passwordHashingService->expects($this->never())->method('hashPassword');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $command = CreateUserCommand::fromApiArray([
            'email' => 'anonymous@example.com',
            'password' => 'AnonymousPass123!',
            'name' => 'Anonymous User',
            'role' => 'ROLE_READER',
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with(null)
            ->willReturn(false)
        ;

        $this->userRepository->expects($this->never())->method('findByEmail');
        $this->userRepository->expects($this->never())->method('save');
        $this->passwordHashingService->expects($this->never())->method('hashPassword');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserWithEmailAlreadyExists(): void
    {
        $adminUser = $this->createMock(User::class);
        $existingUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'existing@example.com',
            'password' => 'ExistingPass123!',
            'name' => 'Existing User',
            'role' => 'ROLE_READER',
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);

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

    /**
     * @throws Exception
     */
    public function testHandlePasswordIsHashedCorrectly(): void
    {
        $adminUser = $this->createMock(User::class);
        $plainPassword = 'MyPlainPassword123!';
        $hashedPassword = 'my_hashed_create_password';

        $command = CreateUserCommand::fromApiArray([
            'email' => 'hash@example.com',
            'password' => $plainPassword,
            'name' => 'Hash User',
            'role' => 'ROLE_READER',
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
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

    /**
     * @dataProvider provideHandleWithDifferentRolesByAuthorizedUserCases
     *
     * @throws Exception
     */
    public function testHandleWithDifferentRolesByAuthorizedUser(UserRole $role): void
    {
        $adminUser = $this->createMock(User::class);
        $command = CreateUserCommand::fromApiArray([
            'email' => 'user@example.com',
            'password' => 'Password123!',
            'name' => 'Test User',
            'role' => $role->value,
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHashingService->method('hashPassword')->willReturn('hashed');
        $this->userRepository->expects($this->once())->method('save');
        $this->mockUserIdAfterSave(1);

        $result = $this->handler->handle($command);

        $this->assertSame(1, $result);
    }

    public static function provideHandleWithDifferentRolesByAuthorizedUserCases(): iterable
    {
        return [
            'Reader role' => [UserRole::READER],
            'Author role' => [UserRole::AUTHOR],
            'Admin role' => [UserRole::ADMIN],
        ];
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
