<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('profil/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profil_modifier', methods: ['GET', 'POST'])]
    public function profilModifier(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $form = $this->createForm(\App\Form\ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/profil_modifier.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/profil/supprimer', name: 'app_profil_supprimer', methods: ['POST'])]
    public function profilSupprimer(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
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

        return $this->redirectToRoute('app_accueil');
    }
}
