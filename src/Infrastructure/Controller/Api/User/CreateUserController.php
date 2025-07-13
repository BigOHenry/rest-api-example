<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Domain\User\ValueObject\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            $command = new CreateUserCommand(
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['name'] ?? '',
                isset($data['role']) ? UserRole::from($data['role']) : UserRole::READER
            );

            $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User created successfully',
                'user' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role'] ?? 'reader',
                ],
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'User creation failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
