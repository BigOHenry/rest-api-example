<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\DeleteUser\DeleteUserCommand;
use App\Domain\User\Exception\UserNotFoundException;
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
            $command = new DeleteUserCommand(id: $id);
            $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'User deleted successfully',
            ]);
        } catch (UserNotFoundException) {
            return new JsonResponse(data: null, status: 204);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'User deletion failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
