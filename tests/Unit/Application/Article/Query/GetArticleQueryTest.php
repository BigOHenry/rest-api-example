<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Query;

use App\Application\Query\Article\GetArticle\GetArticleQuery;
use PHPUnit\Framework\TestCase;

class GetArticleQueryTest extends TestCase
{
    public function testCreateQueryWithValidId(): void
    {
        $articleId = 123;

        $query = new GetArticleQuery($articleId);

        $this->assertSame(123, $query->id);
    }
}
