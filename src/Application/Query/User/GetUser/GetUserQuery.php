<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUser;

use App\Application\Bus\Query\QueryInterface;

class GetUserQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
