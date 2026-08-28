<?php

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\DBAL\Types\Types;

#[Entity, Table(name: 'utilisateurs')] // nom table
class Utilisateur
{

    //propriete colonne
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', nullable: false)]
    private string $nom;

    #[Column(type: 'string', nullable: false)]
    private string $prenom;

    #[Column(type: 'string', nullable: false)]
    private string $campus;

    #[Column(type: 'string', nullable: false)]
    private string $email;

    #[Column(type: 'string', nullable: false)]
    private string $mot_de_passe;

    #[Column(type: Types::STRING, enumType: Role::class)]
    private Role $role = Role::ETUDIANT;

    #[Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    public function __construct(string $nom, string $prenom, string $campus, string $email, string $mot_de_passe, Role $role = Role::ETUDIANT)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->campus = $campus;
        $this->email = $email;
        $this->mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $this->role = $role;
        $this->createdAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getCampus(): string
    {
        return $this->campus;
    }

    public function setCampus(string $campus): void
    {
        $this->campus = $campus;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getMotDePasse(): string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): void
    {
        $this->mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    }
    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }


    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
