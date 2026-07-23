<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

/**
 * Base class for tests that need a database. The schema is rebuilt from the entity
 * mapping before each test against the SQLite file configured in .env.test, so the
 * suite runs without MySQL and every test starts from an empty database.
 */
abstract class DatabaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    /**
     * The password hash has to match what the in-memory provider holds. Symfony
     * de-authenticates a session whose stored user differs from the refreshed one, so
     * logging in with a null password would bounce straight back to /login.
     */
    protected function logInAsAdmin(): void
    {
        $hash = $_ENV['ADMIN_PASSWORD_HASH'] ?? $_SERVER['ADMIN_PASSWORD_HASH'] ?? '';

        $this->client->loginUser(new InMemoryUser('admin', $hash, ['ROLE_ADMIN']));
    }

    /**
     * Persists a minimally valid profile — every non-nullable field filled, nothing else.
     */
    protected function createProfile(): Profile
    {
        $profile = (new Profile())
            ->setFirstName('Ada')
            ->setLastName('Lovelace')
            ->setEmail('ada@example.com')
            ->setPhone('+48 123 456 789')
            ->setBirthDate(new \DateTimeImmutable('1815-12-10'));

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }
}
