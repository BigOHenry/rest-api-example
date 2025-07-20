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

    public function testQueryHasNoParameters(): void
    {
        $query = new GetUsersQuery();

        $reflection = new \ReflectionClass($query);
        $properties = $reflection->getProperties();

        $this->assertEmpty($properties, 'GetUsersQuery should not have any properties');
    }

    public function testMultipleInstancesAreEqual(): void
    {
        $query1 = new GetUsersQuery();
        $query2 = new GetUsersQuery();

        $this->assertEquals($query1, $query2);
    }
}
