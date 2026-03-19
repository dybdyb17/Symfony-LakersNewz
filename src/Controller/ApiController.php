<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Pari;
use App\Entity\Selection;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\MatchNbaRepository;
use App\Repository\PariRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $this->sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!str_contains($email, '@')) {
            return $this->json(['error' => 'Email invalide'], 400);
        }
        if (strlen($password) < 6) {
            return $this->json(['error' => 'Le mot de passe doit contenir au moins 6 caractères'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setPseudo(isset($data['pseudo']) ? $this->sanitize($data['pseudo']) : null);
        $user->setFirstname(isset($data['firstname']) ? $this->sanitize($data['firstname']) : null);
        $user->setLastname(isset($data['lastname']) ? $this->sanitize($data['lastname']) : null);
        if (!empty($data['dateNaissance'])) {
            $data['dateNaissance'] = $this->sanitize($data['dateNaissance']);
            $dateStr = str_replace([' ', '-'], ['', '/'], $data['dateNaissance']);
            $date = \DateTime::createFromFormat('d/m/Y', $dateStr);
            if (!$date) {
                $date = \DateTime::createFromFormat('Y-m-d', $data['dateNaissance']);
            }
            if ($date) {
                $user->setDateNaissance($date);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->json($this->serializeUser($user), 201);
    }

    #[Route('/login', methods: ['POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        $token = $jwtManager->create($user);

        return $this->json(['token' => $token, ...$this->serializeUser($user)]);
    }

    #[Route('/profil/{id}', methods: ['GET'])]
    public function profil(int $id): JsonResponse
    {
        $user = $this->getUser();

        return $this->json($this->serializeUser($user));
    }

    #[Route('/profil/{id}', methods: ['PUT'])]
    public function updateProfil(
        Request $request,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (isset($data['pseudo'])) {
            $user->setPseudo($this->sanitize($data['pseudo']));
        }
        if (isset($data['firstname'])) {
            $user->setFirstname($this->sanitize($data['firstname']));
        }
        if (isset($data['lastname'])) {
            $user->setLastname($this->sanitize($data['lastname']));
        }
        if (isset($data['email'])) {
            $user->setEmail($this->sanitize($data['email']));
        }
        if (isset($data['dateNaissance'])) {
            $data['dateNaissance'] = $this->sanitize($data['dateNaissance']);
            $date = \DateTime::createFromFormat('d/m/Y', $data['dateNaissance']);
            if (!$date) {
                $date = \DateTime::createFromFormat('Y-m-d', $data['dateNaissance']);
            }
            if ($date) {
                $user->setDateNaissance($date);
            }
        }

        $em->flush();

        return $this->json($this->serializeUser($user));
    }

    #[Route('/profil/{id}', methods: ['DELETE'])]
    public function deleteProfil(
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $user = $this->getUser();

        $transactions = $em->getRepository(Transaction::class)->findBy(['user' => $user]);
        foreach ($transactions as $transaction) {
            $em->remove($transaction);
        }

        $paris = $em->getRepository(Pari::class)->findBy(['user' => $user]);
        foreach ($paris as $pari) {
            $selections = $em->getRepository(Selection::class)->findBy(['pari' => $pari]);
            foreach ($selections as $selection) {
                $em->remove($selection);
            }
            $em->remove($pari);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Compte supprimé avec succès']);
    }

    #[Route('/deposer', methods: ['POST'])]
    public function deposer(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $montant = (float) $data['montant'];

        $user->setSolde($user->getSolde() + $montant);

        $transaction = new Transaction();
        $transaction->setType('depot');
        $transaction->setMontant($montant);
        $transaction->setUser($user);

        $em->persist($transaction);
        $em->flush();

        return $this->json([
            'message' => 'Dépôt effectué',
            'solde' => $user->getSolde(),
        ]);
    }

    #[Route('/retirer', methods: ['POST'])]
    public function retirer(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $montant = (float) $data['montant'];

        if ($user->getSolde() < $montant) {
            return $this->json(['error' => 'Solde insuffisant'], 400);
        }

        $user->setSolde($user->getSolde() - $montant);

        $transaction = new Transaction();
        $transaction->setType('retrait');
        $transaction->setMontant($montant);
        $transaction->setUser($user);

        $em->persist($transaction);
        $em->flush();

        return $this->json([
            'message' => 'Retrait effectué',
            'solde' => $user->getSolde(),
        ]);
    }

    #[Route('/pari', methods: ['POST'])]
    public function placerPari(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $mise = (float) $data['mise'];

        if ($user->getSolde() < $mise) {
            return $this->json(['error' => 'Solde insuffisant pour placer ce pari'], 400);
        }

        $user->setSolde($user->getSolde() - $mise);

        $selections = $data['selections'] ?? [];
        $estCombine = !empty($selections);

        $pari = new Pari();
        $pari->setMise($mise);
        $pari->setStatut('en_cours');
        $pari->setUser($user);

        if ($estCombine) {
            $equipes = array_map(fn($s) => $this->sanitize($s['equipe']), $selections);
            $coteCombinee = array_reduce($selections, fn($carry, $s) => $carry * (float) $s['cote'], 1.0);
            $pari->setEquipe(implode(' + ', $equipes));
            $pari->setCote($coteCombinee);

            $em->persist($pari);

            foreach ($selections as $selData) {
                $selection = new Selection();
                $selection->setEquipeChoisie($selData['equipe']);
                $selection->setCote((float) $selData['cote']);
                $selection->setTypePari($selData['typePari'] ?? 'Vainqueur du match');
                $selection->setMiseIndividuelle(null);
                $pari->addSelection($selection);
                $em->persist($selection);
            }
        } else {
            $pari->setEquipe($this->sanitize($data['equipe']));
            $pari->setCote((float) $data['cote']);
            $em->persist($pari);
        }

        $em->flush();

        return $this->json([
            'message' => 'Pari placé',
            'pari' => $this->serializePari($pari),
            'solde' => $user->getSolde(),
        ], 201);
    }

    #[Route('/mes-paris', methods: ['GET'])]
    public function mesParis(PariRepository $pariRepository): JsonResponse
    {
        $user = $this->getUser();
        $paris = $pariRepository->findBy(['user' => $user]);

        return $this->json(array_map([$this, 'serializePari'], $paris));
    }

    #[Route('/articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): JsonResponse
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->json(array_map(fn($article) => [
            'id'        => $article->getId(),
            'titre'     => $article->getTitre(),
            'contenu'   => $article->getContenu(),
            'categorie' => $article->getCategorie(),
            'image'     => $article->getImage(),
            'auteur'    => $article->getAuteur(),
            'createdAt' => $article->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $article->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ], $articles));
    }

    #[Route('/contact', methods: ['POST'])]
    public function contact(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $contact = new Contact();
        $contact->setNom($this->sanitize($data['nom']));
        $contact->setEmail($this->sanitize($data['email']));
        $contact->setSujet($this->sanitize($data['sujet']));
        $contact->setMessage($this->sanitize($data['message']));

        $em->persist($contact);
        $em->flush();

        return $this->json(['message' => 'Message envoyé avec succès'], 201);
    }

    #[Route('/matchs', methods: ['GET'])]
    public function matchsAVenir(MatchNbaRepository $matchNbaRepository): JsonResponse
    {
        $matchs = $matchNbaRepository->findBy(['statut' => 'a_venir']);

        return $this->json(array_map(fn($match) => [
            'id'        => $match->getId(),
            'team1'     => $match->getTeam1(),
            'team2'     => $match->getTeam2(),
            'cote1'     => $match->getCote1(),
            'cote2'     => $match->getCote2(),
            'dateMatch' => $match->getDateMatch()?->format('Y-m-d H:i'),
            'statut'    => $match->getStatut(),
        ], $matchs));
    }

    private function sanitize(string $input): string
    {
        return strip_tags(trim($input));
    }

    private function serializeUser(User $user): array
    {
        return [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'pseudo'    => $user->getPseudo(),
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'solde'     => $user->getSolde(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function serializePari(Pari $pari): array
    {
        return [
            'id'         => $pari->getId(),
            'equipe'     => $pari->getEquipe(),
            'cote'       => $pari->getCote(),
            'mise'       => $pari->getMise(),
            'gains'      => $pari->getGains(),
            'statut'     => $pari->getStatut(),
            'createdAt'  => $pari->getCreatedAt()?->format('Y-m-d H:i:s'),
            'selections' => array_map(fn($s) => [
                'id'               => $s->getId(),
                'equipeChoisie'    => $s->getEquipeChoisie(),
                'cote'             => $s->getCote(),
                'typePari'         => $s->getTypePari(),
                'miseIndividuelle' => $s->getMiseIndividuelle(),
                'resultat'         => $s->getResultat(),
            ], $pari->getSelections()->toArray()),
        ];
    }
}
