<?php

namespace App\Application\Controller;

use App\Domain\Entreprise;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class GestionEntreprise
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    //pagination
    public function gestion_entreprises(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $repository = $this->em->getRepository(Entreprise::class);

        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $parPage = 9;
        $offset = ($page - 1) * $parPage;

        $totalEntreprises = $repository->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $gestion_entreprises = $repository->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($parPage)
            ->getQuery()
            ->getResult();

        $totalPages = (int)ceil($totalEntreprises / $parPage);

        return $view->render($response, 'gestion_entreprises.html.twig', [
            'gestion_entreprises' => $gestion_entreprises,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //recup donne et trim enleve espace inutile
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $nom = trim($parsedBody['nom'] ?? '');
            $domaine = trim($parsedBody['domaine'] ?? '');
            $lieu = trim($parsedBody['lieu'] ?? '');
            $email = trim($parsedBody['email'] ?? '');
            $telephone = trim($parsedBody['telephone'] ?? '');
            $description = trim($parsedBody['description'] ?? '');

            $entreprise = new Entreprise($nom, $domaine, $lieu, $email, $telephone, $description);
            $this->em->persist($entreprise);
            $this->em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('gestion_entreprises');
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int)$args['id'];
        $entreprise = $this->em->find(Entreprise::class, $id);

        if (!$entreprise) {
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $nom = trim($parsedBody['nom'] ?? '');
            $domaine = trim($parsedBody['domaine'] ?? '');
            $lieu = trim($parsedBody['lieu'] ?? '');
            $email = trim($parsedBody['email'] ?? '');
            $telephone = trim($parsedBody['telephone'] ?? '');
            $description = trim($parsedBody['description'] ?? '');

            $entreprise->setNom($nom);
            $entreprise->setDomaine($domaine);
            $entreprise->setLieu($lieu);
            $entreprise->setEmail($email);
            $entreprise->setTelephone($telephone);
            $entreprise->setDescription($description);
            $this->em->flush();


            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('gestion_entreprises');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $view->render($response, 'modifier_entreprises.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];
        $entreprise = $this->em->find(Entreprise::class, $id);

        if ($entreprise) {
            $this->em->remove($entreprise);
            $this->em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('gestion_entreprises');
        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
