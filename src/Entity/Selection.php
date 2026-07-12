<?php

namespace App\Entity;

use App\Repository\SelectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SelectionRepository::class)]
class Selection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $equipeChoisie = null;

    #[ORM\Column(nullable: true)]
    private ?float $cote = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typePari = null;

    #[ORM\Column(nullable: true)]
    private ?float $miseIndividuelle = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $resultat = null;

    #[ORM\ManyToOne(inversedBy: 'selections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pari $pari = null;

    #[ORM\ManyToOne]
    private ?MatchNba $matchNba = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipeChoisie(): ?string
    {
        return $this->equipeChoisie;
    }

    public function setEquipeChoisie(?string $equipeChoisie): static
    {
        $this->equipeChoisie = $equipeChoisie;

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

    public function getTypePari(): ?string
    {
        return $this->typePari;
    }

    public function setTypePari(?string $typePari): static
    {
        $this->typePari = $typePari;

        return $this;
    }

    public function getMiseIndividuelle(): ?float
    {
        return $this->miseIndividuelle;
    }

    public function setMiseIndividuelle(?float $miseIndividuelle): static
    {
        $this->miseIndividuelle = $miseIndividuelle;

        return $this;
    }

    public function getResultat(): ?string
    {
        return $this->resultat;
    }

    public function setResultat(?string $resultat): static
    {
        $this->resultat = $resultat;

        return $this;
    }

    public function getPari(): ?Pari
    {
        return $this->pari;
    }

    public function setPari(?Pari $pari): static
    {
        $this->pari = $pari;

        return $this;
    }

    public function getMatchNba(): ?MatchNba
    {
        return $this->matchNba;
    }

    public function setMatchNba(?MatchNba $matchNba): static
    {
        $this->matchNba = $matchNba;

        return $this;
    }
}
