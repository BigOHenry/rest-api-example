<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Article\Command;

use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use PHPUnit\Framework\TestCase;

class DeleteArticleCommandTest extends TestCase
{
    public function testCreateCommandWithValidId(): void
    {
        $articleId = 123;

        $command = new DeleteArticleCommand($articleId);

        $this->assertSame(123, $command->id);
    }
}
