<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUsers\GetUsersQuery;
use App\Application\Query\User\GetUsers\GetUsersQueryResult;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GetUsersController extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('api/users', name: 'api_users_list', methods: ['GET'])]

    public function __invoke(): JsonResponse
    {
        try {
            $query = new GetUsersQuery();
            $result = $this->queryBus->handle(query: $query);
            \assert($result instanceof GetUsersQueryResult);

            return new JsonResponse(data: [
                'users' => $result->toArray(),
                'count' => $result->count(),
            ]);
        } catch (UserDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'User creation failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
