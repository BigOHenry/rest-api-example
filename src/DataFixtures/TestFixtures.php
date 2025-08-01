<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Article\Entity\Article;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Creates test users with different roles
        $users = [
            [
                'email' => 'admin@test.com',
                'name' => 'Admin User',
                'role' => UserRole::ADMIN,
                'reference' => 'user-admin',
            ],
            [
                'email' => 'author@test.com',
                'name' => 'Author User',
                'role' => UserRole::AUTHOR,
                'reference' => 'user-author',
            ],
            [
                'email' => 'reader@test.com',
                'name' => 'Reader User',
                'role' => UserRole::READER,
                'reference' => 'user-reader',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create(
                $userData['email'],
                'tmp',
                $userData['name'],
                $userData['role']
            );

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'Password1234.');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $this->addReference($userData['reference'], $user);
        }

        $manager->flush();

        // Creates test articles
        $articles = [
            [
                'title' => 'Test Article 1 - Security Testing',
                'content' => 'This is the first test article content for security testing.
                 It contains enough text to pass validation requirements and demonstrates proper access control testing.',
                'author' => 'user-author',
            ],
            [
                'title' => 'Test Article 2 - Access Control',
                'content' => 'This is the second test article content focusing on access control mechanisms.
                 This article helps verify that role-based permissions work correctly.',
                'author' => 'user-author',
            ],
            [
                'title' => 'Admin Article - Management Content',
                'content' => 'This article was created to test admin-level access and permissions.
                 It verifies that higher-level roles can access all necessary resources.',
                'author' => 'user-admin',
            ],
        ];

        foreach ($articles as $articleData) {
            $author = $this->getReference($articleData['author'], User::class);

            $article = Article::create(
                $articleData['title'],
                $articleData['content'],
                $author
            );

            $manager->persist($article);
        }

        $manager->flush();

        echo "✅ Test fixtures loaded successfully:\n";
        echo "  - 3 test users with different roles\n";
        echo "  - 3 test articles with different authors\n";
    }
}
