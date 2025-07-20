<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Query;

use App\Application\Query\User\GetUser\GetUserQuery;
use PHPUnit\Framework\TestCase;

class GetUserQueryTest extends TestCase
{
    public function testCreateQueryWithValidId(): void
    {
        $userId = 123;

        $query = new GetUserQuery($userId);

        $this->assertSame(123, $query->id);
    }
}
