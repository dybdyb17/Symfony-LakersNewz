<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
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
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@lakersnewz.com');
        $user->setPassword($this->hasher->hashPassword($user, 'user123'));
        $user->setFirstname('Dybril');
        $user->setLastname('Test');
        $user->setPseudo('DybTest');
        $user->setSolde(100.00);
        $manager->persist($user);

        // Images Lakers pour les articles
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
