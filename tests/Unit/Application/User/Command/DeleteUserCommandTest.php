<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\DeleteUser\DeleteUserCommand;
use PHPUnit\Framework\TestCase;

class DeleteUserCommandTest extends TestCase
{
    public function testCreateCommandWithValidId(): void
    {
        $userId = 123;

        $command = new DeleteUserCommand($userId);

        $this->assertSame(123, $command->id);
    }
}
