<?php

namespace App\Application\Controller;

use App\Domain\Utilisateur;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class ConnexionController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // Affiche le formulaire de connexion
    public function afficher(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'page_connexion.html.twig', []);
    }

    // Traite la tentative de connexion
    public function connecter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($user === null) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'page_connexion.html.twig', ['error' => 'Email introuvable']);
        }

        if (!password_verify($password, $user->getMotDePasse())) {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'page_connexion.html.twig', ['error' => 'Mot de passe incorrect']);
        }

        $_SESSION['user_id'] = $user->getId();
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function deconnecter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        session_destroy();
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
