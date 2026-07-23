<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\DatabaseTestCase;

/**
 * Exercises the real form_login path, including the CSRF token — loginUser() in the
 * other tests bypasses all of it.
 *
 * Note on ordering: login_throttling allows 5 attempts per 15 minutes and resets the
 * counter on a successful login. The success case runs first on purpose, so repeated
 * runs of the suite never accumulate failed attempts in the rate limiter's cache.
 */
final class LoginTest extends DatabaseTestCase
{
    private const PASSWORD = 'test-password';

    public function testValidCredentialsLogIn(): void
    {
        $this->createProfile();

        $this->submitLogin('admin', self::PASSWORD);

        self::assertResponseRedirects('http://localhost/');
        $this->client->followRedirect();
        self::assertSelectorTextContains('span', 'Tryb administratora');
    }

    public function testInvalidCredentialsAreRejected(): void
    {
        $this->createProfile();

        $this->submitLogin('admin', 'not-the-password');

        self::assertResponseRedirects('http://localhost/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'Nieprawidłowe dane.');

        // Leave the throttling counter clean for the next run.
        $this->submitLogin('admin', self::PASSWORD);
        self::assertResponseRedirects('http://localhost/');
    }

    public function testLoggedInAdminIsSentHomeFromTheLoginPage(): void
    {
        $this->createProfile();
        $this->logInAsAdmin();

        $this->client->request('GET', '/login');

        self::assertResponseRedirects('/');
    }

    private function submitLogin(string $username, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->client->submit($crawler->selectButton('Zaloguj się')->form([
            '_username' => $username,
            '_password' => $password,
        ]));
    }
}
