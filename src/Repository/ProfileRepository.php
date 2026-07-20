<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Profile>
 */
class ProfileRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * The app manages a single CV, so this returns the one (first) profile deterministically.
     */
    public function findMain(): ?Profile
    {
        return $this->findOneBy([], ['id' => 'ASC']);
    }
}
