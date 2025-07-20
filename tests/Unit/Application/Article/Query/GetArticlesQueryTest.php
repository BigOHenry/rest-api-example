<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticles\GetArticlesQuery;
use PHPUnit\Framework\TestCase;

class GetArticlesQueryTest extends TestCase
{
    public function testCreateQuery(): void
    {
        $query = new GetArticlesQuery();

        $this->assertInstanceOf(GetArticlesQuery::class, $query);
    }
}
