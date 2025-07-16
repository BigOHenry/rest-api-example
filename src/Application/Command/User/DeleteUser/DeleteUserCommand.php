<?php

declare(strict_types=1);

namespace App\Application\Command\User\DeleteUser;

use App\Application\Bus\Command\CommandInterface;

final readonly class DeleteUserCommand implements CommandInterface
{
    public function __construct(public int $id)
    {
    }
}
