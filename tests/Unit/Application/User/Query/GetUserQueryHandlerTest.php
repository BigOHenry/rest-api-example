<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUser\GetUserQuery;
use App\Application\Query\User\GetUser\GetUserQueryHandler;
use App\Application\Query\User\GetUser\GetUserQueryResult;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

class GetUserQueryHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private UserAuthorizationService|MockObject $userAuthorizationService;
    private Security|MockObject $security;
    private GetUserQueryHandler $handler;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userAuthorizationService = $this->createMock(UserAuthorizationService::class);
        $this->security = $this->createMock(Security::class);

        $this->handler = new GetUserQueryHandler(
            $this->userRepository,
            $this->userAuthorizationService,
            $this->security
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleSuccessfulUserRetrieval(): void
    {
       $adminUser = $this->createMock(User::class);
        $requestedUser = User::create('user@example.com', 'pass', 'Test User');
        $query = new GetUserQuery(123);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canReadUsers')->willReturn(true);
        $this->userRepository->method('findById')->with(123)->willReturn($requestedUser);

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetUserQueryResult::class, $result);
        $this->assertTrue($result->isUserFound());

        $array = $result->toArray();
        $this->assertNotNull($array);
        $this->assertArrayHasKey('user', $array);
        $this->assertEquals('user@example.com', $array['user']['email']);
        $this->assertEquals('Test User', $array['user']['name']);
        $this->assertEquals('ROLE_READER', $array['user']['role']);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleFailsWhenUserLacksPermission(): void
    {
        $readerUser = $this->createMock(User::class);
        $query = new GetUserQuery(123);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($readerUser);

        $this->userAuthorizationService
            ->expects($this->once())
            ->method('canReadUsers')
            ->with($readerUser)
            ->willReturn(false);

        $this->userRepository->expects($this->never())->method('findById');

        $this->expectException(UserAccessDeniedDomainException::class);

        $this->handler->handle($query);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleReturnsResultWithNullUserWhenNotFound(): void
    {
        $adminUser = $this->createMock(User::class);
        $query = new GetUserQuery(999);

        $this->security->method('getUser')->willReturn($adminUser);
        $this->userAuthorizationService->method('canReadUsers')->willReturn(true);
        $this->userRepository->method('findById')->with(999)->willReturn(null);

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(GetUserQueryResult::class, $result);
        $this->assertFalse($result->isUserFound());
        $this->assertNull($result->toArray());
    }
}
