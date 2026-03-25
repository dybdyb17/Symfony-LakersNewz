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
    public function deposer(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $success = false;
        $montant = 0;

        if ($request->isMethod('POST')) {
            $montant = (float) $request->request->get('montant', 0);

            if ($montant <= 0) {
                $this->addFlash('error', 'Le montant doit être supérieur à 0');
            } else {
                $user->setSolde($user->getSolde() + $montant);

                $transaction = new \App\Entity\Transaction();
                $transaction->setType('depot');
                $transaction->setMontant($montant);
                $transaction->setUser($user);

                $em->persist($transaction);
                $em->flush();

                $success = true;
            }
        }

        return $this->render('pages/deposer.html.twig', [
            'success' => $success,
            'montant' => $montant,
            'solde' => $user->getSolde(),
        ]);
    }

    #[Route('/retirer', name: 'app_retirer')]
    public function retirer(): Response
    {
        return $this->render('pages/retirer.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $em): Response
    {
        $contact = new \App\Entity\Contact();
        $form = $this->createForm(\App\Form\ContactFormType::class, $contact);
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();

            $success = true;
        }

        return $this->render('pages/contact.html.twig', [
            'contactForm' => $form,
            'success' => $success,
        ]);
    }

}
