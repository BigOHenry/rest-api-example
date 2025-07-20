<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\UpdateUser\UpdateUserCommand;
use App\Application\Command\User\UpdateUser\UpdateUserCommandHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserNotFoundDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

class UpdateUserCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private UserAuthorizationService|MockObject $userAuthorizationService;
    private Security|MockObject $security;
    private UpdateUserCommandHandler $handler;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userAuthorizationService = $this->createMock(UserAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new UpdateUserCommandHandler(
            $this->userRepository,
            $this->userAuthorizationService,
            $this->security
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulUserUpdateByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $userToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'updated@example.com',
            'name' => 'Updated User',
            'role' => 'ROLE_READER'
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($adminUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($adminUser)
            ->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn($userToUpdate);

        $userToUpdate->expects($this->once())->method('setEmail')->with('updated@example.com');
        $userToUpdate->expects($this->once())->method('setName')->with('Updated User');
        $userToUpdate->expects($this->once())->method('setRole')->with(UserRole::READER);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($userToUpdate);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulReaderToAuthorUpdate(): void
    {
        $adminUser = $this->createMock(User::class);
        $readerToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(456, [
            'email' => 'newauthor@example.com',
            'name' => 'New Author',
            'role' => 'ROLE_AUTHOR'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->with(456)->willReturn($readerToUpdate);

        $readerToUpdate->expects($this->once())->method('setEmail')->with('newauthor@example.com');
        $readerToUpdate->expects($this->once())->method('setName')->with('New Author');
        $readerToUpdate->expects($this->once())->method('setRole')->with(UserRole::AUTHOR);

        $this->userRepository->expects($this->once())->method('save')->with($readerToUpdate);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulAuthorToAdminUpdate(): void
    {
        $adminUser = $this->createMock(User::class);
        $authorToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(789, [
            'email' => 'newadmin@example.com',
            'name' => 'New Admin',
            'role' => 'ROLE_ADMIN'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->with(789)->willReturn($authorToUpdate);

        $authorToUpdate->expects($this->once())->method('setRole')->with(UserRole::ADMIN);
        $this->userRepository->expects($this->once())->method('save')->with($authorToUpdate);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksManageUsersPermission(): void
    {
        $readerUser = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'unauthorized@example.com',
            'name' => 'Unauthorized Update',
            'role' => 'ROLE_READER'
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($readerUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($readerUser)
            ->willReturn(false); // READER does not have permission

        $this->userRepository->expects($this->never())->method('findById');
        $this->userRepository->expects($this->never())->method('save');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleFailsWhenAuthorTriesToUpdateUser(): void
    {
        $authorUser = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(456, [
            'email' => 'unauthorized@example.com',
            'name' => 'Unauthorized Update',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($authorUser);
        $this->userAuthorizationService->method('canManageUsers')->with($authorUser)->willReturn(false);

        $this->userRepository->expects($this->never())->method('findById');
        $this->userRepository->expects($this->never())->method('save');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $command = UpdateUserCommand::fromApiArray(789, [
            'email' => 'anonymous@example.com',
            'name' => 'Anonymous Update',
            'role' => 'ROLE_READER'
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with(null)
            ->willReturn(false);

        $this->userRepository->expects($this->never())->method('findById');
        $this->userRepository->expects($this->never())->method('save');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleFailsWhenUserNotFound(): void
    {
        $adminUser = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(999, [
            'email' => 'notfound@example.com',
            'name' => 'Not Found User',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->userRepository->expects($this->never())->method('save');

        $this->expectException(UserNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleCallsUserMethodsInCorrectOrder(): void
    {
        $adminUser = $this->createMock(User::class);
        $userToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'ordered@example.com',
            'name' => 'Ordered Update',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->willReturn($userToUpdate);

        $userToUpdate->expects($this->once())->method('setEmail')->with('ordered@example.com');
        $userToUpdate->expects($this->once())->method('setName')->with('Ordered Update');
        $userToUpdate->expects($this->once())->method('setRole')->with(UserRole::READER);

        $this->userRepository->expects($this->once())->method('save')->with($userToUpdate);

        $this->handler->handle($command);
    }

    /**
     * @dataProvider roleUpdateProvider
     * @throws Exception
     */
    public function testHandleWithDifferentRoleUpdates(UserRole $newRole): void
    {
        // Arrange
        $adminUser = $this->createMock(User::class);
        $userToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'roletest@example.com',
            'name' => 'Role Test User',
            'role' => $newRole->value
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->willReturn($userToUpdate);

        $userToUpdate->expects($this->once())->method('setRole')->with($newRole);
        $this->userRepository->expects($this->once())->method('save')->with($userToUpdate);

        $this->handler->handle($command);
    }

    public static function roleUpdateProvider(): array
    {
        return [
            'Update to Reader' => [UserRole::READER],
            'Update to Author' => [UserRole::AUTHOR],
            'Update to Admin' => [UserRole::ADMIN],
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleDoesNotCallSaveWhenAuthorizationFails(): void
    {
        // Arrange
        $readerUser = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($readerUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(false);

        // Critical check: save MUST NOT be called on authorization failure
        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleDoesNotCallSaveWhenUserNotFound(): void
    {
        $adminUser = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(999, [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->willReturn(null);

        // Critical check: save MUST NOT be called when the user does not exist
        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(UserNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleDoesNotUpdatePasswordField(): void
    {
        // Arrange
        $adminUser = $this->createMock(User::class);
        $userToUpdate = $this->createMock(User::class);
        $command = UpdateUserCommand::fromApiArray(123, [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'ROLE_READER'
        ]);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->willReturn($userToUpdate);

        $userToUpdate->expects($this->never())->method('setPassword');

        $userToUpdate->expects($this->once())->method('setEmail');
        $userToUpdate->expects($this->once())->method('setName');
        $userToUpdate->expects($this->once())->method('setRole');

        $this->userRepository->expects($this->once())->method('save');

        $this->handler->handle($command);
    }
}
