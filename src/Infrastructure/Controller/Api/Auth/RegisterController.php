<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Auth;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);
            $command = CreateUserCommand::fromApiArray(data: $data);
            $userId = $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User registered successfully',
                'user' => ['id' => $userId],
            ], status: 201);
        } catch (\JsonException $e) {
            return new JsonResponse([
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], status: 400);
        } catch (ValidationErrorException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => $e->getErrors(),
            ], status: 400);
        } catch (UserDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'User registration failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
