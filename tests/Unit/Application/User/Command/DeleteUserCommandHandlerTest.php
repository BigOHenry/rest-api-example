<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\DeleteUser\DeleteUserCommand;
use App\Application\Command\User\DeleteUser\DeleteUserCommandHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserNotFoundDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

class DeleteUserCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private UserAuthorizationService|MockObject $userAuthorizationService;
    private Security|MockObject $security;
    private DeleteUserCommandHandler $handler;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userAuthorizationService = $this->createMock(UserAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new DeleteUserCommandHandler(
            $this->userRepository,
            $this->userAuthorizationService,
            $this->security
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulUserDeletionByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $userToDelete = $this->createMock(User::class);
        $command = new DeleteUserCommand(123);

        // Admin has permission to delete users
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($adminUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($adminUser)
            ->willReturn(true);

        // A user with this ID exists
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn($userToDelete);

        // User is deleted
        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($userToDelete);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulDeletionOfReaderByAdmin(): void
    {
        $adminUser = $this->createMock(User::class);
        $readerToDelete = User::create('reader@example.com', 'pass', 'Reader');
        $command = new DeleteUserCommand(456);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->with(456)->willReturn($readerToDelete);
        $this->userRepository->expects($this->once())->method('remove')->with($readerToDelete);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksManageUsersPermission(): void
    {
        $readerUser = $this->createMock(User::class);
        $command = new DeleteUserCommand(123);

        // Reader does NOT have permission to delete users
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($readerUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canManageUsers')
            ->with($readerUser)
            ->willReturn(false); // READER does not have permission

        // No other methods should be called after no permissions
        $this->userRepository->expects($this->never())->method('findById');
        $this->userRepository->expects($this->never())->method('remove');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }

    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $command = new DeleteUserCommand(789);

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
        $this->userRepository->expects($this->never())->method('remove');

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
        $command = new DeleteUserCommand(999);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->userRepository->expects($this->never())->method('remove');

        $this->expectException(UserNotFoundDomainException::class);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleCallsRepositoryMethodsInCorrectOrder(): void
    {
        $adminUser = $this->createMock(User::class);
        $userToDelete = $this->createMock(User::class);
        $command = new DeleteUserCommand(123);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(true);
        $this->userRepository->method('findById')->willReturn($userToDelete);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn($userToDelete);

        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($userToDelete);

        $this->handler->handle($command);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleDoesNotCallRemoveWhenAuthorizationFails(): void
    {
        $readerUser = $this->createMock(User::class);
        $command = new DeleteUserCommand(123);

        $this->security->method('getUser')->willReturn($readerUser);
        $this->userAuthorizationService->method('canManageUsers')->willReturn(false);

        // Critical check: remove MUST NOT be called on permission check
        $this->userRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($command);
    }
}
