<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUsers\GetUsersQuery;
use PHPUnit\Framework\TestCase;

class GetUsersQueryTest extends TestCase
{
    public function testCreateQuery(): void
    {
        $query = new GetUsersQuery();

        $this->assertInstanceOf(GetUsersQuery::class, $query);
    }
}
