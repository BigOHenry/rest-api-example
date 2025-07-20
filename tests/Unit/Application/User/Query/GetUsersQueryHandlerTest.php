<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUsers\GetUsersQuery;
use App\Application\Query\User\GetUsers\GetUsersQueryHandler;
use App\Application\Query\User\GetUsers\GetUsersQueryResult;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GetUsersQueryHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private UserAuthorizationService|MockObject $userAuthorizationService;
    private Security|MockObject $security;
    private GetUsersQueryHandler $handler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userAuthorizationService = $this->createMock(UserAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new GetUsersQueryHandler(
            $this->userRepository,
            $this->userAuthorizationService,
            $this->security
        );
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulUsersRetrievalByAuthorizedUser(): void
    {
        // Arrange
        $authorizedUser = $this->createMock(User::class);
        $users = [
            User::create('reader@example.com', 'pass', 'Reader User'),
            User::create('author@example.com', 'pass', 'Author User', UserRole::AUTHOR),
            User::create('admin@example.com', 'pass', 'Admin User', UserRole::ADMIN),
        ];
        $query = new GetUsersQuery();

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($authorizedUser)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canReadUsers')
            ->with(user: $authorizedUser)
            ->willReturn(true)
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($users)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetUsersQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleSuccessfulEmptyUsersRetrieval(): void
    {
        $authorizedUser = $this->createMock(User::class);
        $query = new GetUsersQuery();

        $this->security->method('getUser')->willReturn($authorizedUser);
        $this->userAuthorizationService->method('canReadUsers')->willReturn(true);
        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([])
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetUsersQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksReadUsersPermission(): void
    {
        $unauthorizedUser = $this->createMock(User::class);
        $query = new GetUsersQuery();

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($unauthorizedUser)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canReadUsers')
            ->with(user: $unauthorizedUser)
            ->willReturn(false)
        ;

        // Repositories must not be called when unauthorized
        $this->userRepository->expects($this->never())->method('findAll');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($query);
    }

    public function testHandleFailsWhenUserIsNotLoggedIn(): void
    {
        $query = new GetUsersQuery();

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canReadUsers')
            ->with(user: null)
            ->willReturn(false)
        ;

        $this->userRepository->expects($this->never())->method('findAll');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($query);
    }

    /**
     * @throws Exception
     */
    public function testHandleCallsRepositoryAfterAuthorization(): void
    {
        $authorizedUser = $this->createMock(User::class);
        $users = [$this->createMock(User::class)];
        $query = new GetUsersQuery();

        $this->security->method('getUser')->willReturn($authorizedUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canReadUsers')
            ->with(user: $authorizedUser)
            ->willReturn(true)
        ;

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($users)
        ;

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetUsersQueryResult::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotCallRepositoryWhenAuthorizationFails(): void
    {
        // Arrange
        $unauthorizedUser = $this->createMock(User::class);
        $query = new GetUsersQuery();

        $this->security->method('getUser')->willReturn($unauthorizedUser);
        $this->userAuthorizationService->method('canReadUsers')->willReturn(false);

        // Critical check: findAll MUST NOT be called on authorization failure
        $this->userRepository
            ->expects($this->never())
            ->method('findAll')
        ;

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($query);
    }
}
