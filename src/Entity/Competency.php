<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompetencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetencyRepository::class)]
class Competency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'competencies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profile $profile = null;

    /**
     * @var Collection<int, CompetencyItem>
     */
    #[ORM\OneToMany(targetEntity: CompetencyItem::class, mappedBy: 'competency', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection<int, CompetencyItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CompetencyItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCompetency($this);
        }

        return $this;
    }

    public function removeItem(CompetencyItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getCompetency() === $this) {
                $item->setCompetency(null);
            }
        }

        return $this;
    }
}
