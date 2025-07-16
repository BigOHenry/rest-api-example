<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\User\Entity\User;
use App\Infrastructure\Controller\Api\User\CreateUserController;
use App\Infrastructure\Controller\Api\User\DeleteUserController;
use App\Infrastructure\Controller\Api\User\GetUserController;
use App\Infrastructure\Controller\Api\User\GetUsersController;
use App\Infrastructure\Controller\Api\User\UpdateUserController;

#[ApiResource(
    uriTemplate: '/users',
    shortName: 'User',
    operations: [
        new GetCollection(
            controller: GetUsersController::class,
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Get(
            uriTemplate: '/users/{id}',
            controller: GetUserController::class,
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            controller: CreateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Put(
            uriTemplate: '/users/{id}',
            controller: UpdateUserController::class,
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Delete(
            uriTemplate: '/users/{id}',
            controller: DeleteUserController::class,
            security: "is_granted('ROLE_ADMIN')"
        ),
    ],
    class: User::class,
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class UserResource
{
}
