<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\Article\DeleteArticle\DeleteArticleCommand;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Exception\UserDomainException;
use App\Infrastructure\Controller\Api\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DeleteArticleController extends BaseController
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

            return $this->success(message: 'Article deleted successfully');
        } catch (ArticleDomainException) {
            return $this->notFound();
        } catch (ValidationErrorDomainException $e) {
            return $this->error(error: $e->getMessage(), message: $e->getErrors());
        } catch (UserDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
