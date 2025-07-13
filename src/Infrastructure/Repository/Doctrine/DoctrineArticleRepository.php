<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Article\Entity\Article;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<Article>
 */
class DoctrineArticleRepository extends ServiceEntityRepository implements ArticleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $article): void
    {
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?Article
    {
        return $this->find($id);
    }

    public function findByTitle(string $title): ?Article
    {
        return $this->findOneBy(['title' => $title]);
    }

    public function remove(Article $article): void
    {
        $this->getEntityManager()->remove($article);
        $this->getEntityManager()->flush();
    }
}
