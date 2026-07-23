<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Experience;
use App\Tests\DatabaseTestCase;

/**
 * The whole authorisation model of this app is "public CV, everything else admin-only".
 * These tests pin that down route by route, so adding a controller without #[IsGranted]
 * (or loosening access_control) fails the build instead of silently opening the panel.
 */
final class AccessControlTest extends DatabaseTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function adminOnlyRoutes(): iterable
    {
        yield 'profile new' => ['GET', '/profile/new'];
        yield 'profile edit' => ['GET', '/profile/1/edit'];
        yield 'profile delete' => ['POST', '/profile/1'];
        yield 'pdf export' => ['GET', '/cv.pdf'];

        foreach (['experience', 'education', 'skill', 'competency', 'interest'] as $resource) {
            yield "$resource index" => ['GET', "/$resource"];
            yield "$resource new" => ['GET', "/$resource/new"];
            yield "$resource show" => ['GET', "/$resource/1"];
            yield "$resource edit" => ['GET', "/$resource/1/edit"];
            yield "$resource delete" => ['POST', "/$resource/1"];
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function adminLandingPages(): iterable
    {
        foreach (['experience', 'education', 'skill', 'competency', 'interest'] as $resource) {
            yield "$resource index" => ["/$resource"];
            yield "$resource new" => ["/$resource/new"];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminOnlyRoutes')]
    public function testAnonymousUserIsSentToLogin(string $method, string $path): void
    {
        $this->client->request($method, $path);

        self::assertResponseRedirects('http://localhost/login');
    }

    public function testHomepageIsPublic(): void
    {
        $this->createProfile();

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testLoginPageIsPublic(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminLandingPages')]
    public function testAdminReachesAdminPages(string $path): void
    {
        $this->createProfile();
        $this->logInAsAdmin();

        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public function testDeleteWithoutCsrfTokenLeavesTheRecordAlone(): void
    {
        $profile = $this->createProfile();

        $experience = (new Experience())
            ->setCompany('Analytical Engines Ltd')
            ->setPosition('Programmer')
            ->setDescription('Wrote the first algorithm.')
            ->setProfile($profile);
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
        $id = $experience->getId();

        $this->logInAsAdmin();
        $this->client->request('POST', '/experience/'.$id);

        // #[IsCsrfTokenValid] throws InvalidCsrfTokenException, which extends
        // AuthenticationException — so the firewall drops the session and re-challenges
        // instead of returning 403. Either way the delete must not go through.
        self::assertResponseRedirects('http://localhost/login');

        $this->entityManager->clear();
        self::assertNotNull(
            $this->entityManager->find(Experience::class, $id),
            'A delete without a CSRF token must not remove the record.',
        );
    }
}
