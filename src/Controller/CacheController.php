<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api/cache')]
class CacheController extends AbstractController
{
    #[Route('/classement', methods: ['GET'])]
    public function classement(CacheInterface $cache): JsonResponse
    {
        $donnees = $cache->get('espn_classement', function (ItemInterface $item) {
            $item->expiresAfter(300);
            $url = 'https://site.api.espn.com/apis/v2/sports/basketball/nba/standings';
            return file_get_contents($url);
        });

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/roster', methods: ['GET'])]
    public function roster(CacheInterface $cache): JsonResponse
    {
        $donnees = $cache->get('espn_roster', function (ItemInterface $item) {
            $item->expiresAfter(300);
            $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/lal/roster';
            return file_get_contents($url);
        });

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/matchs', methods: ['GET'])]
    public function matchs(CacheInterface $cache): JsonResponse
    {
        $donnees = $cache->get('espn_matchs', function (ItemInterface $item) {
            $item->expiresAfter(300);
            $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/scoreboard';
            return file_get_contents($url);
        });

        return new JsonResponse($donnees, 200, [], true);
    }

    #[Route('/calendrier', methods: ['GET'])]
    public function calendrier(CacheInterface $cache): JsonResponse
    {
        $donnees = $cache->get('espn_calendrier', function (ItemInterface $item) {
            $item->expiresAfter(300);
            $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/13/schedule';
            return file_get_contents($url);
        });

        return new JsonResponse($donnees, 200, [], true);
    }
}
