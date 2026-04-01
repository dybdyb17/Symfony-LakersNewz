<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use App\Repository\PariRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/contacts', methods: ['GET'])]
    public function contacts(ContactRepository $contactRepository): JsonResponse
    {
        $contacts = $contactRepository->findAll();

        return $this->json(array_map(fn($contact) => [
            'id'        => $contact->getId(),
            'nom'       => $contact->getNom(),
            'email'     => $contact->getEmail(),
            'sujet'     => $contact->getSujet(),
            'message'   => $contact->getMessage(),
            'createdAt' => $contact->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $contacts));
    }

    #[Route('/users', methods: ['GET'])]
    public function users(\App\Repository\UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        return $this->json(array_map(fn($user) => [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'pseudo'    => $user->getPseudo(),
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'solde'     => $user->getSolde(),
            'roles'     => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $users));
    }

    #[Route('/transactions', methods: ['GET'])]
    public function transactions(TransactionRepository $transactionRepository): JsonResponse
    {
        $transactions = $transactionRepository->findAll();

        return $this->json(array_map(fn($transaction) => [
            'id'        => $transaction->getId(),
            'type'      => $transaction->getType(),
            'montant'   => $transaction->getMontant(),
            'createdAt' => $transaction->getCreatedAt()?->format('Y-m-d H:i:s'),
            'user'      => [
                'pseudo' => $transaction->getUser()?->getPseudo(),
                'email'  => $transaction->getUser()?->getEmail(),
            ],
        ], $transactions));
    }

    #[Route('/paris', methods: ['GET'])]
    public function paris(PariRepository $pariRepository): JsonResponse
    {
        $paris = $pariRepository->findAll();

        return $this->json(array_map(fn($pari) => [
            'id'        => $pari->getId(),
            'equipe'    => $pari->getEquipe(),
            'cote'      => $pari->getCote(),
            'mise'      => $pari->getMise(),
            'gains'     => $pari->getGains(),
            'statut'    => $pari->getStatut(),
            'createdAt' => $pari->getCreatedAt()?->format('Y-m-d H:i:s'),
            'user'      => [
                'pseudo' => $pari->getUser()?->getPseudo(),
                'email'  => $pari->getUser()?->getEmail(),
            ],
        ], $paris));
    }
}
