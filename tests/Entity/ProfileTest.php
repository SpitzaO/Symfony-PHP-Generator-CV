<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Competency;
use App\Entity\CompetencyItem;
use App\Entity\Experience;
use App\Entity\Interest;
use App\Entity\Profile;
use PHPUnit\Framework\TestCase;

/**
 * The collection helpers have to keep both sides of each relation in sync — Doctrine
 * only persists the owning side, so an entry added without its back-reference set
 * would be dropped on flush.
 */
final class ProfileTest extends TestCase
{
    public function testAddingAnInterestSetsTheBackReference(): void
    {
        $profile = new Profile();
        $interest = new Interest();

        $profile->addInterest($interest);

        self::assertTrue($profile->getInterests()->contains($interest));
        self::assertSame($profile, $interest->getProfile());
    }

    public function testAddingTheSameInterestTwiceIsANoop(): void
    {
        $profile = new Profile();
        $interest = new Interest();

        $profile->addInterest($interest);
        $profile->addInterest($interest);

        self::assertCount(1, $profile->getInterests());
    }

    public function testRemovingAnInterestDetachesIt(): void
    {
        $profile = new Profile();
        $interest = new Interest();
        $profile->addInterest($interest);

        $profile->removeInterest($interest);

        self::assertCount(0, $profile->getInterests());
        self::assertNull($interest->getProfile());
    }

    public function testExperienceRelationIsSymmetric(): void
    {
        $profile = new Profile();
        $experience = new Experience();

        $profile->addExperience($experience);
        self::assertSame($profile, $experience->getProfile());

        $profile->removeExperience($experience);
        self::assertCount(0, $profile->getExperiences());
        self::assertNull($experience->getProfile());
    }

    public function testCompetencyItemsKeepTheirParent(): void
    {
        $competency = new Competency();
        $item = new CompetencyItem();

        $competency->addItem($item);
        self::assertSame($competency, $item->getCompetency());

        $competency->removeItem($item);
        self::assertCount(0, $competency->getItems());
        self::assertNull($item->getCompetency());
    }

    public function testANewProfileStartsWithEmptyCollections(): void
    {
        $profile = new Profile();

        self::assertCount(0, $profile->getEducation());
        self::assertCount(0, $profile->getExperiences());
        self::assertCount(0, $profile->getSkills());
        self::assertCount(0, $profile->getInterests());
        self::assertCount(0, $profile->getCompetencies());
    }
}
