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

        $this->assertEquals(123, $command->id);
    }

    /**
     * @dataProvider validIdProvider
     */
    public function testCreateCommandWithVariousValidIds(int $id): void
    {
        $command = new DeleteUserCommand($id);

        $this->assertEquals($id, $command->id);
    }

    public static function validIdProvider(): array
    {
        return [
            'Small ID' => [1],
            'Medium ID' => [123],
            'Large ID' => [999999],
            'Random ID' => [42],
            'Another random ID' => [7890],
        ];
    }
}
