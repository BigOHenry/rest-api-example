<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\DeleteUser\DeleteUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'User deletion failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
