<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth/login', methods: ['POST'])]
class LoginController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        // This controller will never be called because the JWT handler
        // handles logins automatically in the security firewall
        return new JsonResponse(['message' => 'Login handled by JWT']);
    }
}
