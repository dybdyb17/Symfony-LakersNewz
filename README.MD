# Lakers Newz

Plateforme web d'actualités et de paris sportifs virtuels autour des Los Angeles Lakers (NBA). Projet réalisé dans le cadre du titre professionnel Développeur Web et Web Mobile (DWWM).

Le site propose deux modules : un module d'actualités (articles, classement NBA, calendrier, effectif) et un module de paris virtuels en monnaie fictive (paris simples et combinés, gestion d'un solde, historique).

## Stack technique

- **Symfony 7.4** (PHP 8.2+)
- **Doctrine ORM** + migrations
- **MySQL**
- **Twig** pour les templates
- **Redis** pour le cache des appels à l'API ESPN
- **Stripe** (mode test) pour le dépôt de monnaie virtuelle
- **JavaScript vanilla** et **CSS vanilla** (sans framework)

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL
- Node.js et npm
- Redis
- Un compte Stripe (clés de test) pour la partie paiement

## Installation

1. **Cloner le dépôt**

```bash
git clone git@github.com:dybdyb17/Symfony-LakersNewz.git
cd Symfony-LakersNewz
```

2. **Installer les dépendances PHP et JavaScript**

```bash
composer install
npm install
```

3. **Configurer les variables d'environnement**

Créer un fichier `.env.local` à la racine du projet et y renseigner la connexion à la base de données ainsi que les clés Stripe :

```dotenv
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/lakersnewz_db?serverVersion=8.0&charset=utf8mb4"
STRIPE_PUBLIC_KEY=pk_test_votre_cle
STRIPE_SECRET_KEY=sk_test_votre_cle
```

> Les secrets doivent rester dans `.env.local`, qui est ignoré par Git. Ne jamais versionner ses clés.

4. **Créer la base de données et appliquer les migrations**

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger les données de test (fixtures)**

```bash
php bin/console doctrine:fixtures:load
```

> Attention : cette commande vide la base avant de la remplir.

6. **Démarrer Redis**

```bash
redis-server --daemonize yes
```

7. **Lancer le serveur de développement**

```bash
php -S 127.0.0.1:8000 -t public
```

Le site est accessible sur [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Comptes de test

Une fois les fixtures chargées, deux comptes sont disponibles :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@lakersnewz.com | admin123 |
| Utilisateur | user@lakersnewz.com | user123 |

Le compte utilisateur démarre avec un solde de 100 € de monnaie virtuelle.

## Tests

Les tests unitaires (logique de calcul des paris) se lancent avec :

```bash
php bin/phpunit
```

## Fonctionnalités principales

**Côté utilisateur**
- Consultation des articles, du classement NBA, du calendrier et de l'effectif
- Création de compte, connexion, gestion du profil
- Dépôt et retrait de monnaie virtuelle
- Placement de paris simples et combinés
- Historique des paris (en cours, gagnés, perdus)

**Côté administrateur**
- Gestion des articles (création, modification, suppression)
- Création des matchs et saisie des résultats
- Résolution automatique des paris à la saisie d'un résultat

## Architecture

Le classement, le calendrier et l'effectif sont récupérés depuis l'API publique d'ESPN, avec mise en cache Redis. Les matchs sur lesquels on peut parier proviennent de la base de données et sont créés depuis l'interface d'administration, ce qui garantit un module de paris fonctionnel indépendamment du calendrier réel de la NBA.

## Auteur

Dybril Boudiaf — M2i Marseille — 2026
