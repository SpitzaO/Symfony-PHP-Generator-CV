<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Interest;
use App\Tests\DatabaseTestCase;

final class InterestControllerTest extends DatabaseTestCase
{
    public function testAdminCreatesAnInterest(): void
    {
        $this->createProfile();
        $this->logInAsAdmin();

        $crawler = $this->client->request('GET', '/interest/new');
        self::assertResponseIsSuccessful();

        $this->client->submit($crawler->selectButton('Zapisz')->form([
            'interest[name]' => 'Photography',
        ]));

        self::assertResponseRedirects('/interest');
        $this->client->followRedirect();
        self::assertSelectorTextContains('td', 'Photography');

        self::assertNotNull(
            $this->entityManager->getRepository(Interest::class)->findOneBy(['name' => 'Photography']),
        );
    }

    public function testNewRedirectsHomeWhenThereIsNoProfile(): void
    {
        $this->logInAsAdmin();

        $this->client->request('GET', '/interest/new');

        self::assertResponseRedirects('/');
    }

    public function testAdminEditsAnInterest(): void
    {
        $interest = $this->createInterest('Chess');
        $this->logInAsAdmin();

        $crawler = $this->client->request('GET', '/interest/'.$interest->getId().'/edit');
        self::assertResponseIsSuccessful();

        $this->client->submit($crawler->selectButton('Zaktualizuj')->form([
            'interest[name]' => 'Speed chess',
        ]));

        self::assertResponseRedirects('/interest');

        $this->entityManager->clear();
        self::assertSame(
            'Speed chess',
            $this->entityManager->find(Interest::class, $interest->getId())?->getName(),
        );
    }

    public function testAdminDeletesAnInterest(): void
    {
        $interest = $this->createInterest('Bouldering');
        $id = $interest->getId();
        $this->logInAsAdmin();

        $crawler = $this->client->request('GET', '/interest/'.$id);
        $this->client->submit($crawler->selectButton('Usuń')->form());

        self::assertResponseRedirects('/interest');

        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Interest::class, $id));
    }

    public function testInterestsShowUpOnTheCv(): void
    {
        $this->createInterest('Photography');

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Photography');
    }

    private function createInterest(string $name): Interest
    {
        $profile = $this->createProfile();

        $interest = (new Interest())->setName($name)->setProfile($profile);
        $this->entityManager->persist($interest);
        $this->entityManager->flush();

        // Detach everything so the request under test loads the profile from the
        // database. Without this, $profile->interests stays as the empty collection
        // this method created, and a fetch-join will not repopulate it.
        $this->entityManager->clear();

        return $interest;
    }
}
