<?php declare(strict_types = 1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use App\Infrastructure\Controller\Api\Auth\RegisterController;

#[ApiResource(
    uriTemplate: '/auth',
    shortName: 'Auth',
    operations: [
//        new Post(
//            uriTemplate: '/auth/login',
//            openapi: new Operation(
//                responses: [
//                    '200' => new Response(
//                        description: 'Login successful',
//                        content: new \ArrayObject([
//                            'application/json' => new MediaType(
//                                schema: new \ArrayObject([
//                                    'type' => 'object',
//                                    'properties' => [
//                                        'token' => ['type' => 'string'],
//                                        'user' => ['type' => 'object'],
//                                    ],
//                                ])
//                            ),
//                        ])
//                    ),
//                    '401' => new Response(description: 'Invalid credentials'),
//                ],
//                summary: 'User login',
//                description: 'Authenticate user and return JWT token',
//                requestBody: new RequestBody(
//                    content: new \ArrayObject([
//                        'application/json' => new MediaType(
//                            schema: new \ArrayObject([
//                                'type' => 'object',
//                                'properties' => [
//                                    'email' => [
//                                        'type' => 'string',
//                                        'example' => 'user@example.com',
//                                    ],
//                                    'password' => [
//                                        'type' => 'string',
//                                        'example' => 'password123',
//                                    ],
//                                ],
//                            ])
//                        ),
//                    ])
//                )
//            ),
//            name: 'auth_login'
//        ),
        new Post(
            uriTemplate: '/auth/register',
            controller: RegisterController::class,
            openapi: new Operation(
                responses: [
                    '201' => new Response(description: 'Registration successful'),
                    '400' => new Response(description: 'Validation error'),
                ],
                summary: 'User registration',
                description: 'Register new user account',
                requestBody: new RequestBody(
                    content: new \ArrayObject(
                        [
                            'application/json' => new MediaType(
                                schema: new \ArrayObject(
                                    [
                                        'type'       => 'object',
                                        'properties' => [
                                            'email'    => [
                                                'type'    => 'string',
                                                'example' => 'user@example.com',
                                            ],
                                            'password' => [
                                                'type'    => 'string',
                                                'example' => 'password123',
                                            ],
                                            'name'     => [
                                                'type'    => 'string',
                                                'example' => 'John Doe',
                                            ],
                                            'role'     => [
                                                'type'    => 'string',
                                                'example' => 'reader',
                                            ],
                                        ],
                                    ]
                                )
                            ),
                        ]
                    )
                )
            ),
            name: 'auth_register'
        ),
    ])]
class AuthResource
{
}
