# Application de Gestion de Budget Personnel

Une application Symfony complète pour la gestion de budget personnel avec tableaux de bord, statistiques et rapports mensuels.

## 🚀 Fonctionnalités

- **Authentification utilisateur** : Système de connexion sécurisé
- **Gestion des transactions** : Ajout, modification et suppression de revenus/dépenses
- **Catégories personnalisées** : Créer des catégories avec couleurs et icônes
- **Budgets mensuels** : Définir des budgets par catégorie
- **Tableau de bord** : Vue d'ensemble des finances avec graphiques interactifs
- **Rapports mensuels** : Statistiques détaillées et analyses
- **Architecture MVC** : Code organisé et maintenable
- **Services métier** : Logique séparée dans des services dédiés

## 📋 Prérequis

- Docker et Docker Compose
- Git (optionnel)

## 🛠️ Installation

### Méthode Automatique (Recommandée)

```bash
cd budget-app
chmod +x install.sh
./install.sh
```

Le script d'installation va :
- Démarrer les conteneurs Docker
- Installer les dépendances Composer
- Créer la base de données
- Exécuter les migrations
- Afficher les instructions pour créer votre premier utilisateur

### Méthode Manuelle

#### 1. Se placer dans le dossier

```bash
cd budget-app
```

#### 2. Démarrer les conteneurs Docker

```bash
docker-compose up -d
```

Cette commande va :
- Créer et démarrer les conteneurs PHP, Nginx, MySQL et phpMyAdmin
- Configurer le réseau entre les services
- Monter les volumes nécessaires

#### 3. Attendre que MySQL démarre (important !)

```bash
# Attendre 30 secondes
sleep 30
```

#### 4. Installer les dépendances Symfony

```bash
docker-compose exec php composer install
```

#### 5. Créer la base de données et les tables

```bash
# Créer la base de données
docker-compose exec php php bin/console doctrine:database:create

# Créer les migrations
docker-compose exec php php bin/console make:migration

# Exécuter les migrations
docker-compose exec php php bin/console doctrine:migrations:migrate -n
```

#### 6. Créer votre premier utilisateur

Consultez le fichier **INSTALL_USER.md** pour les instructions détaillées.

### Vérification de l'installation

```bash
chmod +x check.sh
./check.sh
```

## 🌐 Accès à l'application

- **Application web** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
  - Serveur : mysql
  - Utilisateur : budget_user
  - Mot de passe : budget_pass

## 📁 Structure du projet

```
budget-app/
├── docker/                      # Configuration Docker
│   ├── nginx/
│   │   └── default.conf        # Configuration Nginx
│   └── php/
│       └── Dockerfile          # Image PHP personnalisée
├── src/
│   ├── Controller/             # Contrôleurs MVC
│   │   ├── DashboardController.php
│   │   ├── TransactionController.php
│   │   ├── CategoryController.php
│   │   └── BudgetController.php
│   ├── Entity/                 # Entités Doctrine
│   │   ├── User.php
│   │   ├── Transaction.php
│   │   ├── Category.php
│   │   └── Budget.php
│   ├── Repository/             # Repositories pour les requêtes
│   │   ├── UserRepository.php
│   │   ├── TransactionRepository.php
│   │   ├── CategoryRepository.php
│   │   └── BudgetRepository.php
│   └── Service/                # Services métier
│       └── BudgetService.php
├── templates/                  # Templates Twig
│   ├── base.html.twig
│   ├── dashboard/
│   ├── transaction/
│   ├── category/
│   └── budget/
├── config/                     # Configuration Symfony
├── docker-compose.yml          # Orchestration Docker
└── .env                        # Variables d'environnement
```

## 🔧 Architecture

### Modèle MVC

L'application suit le pattern MVC (Modèle-Vue-Contrôleur) :

- **Modèle** : Entités Doctrine (User, Transaction, Category, Budget)
- **Vue** : Templates Twig avec Bootstrap 5
- **Contrôleur** : Controllers Symfony gérant la logique applicative

### Services

- **BudgetService** : Calculs de statistiques, rapports mensuels, analyse budgétaire

### Base de données

Schéma relationnel :
- `user` : Utilisateurs de l'application
- `category` : Catégories de transactions (liées aux utilisateurs)
- `transaction` : Transactions financières (liées aux utilisateurs et catégories)
- `budget` : Budgets mensuels (liés aux utilisateurs)

## 📊 Fonctionnalités détaillées

### Tableau de bord
- Cartes de synthèse (revenus, dépenses, solde)
- Graphique en donut des dépenses par catégorie
- Barres de progression de l'utilisation des budgets
- Graphique d'évolution sur 12 mois
- Liste des dernières transactions

### Gestion des transactions
- Création avec titre, montant, date, catégorie
- Modification et suppression
- Filtrage par mois/année
- Support des notes et méthodes de paiement

### Catégories
- Création personnalisée
- Types : revenus ou dépenses
- Couleurs et icônes
- Association aux transactions

### Budgets
- Définition par mois/année
- Association à une catégorie
- Suivi en temps réel de l'utilisation
- Alertes de dépassement

### Rapports
- Statistiques mensuelles complètes
- Répartition par catégorie
- Tendances sur 12 mois
- Métriques avancées

## 🔒 Sécurité

- Authentification par formulaire avec CSRF
- Hashage automatique des mots de passe
- Isolation des données par utilisateur
- Protection des routes avec les rôles

## 🛠️ Commandes utiles

```bash
# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f

# Accéder au conteneur PHP
docker-compose exec php bash

# Accéder à MySQL
docker-compose exec mysql mysql -u budget_user -p budget_db

# Créer un nouvel utilisateur (console Symfony)
docker-compose exec php php bin/console make:user

# Vider le cache
docker-compose exec php php bin/console cache:clear
```

## 📝 Développement

### Ajouter une nouvelle entité

```bash
docker-compose exec php php bin/console make:entity
```

### Créer un nouveau contrôleur

```bash
docker-compose exec php php bin/console make:controller
```

### Générer les migrations après modification d'entité

```bash
docker-compose exec php php bin/console make:migration
docker-compose exec php php bin/console doctrine:migrations:migrate
```

## 🐛 Dépannage

### Les conteneurs ne démarrent pas
```bash
docker-compose down -v
docker-compose up -d --build
```

### Erreur de connexion à la base de données
Vérifiez que le conteneur MySQL est bien démarré :
```bash
docker-compose ps
```

### Erreur de permissions
```bash
docker-compose exec php chown -R www-data:www-data /var/www/html/var
```

## 📚 Technologies utilisées

- **Symfony 7.0** : Framework PHP
- **Doctrine ORM** : Gestion de la base de données
- **Twig** : Moteur de templates
- **Bootstrap 5** : Framework CSS
- **Chart.js** : Graphiques interactifs
- **MySQL 8.0** : Base de données
- **Docker** : Conteneurisation
- **Nginx** : Serveur web

## 🎯 Améliorations futures

- Export PDF des rapports
- Notifications par email pour les dépassements de budget
- Gestion multi-devises
- Application mobile
- API REST
- Prévisions budgétaires avec IA
- Import de relevés bancaires

## 📄 Licence

MIT

## 👤 Auteur

Développé avec Symfony et Docker