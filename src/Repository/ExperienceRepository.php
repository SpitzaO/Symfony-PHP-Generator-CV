<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Experience;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Experience>
 */
class ExperienceRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }
}
