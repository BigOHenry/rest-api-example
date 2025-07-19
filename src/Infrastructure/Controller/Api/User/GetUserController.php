<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUser\GetUserQuery;
use App\Application\Query\User\GetUser\GetUserQueryResult;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GetUserController extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/users/{id}', name: 'api_user', methods: ['GET'])]

    public function __invoke(int $id): JsonResponse
    {
        try {
            $query = new GetUserQuery(id: $id);
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetUserQueryResult);

            if (!$result->isUserFound()) {
                return new JsonResponse(data: ['error' => 'User not found'], status: 404);
            }

            return new JsonResponse(data: $result->toArray());
        } catch (UserDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Unexpected error',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
