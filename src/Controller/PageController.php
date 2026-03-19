<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/inscription', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('pages/inscription.html.twig');
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
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

}
