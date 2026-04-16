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

        return $this->render('pages/accueil.html.twig', [
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
        return $this->render('pages/paris.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('pages/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profil_modifier', methods: ['GET', 'POST'])]
    public function profilModifier(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(\App\Form\ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('pages/profil_modifier.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/profil/supprimer', name: 'app_profil_supprimer', methods: ['POST'])]
    public function profilSupprimer(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $transactions = $em->getRepository(\App\Entity\Transaction::class)->findBy(['user' => $user]);
        foreach ($transactions as $transaction) {
            $em->remove($transaction);
        }

        $selections = $em->getRepository(\App\Entity\Selection::class)->findBy([]);
        foreach ($selections as $selection) {
            if ($selection->getPari() && $selection->getPari()->getUser() === $user) {
                $em->remove($selection);
            }
        }

        $paris = $em->getRepository(\App\Entity\Pari::class)->findBy(['user' => $user]);
        foreach ($paris as $pari) {
            $em->remove($pari);
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/deposer', name: 'app_deposer', methods: ['GET'])]
    public function deposer(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('pages/deposer.html.twig', [
            'success' => false,
            'montant' => 0,
            'solde' => $user->getSolde(),
        ]);
    }

    #[Route('/retirer', name: 'app_retirer')]
    public function retirer(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $success = false;
        $insuffisant = false;
        $montant = 0;

        if ($request->isMethod('POST')) {
            $montant = (float) $request->request->get('montant', 0);

            if ($montant <= 0) {
                $this->addFlash('error', 'Le montant doit être supérieur à 0');
            } elseif ($montant < 10) {
                $this->addFlash('error', 'Le minimum de retrait est de 10 €');
            } elseif ($montant > $user->getSolde()) {
                $insuffisant = true;
            } else {
                $user->setSolde($user->getSolde() - $montant);

                $transaction = new \App\Entity\Transaction();
                $transaction->setType('retrait');
                $transaction->setMontant($montant);
                $transaction->setUser($user);

                $em->persist($transaction);
                $em->flush();

                $success = true;
            }
        }

        return $this->render('pages/retirer.html.twig', [
            'success' => $success,
            'insuffisant' => $insuffisant,
            'montant' => $montant,
            'solde' => $user->getSolde(),
        ]);
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
