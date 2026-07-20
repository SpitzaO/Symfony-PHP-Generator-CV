<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Interest;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Interest>
 */
class InterestRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Interest::class);
    }
}
