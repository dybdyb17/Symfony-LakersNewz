<?php

namespace App\Controller;

use App\Service\RedisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cache')]
class CacheController extends AbstractController
{
    #[Route('/classement', methods: ['GET'])]
    public function classement(RedisService $redisService): JsonResponse
    {
        $cle = 'espn_classement';
        $cache = $redisService->get($cle);

        if ($cache !== null) {
            return new JsonResponse($cache, 200, [], true);
        }

        $url = 'https://site.api.espn.com/apis/v2/sports/basketball/nba/standings';
        $donnees = file_get_contents($url);

        if ($donnees === false) {
            return $this->json(['erreur' => 'Impossible de contacter l api d ESPN'], 500);
        }

        $redisService->set($cle, $donnees, 300);

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/roster', methods: ['GET'])]
    public function roster(RedisService $redisService): JsonResponse
    {
        $cle = 'espn_roster';
        $cache = $redisService->get($cle);

        if ($cache !== null) {
            return new JsonResponse($cache, 200, [], true);
        }

        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/lal/roster';
        $donnees = file_get_contents($url);

        if ($donnees === false) {
            return $this->json(['erreur' => 'Impossible de contacter l api d ESPN'], 500);
        }

        $redisService->set($cle, $donnees, 300);

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/matchs', methods: ['GET'])]
    public function matchs(RedisService $redisService): JsonResponse
    {
        $cle = 'espn_matchs';
        $cache = $redisService->get($cle);

        if ($cache !== null) {
            return new JsonResponse($cache, 200, [], true);
        }

        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/scoreboard';
        $donnees = file_get_contents($url);

        if ($donnees === false) {
            return $this->json(['erreur' => 'Impossible de contacter l api d ESPN'], 500);
        }

        $redisService->set($cle, $donnees, 300);

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/calendrier', methods: ['GET'])]
    public function calendrier(RedisService $redisService): JsonResponse
    {
        $cle = 'espn_calendrier';
        $cache = $redisService->get($cle);

        if ($cache !== null) {
            return new JsonResponse($cache, 200, [], true);
        }

        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/13/schedule';
        $donnees = file_get_contents($url);

        if ($donnees === false) {
            return $this->json(['erreur' => 'Impossible de contacter ESPN'], 500);
        }

        $redisService->set($cle, $donnees, 300);

        return new JsonResponse($donnees, 200, [], true);
    }
}
