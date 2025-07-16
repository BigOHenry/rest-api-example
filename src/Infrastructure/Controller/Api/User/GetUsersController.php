<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\User;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Query\User\GetUsers\GetUsersQuery;
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
        $query = new GetUsersQuery();
        $result = $this->queryBus->handle($query);

        return new JsonResponse(['users' => $result]);
    }
}
