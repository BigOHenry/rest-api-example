<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Command\Article\UpdateArticle\UpdateArticleCommand;
use App\Application\Exception\ValidationErrorException;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Domain\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UpdateArticleController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

            $user = $this->getUser();
            if (!$user instanceof User) {
                return new JsonResponse(data: [
                    'error' => 'Authentication required',
                ], status: 401);
            }

            $command = UpdateArticleCommand::fromApiArray(articleId: $id, data: $data);
            $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'Article updated successfully',
            ], status: 200);
        } catch (\JsonException $e) {
            return new JsonResponse(data: [
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], status: 400);
        } catch (ValidationErrorException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
                'message' => $e->getErrors(),
            ], status: $e->getCode());
        } catch (ArticleDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode());
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Article update failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
