<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/deposer/checkout', name: 'app_deposer_checkout', methods: ['POST'])]
    public function checkout(Request $request, StripeService $stripeService, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $montant = (float) $request->request->get('montant', 0);

        if ($montant <= 0) {
            $this->addFlash('error', 'le montant doit etre superieur à 0');
            return $this->redirectToRoute('app_deposer');
        }

        $successUrl = $urlGenerator->generate('app_deposer_success', ['montant' => $montant], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $urlGenerator->generate('app_deposer', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $stripeService->createCheckoutSession($montant, $successUrl . '&session_id={CHECKOUT_SESSION_ID}', $cancelUrl);

        return $this->redirect($session->url);
    }

    #[Route('/deposit/success', name: 'app_deposer_success', methods: ['GET'])]
    public function success(Request $request, StripeService $stripeService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $sessionId = $request->query->get('session_id');
        $montant = (float) $request->query->get('montant', 0);

        if ($sessionId) {
            $session = $stripeService->getSession($sessionId);
            if ($session->payment_status === 'paid') {
                $user->setSolde($user->getSolde() + $montant);

                $transaction = new Transaction();
                $transaction->setType('depot');
                $transaction->setMontant($montant);
                $transaction->setUser($user);

                $em->persist($transaction);
                $em->flush();
            }
        }

        return $this->render('pages/deposer.html.twig', [
            'success' => true,
            'montant' => $montant,
            'solde' => $user->getSolde(),
        ]);
    }
}
