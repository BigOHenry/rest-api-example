<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use App\Application\Exception\HandleProcessErrorException;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteArticleController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                return new JsonResponse(data: [
                    'error' => 'Authentication required',
                ], status: 401);
            }

            $command = new DeleteArticleCommand(id: $id, author: $user);
            $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'Article deleted successfully',
            ]);
        } catch (ArticleNotFoundException) {
            return new JsonResponse(data: null, status: 204);
        } catch (HandleProcessErrorException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode());
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Article deletion failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
