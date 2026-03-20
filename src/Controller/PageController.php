<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pages/home.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/calendrier', name: 'app_calendrier')]
    public function calendrier(): Response
    {
        return $this->render('pages/calendrier.html.twig');
    }

    #[Route('/classement', name: 'app_classement')]
    public function classement(): Response
    {
        return $this->render('pages/classement.html.twig');
    }

    #[Route('/roster', name: 'app_roster')]
    public function roster(): Response
    {
        return $this->render('pages/roster.html.twig');
    }

    #[Route('/paris', name: 'app_paris')]
    public function paris(): Response
    {
        return $this->render('pages/paris.html.twig');
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        return $this->render('pages/profil.html.twig');
    }

    #[Route('/deposer', name: 'app_deposer')]
    public function deposer(): Response
    {
        return $this->render('pages/deposer.html.twig');
    }

    #[Route('/retirer', name: 'app_retirer')]
    public function retirer(): Response
    {
        return $this->render('pages/retirer.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $em): Response
    {
        $success = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $nom = strip_tags(trim($request->request->get('name', '')));
            $email = strip_tags(trim($request->request->get('email', '')));
            $sujet = strip_tags(trim($request->request->get('subject', '')));
            $message = strip_tags(trim($request->request->get('message', '')));

            if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
                $error = 'Tous les champs sont obligatoires';
            } elseif (!str_contains($email, '@')) {
                $error = 'Email invalide';
            } else {
                $contact = new \App\Entity\Contact();
                $contact->setNom($nom);
                $contact->setEmail($email);
                $contact->setSujet($sujet);
                $contact->setMessage($message);

                $em->persist($contact);
                $em->flush();

                $success = true;
            }
        }

        return $this->render('pages/contact.html.twig', [
            'success' => $success,
            'error' => $error,
        ]);
    }

}
