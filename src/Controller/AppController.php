<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppController extends AbstractController
{
    #[Route('/classement', name: 'app_classement', methods: ['GET'])]
    public function classement(HttpClientInterface $client, CacheInterface $cache, Request $request): Response
    {
        $url = 'https://site.api.espn.com/apis/v2/sports/basketball/nba/standings';
        $classement = $cache->get('espn_classement', function (ItemInterface $item) use ($client, $url) {
            $item->expiresAfter(300);
            $response = $client->request('GET', $url);
            return $response->toArray();
        });

        $conferences = [];
        foreach ($classement['children'] as $conference) {
            $equipes = [];
            foreach ($conference['standings']['entries'] as $entry) {
                $stats = [];
                foreach ($entry['stats'] as $stat) {
                    $stats[$stat['name']] = $stat['displayValue'] ?? $stat['summary'] ?? '';
                }
                $equipes[] = [
                    'nom' => $entry['team']['displayName'],
                    'abreviation' => $entry['team']['abbreviation'],
                    'logo' => $entry['team']['logos'][0]['href'] ?? '',
                    'victoires' => $stats['wins'] ?? '0',
                    'defaites' => $stats['losses'] ?? '0',
                    'pourcentage' => $stats['winPercent'] ?? '.000',
                    'gamesBehind' => $stats['gamesBehind'] ?? '-',
                    'domicile' => $stats['Home'] ?? '-',
                    'exterieur' => $stats['Road'] ?? '-',
                    'ppg' => $stats['avgPointsFor'] ?? '0',
                    'oppPpg' => $stats['avgPointsAgainst'] ?? '0',
                    'diff' => $stats['differential'] ?? '0',
                    'serie' => $stats['streak'] ?? '-',
                    'dix_derniers' => $stats['Last Ten Games'] ?? '-',
                ];
            }
            $conferences[] = [
                'nom' => $conference['name'] ?? 'Conference',
                'equipes' => $equipes,
            ];
        }

        $filtre = $request->query->get('conf', 'all');

        if ($filtre === 'all') {
            $toutesEquipes = [];
            foreach ($conferences as $conf) {
                foreach ($conf['equipes'] as $equipe) {
                    $toutesEquipes[] = $equipe;
                }
            }
            usort($toutesEquipes, function ($a, $b) {
                return (int)$b['victoires'] - (int)$a['victoires'];
            });
            $conferences = [['nom' => 'Classement général', 'equipes' => $toutesEquipes]];
        } elseif ($filtre === 'west') {
            $confOuest = $conferences[1] ?? $conferences[0];
            usort($confOuest['equipes'], function ($a, $b) {
                return (int)$b['victoires'] - (int)$a['victoires'];
            });
            $conferences = [$confOuest];
        } elseif ($filtre === 'east') {
            $confEst = $conferences[0];
            usort($confEst['equipes'], function ($a, $b) {
                return (int)$b['victoires'] - (int)$a['victoires'];
            });
            $conferences = [$confEst];
        }

        return $this->render('nba/classement.html.twig', [
            'conferences' => $conferences,
            'filtre' => $filtre,
        ]);
    }

    #[Route('/roster', name: 'app_roster', methods: ['GET'])]
    public function roster(HttpClientInterface $client, CacheInterface $cache): Response
    {
        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/lal/roster';
        $roster = $cache->get('espn_roster', function (ItemInterface $item) use ($client, $url) {
            $item->expiresAfter(300);
            $response = $client->request('GET', $url);
            return $response->toArray();
        });

        return $this->render('nba/roster.html.twig', [
            'roster' => $roster,
        ]);
    }

    #[Route('/calendrier', name: 'app_calendrier', methods: ['GET'])]
    public function calendrier(HttpClientInterface $client, CacheInterface $cache): Response
    {
        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/13/schedule';
        $calendrier = $cache->get('espn_calendrier', function (ItemInterface $item) use ($client, $url) {
            $item->expiresAfter(300);
            $response = $client->request('GET', $url);
            return $response->toArray();
        });

        return $this->render('nba/calendrier.html.twig', [
            'calendrier' => $calendrier,
        ]);
    }

    #[Route('/matchs-nba', name: 'app_matchs_nba', methods: ['GET'])]
    public function matchsNba(HttpClientInterface $client, CacheInterface $cache): Response
    {
        $url = 'https://site.api.espn.com/apis/site/v2/sports/basketball/nba/scoreboard';
        $matchs = $cache->get('espn_matchs', function (ItemInterface $item) use ($client, $url) {
            $item->expiresAfter(300);
            $response = $client->request('GET', $url);
            return $response->toArray();
        });

        return $this->render('nba/matchs.html.twig', [
            'matchs' => $matchs,
        ]);
    }
}
