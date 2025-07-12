<?php declare(strict_types = 1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Domain\User\Entity\User;
use App\Infrastructure\Controller\Api\Auth\LoginController;
use App\Infrastructure\Controller\Api\Auth\RegisterController;
use App\Infrastructure\Controller\Api\User\CreateUserController;
use App\Infrastructure\Controller\Api\User\UpdateUserController;
use App\Infrastructure\Controller\Api\User\DeleteUserController;

#[ApiResource(
    uriTemplate: '/users',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Get(
            uriTemplate: '/users/{id}',
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN') or object == user"
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
//        new Post(
//            uriTemplate: '/auth/login',
//            controller: LoginController::class,
//            name: 'auth_login'
//        ),
//        new Post(
//            uriTemplate: '/auth/register',
//            controller: RegisterController::class,
//            name: 'auth_register'
//        )
    ],
    class: User::class,
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class UserResource
{
}
