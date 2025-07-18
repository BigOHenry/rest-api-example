<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\Article\Entity\Article;
use App\Infrastructure\Controller\Api\Article\CreateArticleController;
use App\Infrastructure\Controller\Api\Article\DeleteArticleController;
use App\Infrastructure\Controller\Api\Article\GetArticleController;
use App\Infrastructure\Controller\Api\Article\GetArticlesController;
use App\Infrastructure\Controller\Api\Article\UpdateArticleController;

#[ApiResource(
    uriTemplate: '/articles',
    shortName: 'Article',
    operations: [
        new GetCollection(
            uriTemplate: '/articles',
            controller: GetArticlesController::class,
        ),
        new Get(
            uriTemplate: '/articles/{id}',
            controller: GetArticleController::class,
        ),
        new Post(
            uriTemplate: '/articles',
            controller: CreateArticleController::class,
        ),
        new Put(
            uriTemplate: '/articles/{id}',
            controller: UpdateArticleController::class,
        ),
        new Delete(
            uriTemplate: '/articles/{id}',
            controller: DeleteArticleController::class,
        ),
    ],
    class: Article::class,
)]
class ArticleResource
{
}
