<?php

namespace App\Tests\Functional\Security;

use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class SecurityAccessControlTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    // ========== Public Access Tests ==========

    public function testPublicEndpointsAreAccessibleWithoutAuthentication(): void
    {
        $publicEndpoints = [
            ['POST', '/api/auth/login'],
            ['POST', '/api/auth/register'],
            ['GET', '/api/doc'],
            ['GET', '/'],
        ];

        foreach ($publicEndpoints as [$method, $path]) {
            $this->client->request($method, $path);

            $this->assertNotEquals(
                Response::HTTP_UNAUTHORIZED,
                $this->client->getResponse()->getStatusCode(),
                "Public endpoint {$method} {$path} should be accessible without authentication"
            );
        }
    }

    // ========== User Management Tests ==========

    public function testUserEndpointsRequireAdminRole(): void
    {
        $userEndpoints = [
            ['GET', '/api/users', 'List users'],
            ['POST', '/api/users', 'Create user'],
            ['GET', '/api/users/1', 'Get user'],
            ['PUT', '/api/users/1', 'Update user'],
            ['DELETE', '/api/users/1', 'Delete user'],
        ];

        foreach ($userEndpoints as [$method, $path, $description]) {
            // Test bez autentizace
            $this->client->request($method, $path);
            $this->assertEquals(
                Response::HTTP_UNAUTHORIZED,
                $this->client->getResponse()->getStatusCode(),
                "{$description} should require authentication"
            );

            // Test s READER rolí
            $this->client->request($method, $path, [], [], $this->getAuthHeaders('reader@test.com'));
            $this->assertEquals(
                Response::HTTP_FORBIDDEN,
                $this->client->getResponse()->getStatusCode(),
                "{$description} should be forbidden for READER"
            );

            // Test s AUTHOR rolí
            $this->client->request($method, $path, [], [], $this->getAuthHeaders('author@test.com'));
            $this->assertEquals(
                Response::HTTP_FORBIDDEN,
                $this->client->getResponse()->getStatusCode(),
                "{$description} should be forbidden for AUTHOR"
            );

            // Test s ADMIN rolí
            $this->client->request($method, $path, [], [], $this->getAuthHeaders('admin@test.com'));
            $this->assertNotEquals(
                Response::HTTP_FORBIDDEN,
                $this->client->getResponse()->getStatusCode(),
                "{$description} should be accessible for ADMIN"
            );
        }
    }

    // ========== Article Tests ==========

    public function testArticleCreationRequiresAuthorOrAdmin(): void
    {
        $articleData = json_encode([
            'title' => 'Test Article Title for Security Testing',
            'content' => 'This is test article content for security testing that is long enough to pass validation requirements.'
        ]);

        // Test s READER rolí
        $this->client->request(
            'POST',
            '/api/articles',
            [],
            [],
            $this->getAuthHeaders('reader@test.com', 'application/json'),
            $articleData
        );
        $this->assertEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode(),
            "Article creation should be forbidden for READER"
        );

        // Test s AUTHOR rolí
        $this->client->request(
            'POST',
            '/api/articles',
            [],
            [],
            $this->getAuthHeaders('author@test.com', 'application/json'),
            $articleData
        );
        $this->assertNotEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode(),
            "Article creation should be accessible for AUTHOR"
        );

        // Test s ADMIN rolí
        $this->client->request(
            'POST',
            '/api/articles',
            [],
            [],
            $this->getAuthHeaders('admin@test.com', 'application/json'),
            $articleData
        );
        $this->assertNotEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode(),
            "Article creation should be accessible for ADMIN"
        );
    }

    public function testArticleReadingRequiresAuthentication(): void
    {
        // Test bez autentizace
        $this->client->request('GET', '/api/articles');
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $this->client->getResponse()->getStatusCode(),
            "Article reading should require authentication"
        );

        // Test se všemi rolemi
        $testUsers = ['reader@test.com', 'author@test.com', 'admin@test.com'];

        foreach ($testUsers as $email) {
            $this->client->request('GET', '/api/articles', [], [], $this->getAuthHeaders($email));
            $this->assertNotEquals(
                Response::HTTP_UNAUTHORIZED,
                $this->client->getResponse()->getStatusCode(),
                "Article reading should be accessible for authenticated user ({$email})"
            );
            $this->assertNotEquals(
                Response::HTTP_FORBIDDEN,
                $this->client->getResponse()->getStatusCode(),
                "Article reading should not be forbidden for authenticated user ({$email})"
            );
        }
    }

    public function testRoleHierarchyWorksCorrectly(): void
    {
        // Test ADMIN má přístup ke všemu
        $this->client->request('GET', '/api/users', [], [], $this->getAuthHeaders('admin@test.com'));
        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/articles', [], [], $this->getAuthHeaders('admin@test.com'));
        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        // Test AUTHOR může spravovat články, ale ne uživatele
        $this->client->request('GET', '/api/articles', [], [], $this->getAuthHeaders('author@test.com'));
        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/users', [], [], $this->getAuthHeaders('author@test.com'));
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        // Test READER může jen číst články
        $this->client->request('GET', '/api/articles', [], [], $this->getAuthHeaders('reader@test.com'));
        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/users', [], [], $this->getAuthHeaders('reader@test.com'));
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    // ========== Helper Methods ==========

    private function getAuthHeaders(string $email, string $contentType = null): array
    {
        // Najde uživatele v databázi (fixtures data)
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception("Test user {$email} not found in database - check fixtures");
        }

        // Vygeneruje JWT token
        $token = $this->jwtManager->create($user);

        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        if ($contentType) {
            $headers['CONTENT_TYPE'] = $contentType;
        }

        return $headers;
    }
}
