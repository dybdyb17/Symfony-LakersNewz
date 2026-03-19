<?php

namespace App\Entity;

use App\Repository\PariRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PariRepository::class)]
class Pari
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $equipe = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $cote = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $mise = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $gains = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, Selection>
     */
    #[ORM\OneToMany(targetEntity: Selection::class, mappedBy: 'pari')]
    private Collection $selections;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->selections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipe(): ?string
    {
        return $this->equipe;
    }

    public function setEquipe(?string $equipe): static
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getCote(): ?float
    {
        return $this->cote;
    }

    public function setCote(?float $cote): static
    {
        $this->cote = $cote;
        return $this;
    }

    public function getMise(): ?float
    {
        return $this->mise;
    }

    public function setMise(?float $mise): static
    {
        $this->mise = $mise;
        return $this;
    }

    public function getGains(): ?float
    {
        return $this->gains;
    }

    public function setGains(?float $gains): static
    {
        $this->gains = $gains;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Selection>
     */
    public function getSelections(): Collection
    {
        return $this->selections;
    }

    public function addSelection(Selection $selection): static
    {
        if (!$this->selections->contains($selection)) {
            $this->selections->add($selection);
            $selection->setPari($this);
        }

        return $this;
    }

    public function removeSelection(Selection $selection): static
    {
        if ($this->selections->removeElement($selection)) {
            // set the owning side to null (unless already changed)
            if ($selection->getPari() === $this) {
                $selection->setPari(null);
            }
        }

        return $this;
    }
}
