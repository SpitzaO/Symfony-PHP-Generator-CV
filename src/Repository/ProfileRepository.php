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
    /**
     * One fetch-join query per collection. Joining all of them into a single query
     * would multiply rows across every association, so they run one at a time; each
     * one hydrates its collection on the Profile already held in the identity map.
     */
    private const RELATION_QUERIES = [
        'SELECT p, e FROM App\Entity\Profile p LEFT JOIN p.experiences e WHERE p = :profile ORDER BY e.startDate DESC, e.id DESC',
        'SELECT p, ed FROM App\Entity\Profile p LEFT JOIN p.education ed WHERE p = :profile ORDER BY ed.startDate DESC, ed.id DESC',
        'SELECT p, s FROM App\Entity\Profile p LEFT JOIN p.skills s WHERE p = :profile ORDER BY s.name ASC',
        'SELECT p, i FROM App\Entity\Profile p LEFT JOIN p.interests i WHERE p = :profile ORDER BY i.name ASC',
        // Items come along in the same pass — otherwise rendering the CV costs one
        // extra query per competency.
        'SELECT p, c, ci FROM App\Entity\Profile p LEFT JOIN p.competencies c LEFT JOIN c.items ci WHERE p = :profile ORDER BY c.id ASC, ci.id ASC',
    ];

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

    /**
     * Same profile as findMain(), with every collection needed to render the CV
     * already loaded. Use this for the homepage and the PDF export.
     */
    public function findMainWithRelations(): ?Profile
    {
        $profile = $this->findMain();

        if (!$profile instanceof Profile) {
            return null;
        }

        foreach (self::RELATION_QUERIES as $dql) {
            $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('profile', $profile)
                ->getResult();
        }

        return $profile;
    }
}
