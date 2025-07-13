<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Auth;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\User\CreateUser\CreateUserCommand;
use App\Domain\User\ValueObject\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        if (!$this->validateRegistrationData($data)) {
            return new JsonResponse([
                'error' => 'Invalid data',
                'message' => 'Required fields: email, password, name, role',
            ], 400);
        }

        try {
            $command = new CreateUserCommand(
                $data['email'],
                $data['password'],
                $data['name'],
                UserRole::from($data['role'] ?? UserRole::READER)
            );

            $this->commandBus->handle($command);

            return new JsonResponse([
                'message' => 'User registered successfully',
                'user' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $data['role'] ?? UserRole::READER,
                ],
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @param string[]|null $data
     */
    private function validateRegistrationData(?array $data): bool
    {
        if (!$data) {
            return false;
        }

        $requiredFields = ['email', 'password', 'name'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }

        if (isset($data['role']) && !\in_array($data['role'], ['admin', 'author', 'reader'], true)) {
            return false;
        }

        return filter_var($data['email'], \FILTER_VALIDATE_EMAIL) && mb_strlen($data['password']) >= 8;
    }
}
