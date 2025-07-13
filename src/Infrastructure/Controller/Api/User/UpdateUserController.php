<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\UpdateUser\UpdateUserCommand;
use App\Domain\User\ValueObject\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UpdateUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            $command = new UpdateUserCommand(
                id: $id,
                email: $data['email'],
                name: $data['name'],
                role: UserRole::from($data['role'])
            );

            $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $id,
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role'],
                ],
            ], 200);
        } catch (\JsonException $e) {
            return new JsonResponse([
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\ValueError $e) {
            return new JsonResponse([
                'error' => 'Invalid role value',
                'message' => 'Role must be one of: admin, author, reader',
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'User update failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
