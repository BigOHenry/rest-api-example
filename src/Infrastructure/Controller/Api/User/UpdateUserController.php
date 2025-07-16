<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\UpdateUser\UpdateUserCommand;
use App\Application\Exception\ValidationErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UpdateUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = UpdateUserCommand::fromApiArray(userId: $id, data: $data);
            $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'User updated successfully',
            ], status: 200);
        } catch (\JsonException $e) {
            return new JsonResponse(data: [
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], status: 400);
        } catch (ValidationErrorException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
                'message' => $e->getErrors(),
            ], status: 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'User update failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
