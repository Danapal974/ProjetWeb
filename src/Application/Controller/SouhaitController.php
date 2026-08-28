<?php

namespace App\Application\Controller;

use App\Domain\Souhait;
use App\Domain\Offres;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class SouhaitController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // Affiche la liste des souhaits de l'utilisateur
    public function souhait(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $session = $request->getAttribute('session');
        $user = $request->getAttribute('user');
        $repository = $this->em->getRepository(Souhait::class);

        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $parPage = 6;
        $offset = ($page - 1) * $parPage;

        $total = $repository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $souhaits = $repository->createQueryBuilder('s')
            ->where('s.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('s.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($parPage)
            ->getQuery()
            ->getResult();

        $totalPages = (int)ceil($total / $parPage);

        return $view->render($response, 'liste_de_souhait.html.twig', [
            'role' => $session['userRole'] ?? '',
            'souhaits' => $souhaits,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function ajouter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];
        $offre = $this->em->find(Offres::class, $id);
        $user = $request->getAttribute('user');

        if ($offre && $user) {
            $dejaSouhaite = $this->em->getRepository(Souhait::class)->findOneBy([ // Vérifie si déjà dans les souhaits
                'utilisateur' => $user,
                'nom' => $offre->getNom()
            ]);

            if (!$dejaSouhaite) {
                $souhait = new Souhait(
                    $offre->getNom(),
                    $offre->getDomaine(),
                    $offre->getLieu(),
                    $offre->getEmail(),
                    $offre->getDescription(),
                    $offre->getDuree(),
                    $offre->getNiveau(),
                    $offre->getSalaire(),
                    $user
                );
                $this->em->persist($souhait);
                $this->em->flush();
            }
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('souhait');
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];
        $souhait = $this->em->find(Souhait::class, $id);

        if ($souhait) {
            $this->em->remove($souhait);
            $this->em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('souhait');
        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
