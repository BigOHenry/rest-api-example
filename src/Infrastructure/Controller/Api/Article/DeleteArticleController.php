<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use App\Domain\Article\Exception\ArticleDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DeleteArticleController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route('api/articles', name: 'api_articles_delete', methods: ['DELETE'])]
    public function __invoke(int $id): JsonResponse
    {
        try {
            $command = new DeleteArticleCommand(id: $id);
            $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'Article deleted successfully',
            ]);
        } catch (ArticleDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Article deletion failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
