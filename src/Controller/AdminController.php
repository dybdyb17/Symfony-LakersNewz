<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\MatchNba;
use App\Entity\Pari;
use App\Repository\ArticleRepository;
use App\Repository\ContactRepository;
use App\Repository\MatchNbaRepository;
use App\Repository\PariRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/matchs', methods: ['POST'])]
    public function createMatch(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $match = new MatchNba();
        $match->setTeam1($data['team1']);
        $match->setTeam2($data['team2']);
        $match->setCote1((float) $data['cote1']);
        $match->setCote2((float) $data['cote2']);
        $match->setDateMatch(\DateTime::createFromFormat('Y-m-d H:i', $data['dateMatch']) ?: null);

        $em->persist($match);
        $em->flush();

        return $this->json($this->serializeMatch($match), 201);
    }

    #[Route('/matchs/{id}', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function updateMatch(Request $request, MatchNbaRepository $matchNbaRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $match = $matchNbaRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['cote1'])) {
            $match->setCote1((float) $data['cote1']);
        }
        if (isset($data['cote2'])) {
            $match->setCote2((float) $data['cote2']);
        }
        if (isset($data['dateMatch'])) {
            $match->setDateMatch(\DateTime::createFromFormat('Y-m-d H:i', $data['dateMatch']) ?: null);
        }
        if (isset($data['statut'])) {
            $match->setStatut($data['statut']);
        }

        $em->flush();

        return $this->json($this->serializeMatch($match));
    }

    #[Route('/matchs/{id}/resultat', methods: ['PUT'])]
    public function updateResultat(Request $request, MatchNbaRepository $matchNbaRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $match = $matchNbaRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $match->setScoreTeam1((int) $data['scoreTeam1']);
        $match->setScoreTeam2((int) $data['scoreTeam2']);
        $match->setResultat($data['resultat']);
        $match->setStatut('termine');

        $em->flush();

        $pariRepository = $em->getRepository(Pari::class);
        $paris = $pariRepository->createQueryBuilder('p')
            ->where('p.statut = :statut')
            ->andWhere('p.equipe = :team1 OR p.equipe = :team2')
            ->setParameter('statut', 'en_cours')
            ->setParameter('team1', $match->getTeam1())
            ->setParameter('team2', $match->getTeam2())
            ->getQuery()
            ->getResult();

        $nbParisMisAJour = 0;
        foreach ($paris as $pari) {
            if ($pari->getEquipe() === $match->getResultat()) {
                $gains = $pari->getMise() * $pari->getCote();
                $pari->setStatut('gagne');
                $pari->setGains($gains);
                $user = $pari->getUser();
                $user->setSolde($user->getSolde() + $gains);
            } else {
                $pari->setStatut('perdu');
                $pari->setGains(0);
            }
            $nbParisMisAJour++;
        }

        $em->flush();

        return $this->json([
            'match'           => $this->serializeMatch($match),
            'parisMisAJour'   => $nbParisMisAJour,
        ]);
    }

    #[Route('/matchs', methods: ['GET'])]
    public function listMatchs(MatchNbaRepository $matchNbaRepository): JsonResponse
    {
        $matchs = $matchNbaRepository->findAll();

        return $this->json(array_map([$this, 'serializeMatch'], $matchs));
    }

    #[Route('/articles', methods: ['GET'])]
    public function listArticles(ArticleRepository $articleRepository): JsonResponse
    {
        $articles = $articleRepository->findAll();

        return $this->json(array_map([$this, 'serializeArticle'], $articles));
    }

    #[Route('/articles', methods: ['POST'])]
    public function createArticle(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setTitre($data['titre']);
        $article->setContenu($data['contenu']);
        if (isset($data['categorie'])) {
            $article->setCategorie($data['categorie']);
        }
        if (isset($data['image'])) {
            $article->setImage($data['image']);
        }
        if (isset($data['auteur'])) {
            $article->setAuteur($data['auteur']);
        }

        $em->persist($article);
        $em->flush();

        return $this->json($this->serializeArticle($article), 201);
    }

    #[Route('/articles/{id}', methods: ['PUT'])]
    public function updateArticle(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $article = $articleRepository->find($id);
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['titre'])) {
            $article->setTitre($data['titre']);
        }
        if (isset($data['contenu'])) {
            $article->setContenu($data['contenu']);
        }
        if (isset($data['categorie'])) {
            $article->setCategorie($data['categorie']);
        }
        if (isset($data['image'])) {
            $article->setImage($data['image']);
        }
        if (isset($data['auteur'])) {
            $article->setAuteur($data['auteur']);
        }

        $article->setUpdatedAt(new \DateTime());

        $em->flush();

        return $this->json($this->serializeArticle($article));
    }

    #[Route('/articles/{id}', methods: ['DELETE'])]
    public function deleteArticle(ArticleRepository $articleRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $article = $articleRepository->find($id);
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        $em->remove($article);
        $em->flush();

        return $this->json(['message' => 'Article supprimé avec succès']);
    }

    private function serializeArticle(Article $article): array
    {
        return [
            'id'         => $article->getId(),
            'titre'      => $article->getTitre(),
            'contenu'    => $article->getContenu(),
            'categorie'  => $article->getCategorie(),
            'image'      => $article->getImage(),
            'auteur'     => $article->getAuteur(),
            'createdAt'  => $article->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt'  => $article->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function serializeMatch(MatchNba $match): array
    {
        return [
            'id'         => $match->getId(),
            'team1'      => $match->getTeam1(),
            'team2'      => $match->getTeam2(),
            'cote1'      => $match->getCote1(),
            'cote2'      => $match->getCote2(),
            'dateMatch'  => $match->getDateMatch()?->format('Y-m-d H:i'),
            'statut'     => $match->getStatut(),
            'scoreTeam1' => $match->getScoreTeam1(),
            'scoreTeam2' => $match->getScoreTeam2(),
            'resultat'   => $match->getResultat(),
            'createdAt'  => $match->getCreatedAt()?->format('Y-m-d H:i'),
        ];
    }
}
