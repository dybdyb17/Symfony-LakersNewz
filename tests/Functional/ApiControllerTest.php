<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    private function jsonRequest($client, string $method, string $uri, array $data, ?string $token = null): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            json_encode($data)
        );
    }

    private function loginAndGetToken($client, string $email, string $password): string
    {
        $this->jsonRequest($client, 'POST', '/api/login', [
            'email'    => $email,
            'password' => $password,
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);

        return $data['token'] ?? '';
    }

    public function testRegister(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'     => 'functional-test@lakers.com',
            'password'  => 'test123456',
            'pseudo'    => 'FuncTester',
            'firstname' => 'Test',
            'lastname'  => 'Lakers',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('functional-test@lakers.com', $data['email']);
        $this->assertEquals('FuncTester', $data['pseudo']);
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'    => 'login-test@lakers.com',
            'password' => 'test123456',
            'pseudo'   => 'LoginTester',
        ]);

        $this->jsonRequest($client, 'POST', '/api/login', [
            'email'    => 'login-test@lakers.com',
            'password' => 'test123456',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('login-test@lakers.com', $data['email']);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFail(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/login', [
            'email'    => 'nexistepas@lakers.com',
            'password' => 'wrong',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeposer(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'    => 'depot-test@lakers.com',
            'password' => 'test123456',
        ]);

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'depot-test@lakers.com']);
        $client->loginUser($user, 'web');

        $this->jsonRequest($client, 'POST', '/api/deposer', ['montant' => 100]);

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(100, $data['solde']);
    }

    public function testRetirerSoldeInsuffisant(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'    => 'retrait-test@lakers.com',
            'password' => 'test123456',
        ]);

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'retrait-test@lakers.com']);
        $client->loginUser($user, 'web');

        $this->jsonRequest($client, 'POST', '/api/retirer', ['montant' => 500]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateArticle(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'    => 'admin-test@lakers.com',
            'password' => 'test123456',
            'pseudo'   => 'AdminTester',
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['id'];

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->getConnection()->executeStatement(
            'UPDATE user SET roles = :roles WHERE id = :id',
            ['roles' => json_encode(['ROLE_ADMIN']), 'id' => $userId]
        );
        $em->clear();

        $token = $this->loginAndGetToken($client, 'admin-test@lakers.com', 'test123456');

        $this->jsonRequest($client, 'POST', '/api/admin/articles', [
            'titre'   => 'Test Article PHPUnit',
            'contenu' => 'Contenu de test PHPUnit',
        ], $token);

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test Article PHPUnit', $data['titre']);
    }

    public function testAdminAccessDenied(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/admin/users');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testContact(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/contact', [
            'nom'     => 'Test Contact',
            'email'   => 'contact-test@lakers.com',
            'sujet'   => 'Test PHPUnit',
            'message' => 'Message de test unitaire',
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testDeleteProfil(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/register', [
            'email'    => 'delete-test@lakers.com',
            'password' => 'test123456',
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['id'];

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'delete-test@lakers.com']);
        $client->loginUser($user, 'web');

        $client->request('DELETE', '/api/profil/' . $userId, [], [], ['CONTENT_TYPE' => 'application/json']);
        $this->assertResponseStatusCodeSame(200);

        // Efface la session pour simuler une requête anonyme
        $client->getCookieJar()->clear();
        $client->request('GET', '/api/profil/' . $userId, [], [], ['CONTENT_TYPE' => 'application/json']);
        $this->assertResponseStatusCodeSame(401);
    }

    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $conn = $em->getConnection();

        $conn->executeStatement("DELETE t FROM transaction t INNER JOIN user u ON t.user_id = u.id WHERE u.email LIKE '%lakers.com%'");
        $conn->executeStatement("DELETE p FROM pari p INNER JOIN user u ON p.user_id = u.id WHERE u.email LIKE '%lakers.com%'");
        $conn->executeStatement("DELETE FROM user WHERE email LIKE '%lakers.com%'");
        $conn->executeStatement("DELETE FROM article WHERE titre LIKE '%PHPUnit%'");
        $conn->executeStatement("DELETE FROM contact WHERE email LIKE '%lakers.com%'");

        $em->close();

        parent::tearDown();
    }
}
