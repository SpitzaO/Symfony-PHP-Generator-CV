<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Education;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Education>
 */
class EducationRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Education::class);
    }
}
