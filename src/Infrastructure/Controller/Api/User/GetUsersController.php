<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUsers\GetUsersQuery;
use App\Application\Query\User\GetUsers\GetUsersQueryResult;
use App\Domain\User\Exception\UserDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetUsersController extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

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
            ], status: $e->getCode());
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'User creation failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
