<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompetencyItem;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<CompetencyItem>
 */
class CompetencyItemRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetencyItem::class);
    }
}
