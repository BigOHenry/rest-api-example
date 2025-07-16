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
        $data = json_decode($request->getContent(), associative: true);

        try {
            $command = new CreateUserCommand(
                $data['email'],
                $data['password'],
                $data['name'],
                UserRole::from($data['role'])
            );

            $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User created successfully',
                'user' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role'],
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
