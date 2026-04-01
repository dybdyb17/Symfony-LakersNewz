<?php

namespace App\Controller;

use App\Entity\MatchNba;
use App\Entity\Pari;
use App\Entity\Selection;
use App\Repository\ArticleRepository;
use App\Repository\MatchNbaRepository;
use App\Repository\PariRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/pari', methods: ['POST'])]
    public function placerPari(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $mise = (float) $data['mise'];

        if ($user->getSolde() < $mise) {
            return $this->json(['error' => 'Solde insuffisant pour placer ce pari'], 400);
        }

        $user->setSolde($user->getSolde() - $mise);

        $selections = $data['selections'] ?? [];
        $estCombine = !empty($selections);

        // Enregistrer le match en base si il n'existe pas
        if (!$estCombine && isset($data['match'])) {
            $matchData = $data['match'];
            $existingMatch = $em->getRepository(MatchNba::class)->findOneBy([
                'team1' => $matchData['team1'],
                'team2' => $matchData['team2'],
                'statut' => 'a_venir',
            ]);
            if (!$existingMatch) {
                $match = new MatchNba();
                $match->setTeam1($matchData['team1']);
                $match->setTeam2($matchData['team2']);
                $match->setCote1((float) ($matchData['cote1'] ?? $data['cote']));
                $match->setCote2((float) ($matchData['cote2'] ?? $data['cote']));
                $match->setStatut('a_venir');
                $em->persist($match);
            }
        }

        // Pour les paris combinés, enregistrer chaque match
        if ($estCombine && isset($data['matchs'])) {
            foreach ($data['matchs'] as $matchData) {
                $existingMatch = $em->getRepository(MatchNba::class)->findOneBy([
                    'team1' => $matchData['team1'],
                    'team2' => $matchData['team2'],
                    'statut' => 'a_venir',
                ]);
                if (!$existingMatch) {
                    $match = new MatchNba();
                    $match->setTeam1($matchData['team1']);
                    $match->setTeam2($matchData['team2']);
                    $match->setCote1((float) ($matchData['cote1'] ?? 1.50));
                    $match->setCote2((float) ($matchData['cote2'] ?? 1.50));
                    $match->setStatut('a_venir');
                    $em->persist($match);
                }
            }
        }

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
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }
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
