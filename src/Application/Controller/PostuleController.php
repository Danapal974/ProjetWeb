<?php

namespace App\Application\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\ORM\EntityManager;
use App\Domain\Candidature;
use App\Domain\Offres;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class PostuleController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // Affiche le formulaire de candidature
    public function afficher2(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        // On récupère l'ID de l'offre 
        $idOffre = $args['id'] ?? null; //operateur coalescence
        $user = $request->getAttribute('user');
        $offre = $this->em->find(Offres::class, (int)$idOffre);

        // Vérifie si l'utilisateur a déjà postulé à cette offre
        $dejaPostule = $this->em->getRepository(Candidature::class)->findOneBy([
            'utilisateur' => $user,
            'nom' => $offre->getNom()
        ]);

        if ($dejaPostule) {
            return $response->withHeader('Location', '/candidature')->withStatus(302);
        }

        return $view->render($response, 'Formulaire_postule.html.twig', [
            'offre_id' => $idOffre
        ]);
    }

    // Traite le formulaire et enregistre la candidature
    public function traiter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $idOffre = (int)$args['id'];
        $user = $request->getAttribute('user');

        // Nettoyage des données protection contre caracteres html
        $prenom      = htmlspecialchars($data['prenom']      ?? '', ENT_QUOTES, 'UTF-8');
        $nom         = htmlspecialchars($data['nom']         ?? '', ENT_QUOTES, 'UTF-8');
        $email       = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $description = htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $telephone   = htmlspecialchars($data['telephone']   ?? '', ENT_QUOTES, 'UTF-8');

        $error   = null;
        $success = null;

        //validations donnes

        if (preg_match("/[^A-Za-zÀ-ÿ\s-]/", $prenom)) {
            $error = "Votre prénom ne doit contenir que des lettres.";
        } elseif (preg_match("/[^A-Za-zÀ-ÿ\s-]/", $nom)) {
            $error = "Votre nom ne doit contenir que des lettres.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email '$email' n'est pas valide.";
        } elseif (!preg_match("/^[0-9]{10}$/", $telephone)) {
            $error = "Votre numéro doit contenir exactement 10 chiffres et ne contenir que des chiffres.";
        }

        //si pas erreur
        if ($error === null) {
            if (isset($uploadedFiles['fichier']) && $uploadedFiles['fichier']->getError() === UPLOAD_ERR_OK) {
                $fichier  = $uploadedFiles['fichier'];
                $mimeType = $fichier->getClientMediaType();

                if ($mimeType !== 'application/pdf') {
                    $error = "Type de fichier non autorisé (PDF uniquement).";
                } elseif ($fichier->getSize() > 2 * 1024 * 1024) {
                    $error = "Fichier trop volumineux (max 2MB).";
                } else {
                    $uploadDir = __DIR__ . '/../../../public/uploads/';

                    // donne un nom aux fichier pour eviter ecrasement (envoie de deux memes fichiers par exemple)
                    $filename = uniqid() . "_" . basename($fichier->getClientFilename());
                    $fichier->moveTo($uploadDir . $filename);

                    //prends les info dans la bdd
                    $offre = $this->em->find(Offres::class, $idOffre);

                    // verif si offre et user existe pour mettre offre dans ma candidature
                    if ($offre && $user) {
                        $candidature = new Candidature(
                            $offre->getNom(),
                            $offre->getDomaine(),
                            $offre->getLieu(),
                            $offre->getEmail(),
                            $offre->getDescription(),
                            $offre->getDuree(),
                            $offre->getNiveau(),
                            $offre->getSalaire(),
                            'attente',
                            $user
                        );


                        $this->em->persist($candidature);
                        $this->em->flush();

                        // Redirection vers la liste des candidatures après succès
                        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                        return $response->withHeader('Location', $routeParser->urlFor('candidature'))->withStatus(302);
                    } else {
                        $error = "Offre introuvable en base de données.";
                    }
                }
            } else {
                $error = "Veuillez joindre votre CV (PDF).";
            }
        }

        // si erreur remet formulaire
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Formulaire_postule.html.twig', [
            'error'       => $error,
            'prenom'      => $prenom,
            'nom'         => $nom,
            'email'       => $email,
            'description' => $description,
            'telephone'   => $telephone,
            'id'          => $idOffre
        ]);
    }
}
