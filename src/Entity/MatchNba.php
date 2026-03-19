<?php

namespace App\Entity;

use App\Repository\MatchNbaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchNbaRepository::class)]
#[ORM\Table(name: 'match_nba')]
class MatchNba
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $team1 = null;

    #[ORM\Column(length: 255)]
    private ?string $team2 = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $cote1 = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $cote2 = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateMatch = null;

    #[ORM\Column(length: 50)]
    private string $statut = 'a_venir';

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $scoreTeam1 = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $scoreTeam2 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $resultat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam1(): ?string
    {
        return $this->team1;
    }

    public function setTeam1(string $team1): static
    {
        $this->team1 = $team1;
        return $this;
    }

    public function getTeam2(): ?string
    {
        return $this->team2;
    }

    public function setTeam2(string $team2): static
    {
        $this->team2 = $team2;
        return $this;
    }

    public function getCote1(): ?float
    {
        return $this->cote1;
    }

    public function setCote1(float $cote1): static
    {
        $this->cote1 = $cote1;
        return $this;
    }

    public function getCote2(): ?float
    {
        return $this->cote2;
    }

    public function setCote2(float $cote2): static
    {
        $this->cote2 = $cote2;
        return $this;
    }

    public function getDateMatch(): ?\DateTime
    {
        return $this->dateMatch;
    }

    public function setDateMatch(?\DateTime $dateMatch): static
    {
        $this->dateMatch = $dateMatch;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getScoreTeam1(): ?int
    {
        return $this->scoreTeam1;
    }

    public function setScoreTeam1(?int $scoreTeam1): static
    {
        $this->scoreTeam1 = $scoreTeam1;
        return $this;
    }

    public function getScoreTeam2(): ?int
    {
        return $this->scoreTeam2;
    }

    public function setScoreTeam2(?int $scoreTeam2): static
    {
        $this->scoreTeam2 = $scoreTeam2;
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
