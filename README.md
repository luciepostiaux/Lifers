# Lifers

<p align="center">
  <img src="public/images/landing/hero-lifers.webp" alt="Lifers, jeu de simulation de vie communautaire" width="1100">
</p>

**Lifers** est un jeu web de simulation de vie communautaire dans lequel chaque personne crée un personnage, prend soin de ses besoins, construit son parcours et interagit avec les autres habitants du jeu.

Le projet est né en 2024 dans le cadre de mon travail de fin d’études. Il fait aujourd’hui l’objet d’une refonte complète : architecture modernisée, règles métier fiabilisées, nouvelle direction artistique, sécurité renforcée et expérience adaptée aux écrans actuels.

> Le dépôt présente une version de démonstration destinée à mon portfolio. Lifers reste un projet en évolution et n’est pas encore une version commerciale définitive.

## Expérience proposée

Un compte possède un seul Lifer actif à la fois. Sa vie lui appartient : sa progression, son argent, sa santé, ses relations et ses conversations sont rattachés à ce personnage. Lorsqu’il meurt, son identité rejoint l’histoire du monde et le compte peut commencer une nouvelle vie.

### Vie quotidienne

- sept jauges à surveiller : faim, soif, hygiène, bonheur, divertissement, condition physique et santé ;
- vieillissement, maladies, soins, négligence et mortalité naturelle ;
- inventaire, achats et consommation d’objets au LifeMarket ;
- activités sportives et loisirs avec coûts et effets sur les besoins ;
- journal quotidien et nécrologie des Lifers disparus.

### Études et carrière

- catalogue d’études avec durée, coût, prérequis et diplôme obtenu ;
- métiers conditionnés par les diplômes acquis ;
- salaire quotidien et progression liée à l’ancienneté ;
- parcours et emploi courant conservés dans l’histoire du Lifer.

### Famille et générations

- demandes de mariage, divorce et relations consenties ;
- grossesses, naissances simples ou multiples et choix du prénom ;
- besoins des enfants, garde partagée, abandon et adoption ;
- orphelinat et départ du foyer à 18 ans ;
- réincarnation possible dans un Lifer issu d’une famille existante.

### Communauté

- salon général, conversations privées et groupes personnalisés ;
- messages instantanés avec Pusher Channels ;
- profils publics personnalisables avec texte enrichi et images ;
- commentaires soumis à l’approbation du propriétaire du profil ;
- demandes d’amitié et identité publique limitée au nom du Lifer ;
- confidentialité des conversations privées imposée côté serveur.

### Administration et modération

- rôles séparés pour l’administration, la modération et les utilisateurs ;
- gestion détaillée d’un Lifer : argent, jauges, maladies, diplômes et décès ;
- bannissement d’un compte et protection du compte administrateur principal ;
- modération des profils, commentaires et messages du salon général ;
- historique des actions sensibles avec motif et contenu public retiré ;
- salon privé réservé à l’équipe de modération.

## Direction artistique

La refonte utilise une palette crème, prune, vert sauge et or. L’interface privilégie les compositions aérées, les cartes légèrement surélevées et une navigation latérale rétractable afin de conserver de l’espace pour le jeu.

Les écrans sont conçus pour fonctionner sur ordinateur, tablette et mobile, avec une attention portée au clavier, aux contrastes, aux états de focus et à la réduction des animations.

## Technologies

| Domaine | Technologies |
| --- | --- |
| Serveur | PHP 8.5, Laravel 13 |
| Interface | Vue 3, Inertia 3, Tailwind CSS 3 |
| Éditeur enrichi | TipTap 3 |
| Base de données | MySQL 8 |
| Temps réel | Laravel Echo, Pusher Channels |
| Compilation | Node.js 24, npm 11, Vite 8 |
| Authentification | Laravel Jetstream, Fortify et Sanctum |
| Qualité | PHPUnit, Laravel Pint, tests fonctionnels et d’autorisation |

## Installation locale

### Prérequis

- PHP 8.5 avec les extensions requises par Laravel ;
- Composer ;
- Node.js 24 et npm 11 ;
- MySQL 8 ;
- une application Pusher Channels uniquement si le temps réel doit être testé.

### Préparation

```bash
git clone <url-du-depot>
cd Lifers
cp .env.example .env
composer install
npm ci
php artisan key:generate
```

Crée ensuite une base MySQL vide et adapte les valeurs suivantes dans `.env` :

```env
DB_DATABASE=lifers
DB_USERNAME=root
DB_PASSWORD=
```

Initialise les tables et les catalogues de référence, puis crée le lien vers les images envoyées par les utilisateurs :

```bash
php artisan migrate --seed
php artisan storage:link
```

### Lancement

Dans un premier terminal :

```bash
php artisan serve
```

Dans un second terminal :

```bash
npm run dev
```

Le site est ensuite disponible à l’adresse indiquée par Laravel, généralement `http://127.0.0.1:8000`.

## Configuration facultative

### Messages instantanés

Renseigne les identifiants de ta propre application Pusher dans `.env` :

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=eu
```

Ne publie jamais le fichier `.env` ni le secret Pusher. Sans configuration Pusher, l’application reste navigable mais les nouveaux messages peuvent nécessiter un rechargement pour apparaître.

### E-mails en développement

La vérification d’adresse et la réinitialisation du mot de passe nécessitent un service d’envoi. Pour écrire temporairement les e-mails dans les journaux Laravel sans installer de serveur SMTP local :

```env
MAIL_MAILER=log
```

Un véritable compte SMTP doit être configuré avant toute mise en ligne publique.

## Tâches du jeu

Le cycle quotidien gère notamment les jauges, salaires, maladies, décès et besoins familiaux. Sur un serveur disposant du planificateur Laravel, celui-ci doit être exécuté chaque minute.

Pour un hébergement mutualisé, la commande idempotente suivante regroupe les traitements utiles :

```bash
php artisan lifers:shared-hosting-tick
```

Le lanceur [`cron/lifers-shared-hosting.php`](cron/lifers-shared-hosting.php) peut être appelé par une tâche planifiée horaire. Les traitements quotidiens sont protégés contre une double application.

## Tests et qualité

La suite couvre notamment les autorisations des conversations, l’intégrité financière, les études, les métiers, la famille, la santé, la réincarnation, la modération et l’administration.

```bash
php artisan test
npm run build
vendor/bin/pint --test
```

> Les tests utilisent la base MySQL `lifers_testing` définie dans `phpunit.xml` et la réinitialisent. Il faut créer une base dédiée portant exactement ce nom et ne jamais la remplacer par une base contenant des données utiles.

## Déploiement

Pour une mise en ligne, il faut notamment :

- faire pointer la racine web du domaine vers `public/` ;
- utiliser `APP_ENV=production`, `APP_DEBUG=false` et l’URL HTTPS réelle ;
- générer une clé d’application propre au serveur ;
- configurer MySQL, Pusher, SMTP et les sauvegardes ;
- compiler les ressources avec `npm run build` ;
- mettre en cache la configuration après validation de l’environnement ;
- activer la tâche planifiée du cycle de jeu ;
- tester l’inscription, les e-mails et le temps réel avec deux sessions distinctes.

Les secrets de développement ne doivent jamais être réutilisés en production.

## Fonctions prévues plus tard

Les éléments suivants sont volontairement reportés afin de consolider d’abord le cœur du jeu :

- logements ;
- animaux et soins associés ;
- Rewinds et événements spéciaux ;
- personnalisation avancée de l’apparence du Lifer ;
- signalement ciblé d’un message privé ;
- enrichissement continu des études, métiers et contenus du monde.

## Autrice

Projet imaginé, conçu et développé par **Lucie Postiaux**.

Lifers est présenté dans le cadre de mon portfolio de développement web. Les identités, monnaies, maladies, relations et situations décrites dans le jeu sont fictives et relèvent d’une simulation.
