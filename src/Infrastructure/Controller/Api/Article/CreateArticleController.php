<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\Article\CreateArticle\CreateArticleCommand;
use App\Application\Exception\ValidationErrorException;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Domain\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CreateArticleController extends AbstractController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    #[Route('/api/articles', name: 'api_articles_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

            $user = $this->getUser();
            if (!$user instanceof User) {
                return new JsonResponse(data: [
                    'error' => 'Authentication required',
                ], status: 401);
            }

            $command = CreateArticleCommand::fromApiArray(data: $data, author: $user);
            $articleId = $this->commandBus->handle(command: $command);

            return new JsonResponse(data: [
                'message' => 'Article created successfully',
                'article' => ['id' => $articleId],
            ], status: 201);
        } catch (\JsonException $e) {
            return new JsonResponse(data: [
                'error' => 'Invalid JSON format',
                'message' => $e->getMessage(),
            ], status: 400);
        } catch (ValidationErrorException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
                'message' => $e->getErrors(),
            ], status: 400);
        } catch (ArticleDomainException $e) {
            return new JsonResponse(data: [
                'error' => $e->getMessage(),
            ], status: $e->getCode() ?? 400);
        } catch (\Exception $e) {
            return new JsonResponse(data: [
                'error' => 'Article creation failed',
                'message' => $e->getMessage(),
            ], status: 400);
        }
    }
}
