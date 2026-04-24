<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PariController extends AbstractController
{
    #[Route('/paris', name: 'app_paris')]
    public function paris(): Response
    {
        return $this->render('paris/paris.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/deposer', name: 'app_deposer', methods: ['GET'])]
    public function deposer(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('paris/deposer.html.twig', [
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
            return $this->redirectToRoute('app_connexion');
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

        return $this->render('paris/retirer.html.twig', [
            'success' => $success,
            'insuffisant' => $insuffisant,
            'montant' => $montant,
            'solde' => $user->getSolde(),
        ]);
    }
}
