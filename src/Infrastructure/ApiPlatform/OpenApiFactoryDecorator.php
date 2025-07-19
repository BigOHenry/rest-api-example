<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[AsDecorator(
    decorates: 'api_platform.openapi.factory',
    priority: -25,
    onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE,
)]
readonly class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $authPath = $openApi->getPaths()->getPath('/api/auth/login');

        if ($authPath instanceof PathItem && $authPath->getPost() instanceof Operation) {
            $post = $authPath->getPost()->withTags(['Auth']);

            $openApi->getPaths()->addPath(
                path: '/api/auth/login',
                pathItem: (new PathItem())->withPost($post),
            );
        }

        return $openApi;
    }
}
