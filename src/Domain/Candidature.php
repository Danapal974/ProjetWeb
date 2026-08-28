<?php

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity, Table(name: 'candidature')]
class Candidature
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', nullable: false)]
    private string $nom;

    #[Column(type: 'string', nullable: false)]
    private string $domaine;

    #[Column(type: 'string', nullable: false)]
    private string $lieu;

    #[Column(type: 'string', nullable: false)]
    private string $email;

    #[Column(type: 'string', nullable: false)]
    private string $description;

    #[Column(type: 'string', nullable: false)]
    private string $duree;

    #[Column(type: 'string', nullable: false)]
    private string $niveau;

    #[Column(type: 'string', nullable: false)]
    private string $salaire;

    #[Column(type: 'string', nullable: false)]
    private string $statut;

    #[ManyToOne(targetEntity: Utilisateur::class)] //cela permet d'avoir plusieurs candidatures pour un utilisateur
    #[JoinColumn(nullable: false)] //permet d'avoir les id dans Utilisateurs
    private Utilisateur $utilisateur;


    #[Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    public function __construct(string $nom, string $domaine, string $lieu, string $email, string $description, string $duree, string $niveau, string $salaire, string $statut,  Utilisateur $utilisateur)
    {
        $this->nom = $nom;
        $this->domaine = $domaine;
        $this->lieu = $lieu;
        $this->email = $email;
        $this->description = $description;
        $this->duree = $duree;
        $this->niveau = $niveau;
        $this->salaire = $salaire;
        $this->statut = $statut;
        $this->utilisateur = $utilisateur;
        $this->createdAt = new DateTimeImmutable('now');
    }


    public function getId(): int
    {
        return $this->id;
    }
    //permet recup valeur
    public function getNom(): string
    {
        return $this->nom;
    }
    //permet modif valeur
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getDomaine(): string
    {
        return $this->domaine;
    }
    public function setDomaine(string $domaine): void
    {
        $this->domaine = $domaine;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }
    public function setLieu(string $lieu): void
    {
        $this->lieu = $lieu;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDuree(): string
    {
        return $this->duree;
    }
    public function setDuree(string $duree): void
    {
        $this->duree = $duree;
    }

    public function getNiveau(): string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): void
    {
        $this->niveau = $niveau;
    }

    public function getSalaire(): string
    {
        return $this->salaire;
    }

    public function setSalaire(string $salaire): void
    {
        $this->salaire = $salaire;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
