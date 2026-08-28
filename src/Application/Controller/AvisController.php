<?php

namespace App\Application\Controller;

use App\Domain\Avis;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class AvisController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function avis(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $donnees = $request->getParsedBody(); //recup donne formulaire
        $commentaire = $donnees['commentaire'] ?? '';
        $note = (int)($donnees['note'] ?? 0);
        $entrepriseId = (int)$args['id']; // recup id depuis url


        $avis = new Avis($commentaire, $note, $entrepriseId);
        $this->em->persist($avis); //prepare l'envoie
        $this->em->flush(); // fait l'envoie


        return $response->withHeader('Location', '/entreprise')->withStatus(302);
    }

    //affiche formulaire avis
    public function afficher(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $entreprise = (int)$args['id'];
        return $view->render($response, 'Avis.html.twig', ['entrepriseId' => $entreprise]);
    }
}
