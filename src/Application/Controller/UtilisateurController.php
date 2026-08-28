<?php

namespace App\Application\Controller;

use App\Domain\Utilisateur;
use App\Domain\Role;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class UtilisateurController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // Affiche la liste des utilisateurs
    public function gestion_utilisateurs(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $repository = $this->em->getRepository(Utilisateur::class);

        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $parPage = 9;
        $offset = ($page - 1) * $parPage;
        $userConnecte = $request->getAttribute('user');
        $roleConnecte = $userConnecte->getRole();

        $qb = $repository->createQueryBuilder('o');
        if ($roleConnecte !== Role::ADMIN) {
            $qb->where('o.role = :role')
                ->setParameter('role', Role::ETUDIANT->value)
                ->andWhere('o.campus = :campus')
                ->setParameter('campus', $userConnecte->getCampus());
        }

        $totalUtilisateurs = (clone $qb)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $gestion_utilisateurs = $qb
            ->select('o')
            ->orderBy('o.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($parPage)
            ->getQuery()
            ->getResult();

        $totalPages = (int)ceil($totalUtilisateurs / $parPage);

        return $view->render($response, 'gestion_utilisateurs.html.twig', [
            'gestion_utilisateurs' => $gestion_utilisateurs,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function ajoute(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $nom = trim($parsedBody['nom'] ?? '');
            $prenom = trim($parsedBody['prenom'] ?? '');
            $email = trim($parsedBody['email'] ?? '');
            $mot_de_passe = trim($parsedBody['mot_de_passe'] ?? '');
            $roleStr = trim($parsedBody['role'] ?? 'pilote');
            $role = Role::from($roleStr);
            $campus = trim($parsedBody['campus'] ?? '');

            $utilisateurs = new Utilisateur($nom, $prenom, $campus, $email, $mot_de_passe, $role);
            $this->em->persist($utilisateurs);
            $this->em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('gestion_utilisateurs');
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function modifier(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        $id = (int)$args['id'];
        $utilisateurs = $this->em->find(Utilisateur::class, $id);

        if (!$utilisateurs) {
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $nom = trim($parsedBody['nom'] ?? '');
            $prenom = trim($parsedBody['prenom'] ?? '');
            $email = trim($parsedBody['email'] ?? '');
            $campus = trim($parsedBody['campus'] ?? '');
            $mot_de_passe = trim($parsedBody['mot_de_passe'] ?? '');
            $roleStr = trim($parsedBody['role'] ?? 'pilote');
            $role = Role::from($roleStr);


            $utilisateurs->setNom($nom);
            $utilisateurs->setPrenom($prenom);
            $utilisateurs->setCampus($campus);
            $utilisateurs->setEmail($email);
            $utilisateurs->setMotDePasse($mot_de_passe);
            $utilisateurs->setRole($role);
            $this->em->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('gestion_utilisateurs');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $view->render($response, 'modifier_utilisateurs.html.twig', [
            'gestion_utilisateurs' => $utilisateurs,
        ]);
    }

    public function supprimer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int)$args['id'];
        $etudiants = $this->em->find(Utilisateur::class, $id);

        if ($etudiants) {
            $this->em->remove($etudiants);
            $this->em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('gestion_utilisateurs');
        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
