<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Competency;
use App\Entity\CompetencyItem;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Skill;
use App\Tests\DatabaseTestCase;

final class HomeControllerTest extends DatabaseTestCase
{
    public function testVisitorSeesAPlaceholderWhenThereIsNoProfile(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'CV już wkrótce');
    }

    public function testAdminIsInvitedToCreateTheProfile(): void
    {
        $this->logInAsAdmin();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Zbudujmy Twoje CV');
        self::assertCount(1, $crawler->filter('a[href="/profile/new"]'));
    }

    public function testHomepageRendersEveryCvSection(): void
    {
        $this->createFullProfile();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Ada Lovelace');

        $body = $crawler->html();
        self::assertStringContainsString('Analytical Engines Ltd', $body);
        self::assertStringContainsString('Assembly', $body);
        self::assertStringContainsString('Mathematics', $body);
        self::assertStringContainsString('Gallup', $body);
        self::assertStringContainsString('Achiever', $body);
        self::assertStringContainsString('href="https://github.com/adalovelace"', $body);
        self::assertStringContainsString('href="https://ada.example"', $body);
    }

    public function testAdminControlsAreHiddenFromVisitors(): void
    {
        $this->createProfile();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('a[href="/cv.pdf"]'));
        self::assertSelectorNotExists('a[href="/interest"]');
    }

    public function testPdfExportReturnsAPdfDocument(): void
    {
        $this->createFullProfile();
        $this->logInAsAdmin();

        $this->client->request('GET', '/cv.pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', $this->client->getResponse()->getContent() ?: '');
    }

    public function testPdfExportRedirectsWhenThereIsNoProfile(): void
    {
        $this->logInAsAdmin();

        $this->client->request('GET', '/cv.pdf');

        self::assertResponseRedirects('/');
    }

    /**
     * A profile with one entry in every collection — enough to catch a section that
     * stops rendering, and to exercise the nested competency/items relation.
     */
    private function createFullProfile(): void
    {
        $profile = $this->createProfile();
        $profile->setGithubUrl('https://github.com/adalovelace');
        $profile->setWebsiteUrl('https://ada.example');

        $profile->addExperience(
            (new Experience())
                ->setCompany('Analytical Engines Ltd')
                ->setPosition('Programmer')
                ->setDescription('Wrote the first algorithm.')
                ->setStartDate(new \DateTimeImmutable('1842-01-01'))
        );
        $profile->addSkill((new Skill())->setName('Assembly')->setLevel('Expert'));
        $profile->addInterest((new Interest())->setName('Mathematics'));

        $competency = (new Competency())->setName('Gallup');
        $competency->addItem((new CompetencyItem())->setName('Achiever'));
        $profile->addCompetency($competency);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
