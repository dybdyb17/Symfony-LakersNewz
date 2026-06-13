<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use App\Entity\MatchNba;
use App\Entity\Pari;
use App\Entity\Selection;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $admin = new User();
        $admin->setEmail('admin@lakersnewz.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setFirstname('Admin');
        $admin->setLastname('Lakers');
        $admin->setPseudo('AdminLN');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSolde(0);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@lakersnewz.com');
        $user->setPassword($this->hasher->hashPassword($user, 'user123'));
        $user->setFirstname('Dybril');
        $user->setLastname('Boudiaf');
        $user->setPseudo('DybLakers');
        $user->setSolde(247.50);
        $manager->persist($user);

        $transactionsData = [
            ['type' => 'depot', 'montant' => 50.00, 'jours' => 90],
            ['type' => 'depot', 'montant' => 100.00, 'jours' => 75],
            ['type' => 'retrait', 'montant' => 30.00, 'jours' => 60],
            ['type' => 'depot', 'montant' => 75.00, 'jours' => 45],
            ['type' => 'depot', 'montant' => 50.00, 'jours' => 30],
            ['type' => 'retrait', 'montant' => 20.00, 'jours' => 15],
        ];

        foreach ($transactionsData as $td) {
            $transaction = new Transaction();
            $transaction->setType($td['type']);
            $transaction->setMontant($td['montant']);
            $transaction->setUser($user);
            $date = new \DateTime();
            $date->modify('-' . $td['jours'] . ' days');
            $transaction->setCreatedAt($date);
            $manager->persist($transaction);
        }

        $matchsTermines = [
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Golden State Warriors', 'cote1' => 1.85, 'cote2' => 2.10, 'score1' => 118, 'score2' => 105, 'jours' => 120],
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Boston Celtics', 'cote1' => 2.30, 'cote2' => 1.65, 'score1' => 102, 'score2' => 110, 'jours' => 100],
            ['team1' => 'Denver Nuggets', 'team2' => 'Los Angeles Lakers', 'cote1' => 1.75, 'cote2' => 2.20, 'score1' => 98, 'score2' => 112, 'jours' => 85],
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Phoenix Suns', 'cote1' => 1.90, 'cote2' => 1.95, 'score1' => 125, 'score2' => 119, 'jours' => 70],
            ['team1' => 'Milwaukee Bucks', 'team2' => 'Los Angeles Lakers', 'cote1' => 1.80, 'cote2' => 2.05, 'score1' => 108, 'score2' => 99, 'jours' => 55],
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Dallas Mavericks', 'cote1' => 1.70, 'cote2' => 2.25, 'score1' => 130, 'score2' => 121, 'jours' => 40],
        ];

        $matchEntities = [];
        foreach ($matchsTermines as $m) {
            $match = new MatchNba();
            $match->setTeam1($m['team1']);
            $match->setTeam2($m['team2']);
            $match->setCote1($m['cote1']);
            $match->setCote2($m['cote2']);
            $match->setScoreTeam1($m['score1']);
            $match->setScoreTeam2($m['score2']);
            $match->setStatut('termine');
            $match->setResultat($m['score1'] > $m['score2'] ? $m['team1'] : $m['team2']);
            $date = new \DateTime();
            $date->modify('-' . $m['jours'] . ' days');
            $match->setDateMatch($date);
            $manager->persist($match);
            $matchEntities[] = $match;
        }

        $matchsFuturs = [
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Oklahoma City Thunder', 'cote1' => 2.40, 'cote2' => 1.60, 'jours' => -2],
            ['team1' => 'Los Angeles Lakers', 'team2' => 'Cleveland Cavaliers', 'cote1' => 1.95, 'cote2' => 1.90, 'jours' => -5],
            ['team1' => 'New York Knicks', 'team2' => 'Los Angeles Lakers', 'cote1' => 1.85, 'cote2' => 2.00, 'jours' => -8],
        ];

        foreach ($matchsFuturs as $m) {
            $match = new MatchNba();
            $match->setTeam1($m['team1']);
            $match->setTeam2($m['team2']);
            $match->setCote1($m['cote1']);
            $match->setCote2($m['cote2']);
            $match->setStatut('a_venir');
            $date = new \DateTime();
            $date->modify($m['jours'] . ' days');
            $match->setDateMatch($date);
            $manager->persist($match);
        }

        $parisData = [
            ['equipe' => 'Los Angeles Lakers', 'cote' => 1.85, 'mise' => 20.00, 'statut' => 'gagne', 'gains' => 37.00, 'jours' => 120],
            ['equipe' => 'Los Angeles Lakers', 'cote' => 2.30, 'mise' => 15.00, 'statut' => 'perdu', 'gains' => 0, 'jours' => 100],
            ['equipe' => 'Los Angeles Lakers', 'cote' => 2.20, 'mise' => 25.00, 'statut' => 'gagne', 'gains' => 55.00, 'jours' => 85],
            ['equipe' => 'Phoenix Suns', 'cote' => 1.95, 'mise' => 10.00, 'statut' => 'perdu', 'gains' => 0, 'jours' => 70],
            ['equipe' => 'Milwaukee Bucks', 'cote' => 1.80, 'mise' => 30.00, 'statut' => 'gagne', 'gains' => 54.00, 'jours' => 55],
            ['equipe' => 'Los Angeles Lakers', 'cote' => 1.70, 'mise' => 20.00, 'statut' => 'gagne', 'gains' => 34.00, 'jours' => 40],
        ];

        foreach ($parisData as $pd) {
            $pari = new Pari();
            $pari->setEquipe($pd['equipe']);
            $pari->setCote($pd['cote']);
            $pari->setMise($pd['mise']);
            $pari->setGains($pd['gains']);
            $pari->setStatut($pd['statut']);
            $pari->setUser($user);
            $date = new \DateTime();
            $date->modify('-' . $pd['jours'] . ' days');
            $pari->setCreatedAt($date);

            $selection = new Selection();
            $selection->setEquipeChoisie($pd['equipe']);
            $selection->setCote($pd['cote']);
            $selection->setTypePari('simple');
            $selection->setMiseIndividuelle($pd['mise']);
            $selection->setResultat($pd['statut'] === 'gagne' ? 'gagne' : 'perdu');
            $selection->setPari($pari);
            $manager->persist($selection);

            $manager->persist($pari);
        }

        $images = [
            'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800',
            'https://images.unsplash.com/photo-1504450758481-7338bbe75c8e?w=800',
            'https://images.unsplash.com/photo-1515523110800-9415d13b84a8?w=800',
            'https://images.unsplash.com/photo-1574623452334-1e0ac2b3ccb4?w=800',
            'https://images.unsplash.com/photo-1608245449230-4ac19066d2d0?w=800',
            'https://images.unsplash.com/photo-1519861531473-9200262188bf?w=800',
            'https://images.unsplash.com/photo-1628779238951-be2c9f2a59f4?w=800',
            'https://images.unsplash.com/photo-1559692048-79a3f837883d?w=800',
            'https://images.unsplash.com/photo-1518063319789-7217e6706b04?w=800',
            'https://images.unsplash.com/photo-1577471488278-16eec37ffcc2?w=800',
        ];

        $categories = ['news', 'rumeurs', 'interviews', 'analyses'];

        $titres = [
            "LeBron James depasse le record historique de points en NBA",
            "Les Lakers signent un nouveau contrat avec un joueur star",
            "Analyse tactique : comment les Lakers dominent la conference Ouest",
            "Interview exclusive avec le coach des Lakers sur la saison",
            "Rumeurs de transfert : un All-Star pourrait rejoindre les Lakers",
            "Les Lakers remportent une victoire decisive contre les Celtics",
            "Retour sur le parcours des Lakers en playoffs cette saison",
            "La strategie defensive des Lakers expliquee en detail",
            "LeBron James parle de sa retraite et de son avenir",
            "Les jeunes talents des Lakers qui vont exploser cette saison",
            "Analyse du roster des Lakers pour la saison prochaine",
            "Le Crypto.com Arena bat un record d'affluence",
            "Les Lakers et leur nouvelle approche du jeu a trois points",
            "Interview avec Austin Reaves sur son role dans l'equipe",
            "Rumeurs : les Lakers visent un trade majeur avant la deadline",
            "Comment les Lakers preparent leurs matchs de playoffs",
            "Le parcours inspirant de Bronny James avec les Lakers",
            "Analyse statistique : les Lakers en chiffres cette saison",
            "Les Lakers celebrent leur victoire en conference Ouest",
            "Retour sur les meilleurs moments de la saison des Lakers",
        ];

        for ($i = 0; $i < 20; $i++) {
            $article = new Article();
            $article->setTitre($titres[$i]);
            $article->setCategorie($categories[$i % 4]);
            $article->setImage($images[$i % 10]);
            $article->setAuteur('Lakers Newz');

            $contenu = "";
            for ($p = 0; $p < 8; $p++) {
                $contenu .= $faker->paragraph(8) . "\n\n";
            }
            $article->setContenu($contenu);

            $date = new \DateTime();
            $date->modify('-' . ($i * 5) . ' days');
            $article->setCreatedAt($date);

            $manager->persist($article);
        }

        $manager->flush();
    }
}
