<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api\Article;

use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Command\Article\CreateArticle\CreateArticleCommand;
use App\Domain\Article\Exception\ArticleAccessDeniedDomainException;
use App\Domain\Article\Exception\ArticleDomainException;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Domain\User\Entity\User;
use App\Infrastructure\Controller\Api\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreateArticleController extends BaseController
{
    public function __construct(
        private readonly CreationCommandBusInterface $commandBus,
    ) {
    }

    #[Route('/api/articles', name: 'api_articles_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/articles',
        description: 'Create new article',
        summary: 'Create new article',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content'],
                properties: [
                    'title' => new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'What is Lorem Ipsum?'
                    ),
                    'content' => new OA\Property(
                        property: 'content',
                        type: 'string',
                        example: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Articles'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Article created successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article created successfully'
                        ),
                        'article' => new OA\Property(
                            property: 'article',
                            properties: [
                                'id' => new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Validation failed'
                        ),
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: [
                                'title' => 'Title must be between 10 and 255 characters long',
                                'content' => 'Content must be at least 100 characters long'
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(ref: '#/components/responses/NotAuthenticatedError', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/AccessDeniedError', response: Response::HTTP_FORBIDDEN),
            new OA\Response(ref: '#/components/responses/InternalServerError', response: Response::HTTP_INTERNAL_SERVER_ERROR),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode(json: $request->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

            $user = $this->getUser();
            if (!$user instanceof User) {
                return $this->notAuthenticated();
            }

            $command = CreateArticleCommand::fromApiArray(data: $data, author: $user);
            $articleId = $this->commandBus->handle(command: $command);

            return $this->success(message: 'Article created successfully', response_data: ['user' => ['id' => $articleId]]);
        } catch (\JsonException) {
            return $this->invalidJson();
        } catch (ValidationErrorDomainException $e) {
            return $this->error(error: $e->getMessage(), message: $e->getErrors());
        } catch (ArticleAccessDeniedDomainException $e) {
            return $this->accessDenied(message: $e->getMessage());
        } catch (ArticleDomainException $e) {
            return $this->error(error: $e->getMessage(), code: $e->getCode());
        } catch (\Exception $e) {
            return $this->exception(message: $e->getMessage());
        }
    }
}
