<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'avis')]
class Avis
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'text', nullable: false)]
    private string $commentaire;

    #[Column(type: 'integer', nullable: false)]
    private int $note;

    #[Column(type: 'integer', name: 'entreprise_id')]
    private int $entrepriseId;

    public function __construct(string $commentaire, int $note, int $entrepriseId)
    {
        $this->commentaire = $commentaire;
        $this->note = $note;
        $this->entrepriseId = $entrepriseId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        if ($note <= 5) { // on vérifie si c'est bien entre 0 et 5
            $this->note = $note;
        }
    }

    public function getEntrepriseId(): string
    {
        return $this->entrepriseId;
    }
}
