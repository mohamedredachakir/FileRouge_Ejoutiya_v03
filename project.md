# Cahier des Charges - Projet Ejoutiya (Version 03)

## 1. Introduction du Projet
**Ejoutiya** est une marketplace dédiée exclusivement à l'univers du **Streetwear**. L'objectif est de créer un écosystème où chaque **marque de streetwear** peut créer son propre espace (Brand Page), affirmer son identité visuelle et vendre ses collections directement aux passionnés de mode urbaine.

## 2. Pile Technologique
Le projet adopte une architecture découplée avec une API REST et un frontend Single Page Application (SPA).

### Backend (API REST)
- **Framework** : Laravel 11+
- **Langage** : PHP 8.3+
- **Authentification** : Laravel Sanctum (Personal Access Tokens)
- **Gestion de dépendances** : Composer

### Frontend (SPA)
- **Framework** : Vue 3
- **Outil de build** : Vite
- **Gestion d'état** : Pinia
- **Routage** : Vue Router
- **Styling** : TailwindCSS
- **Langage** : TypeScript

### Infrastructure & Stockage
- **Base de données** : PostgreSQL 16
- **Cache / File d'attente** : Redis 7
- **Serveur Web** : Nginx
- **Conteneurisation** : Docker & Docker Compose

## 3. Analyse Fonctionnelle

### 3.1 Acteurs du Système
1. **Client** : Passionné de streetwear souhaitant découvrir et acheter des pièces exclusives.
2. **Propriétaire de Marque (Brand Owner)** : Créateur gérant l'identité de sa marque et son catalogue de produits.
3. **Administrateur** : Super-utilisateur garant de la qualité des marques présentes et de la sécurité du système.

### 3.2 Cas d'Utilisation
#### Client
- Découvrir des marques de streetwear émergentes et établies.
- Consulter les collections et les détails des produits.
- Gérer un panier d'achat multi-marques.
- Suivre l'historique et le statut de ses commandes.

#### Propriétaire de Marque (Brand Owner)
- Créer et personnaliser l'identité de sa marque (Logo, Storytelling, Hero Image).
- Gérer le catalogue de produits streetwear (Collections, Drops).
- Gérer les stocks par tailles (standard streetwear : S, M, L, XL, XXL).
- Traiter les commandes et suivre ses performances de vente.

#### Administrateur
- Modérer et valider l'entrée des nouvelles marques sur la plateforme.
- Gérer les litiges et modérer les utilisateurs.
- Suspendre ou bannir des marques ne respectant pas la charte de la marketplace.

## 4. Modèle de Données (Entités Principales)

### Système & Authentification
- **Users** : Profils utilisateurs avec rôles spécifiques (client, brand_owner, admin).
- **Personal Access Tokens** : Gestion sécurisée des sessions API.

### Univers de Marque & Catalogue
- **Brands (Marques)** : Identité visuelle, bio de la marque, réseaux sociaux et statut (actif, suspendu, pending).
- **Products** : Pièces de streetwear liées à une marque, incluant prix, stock, catégorie et variantes.
- **Product Images** : Lookbook et photos de détails pour chaque produit.

### Ventes & Transactions
- **Carts & Cart Items** : Panier persistant permettant de regrouper des articles de différentes marques.
- **Orders & Order Items** : Commandes finalisées avec capture du prix au moment de l'achat.

## 5. Règles de Gestion

### Catégories Streetwear
- T-shirts (Oversized, Graphic), Hoodies, Pants (Cargo, Jogger), Sneakers, Accessories (Caps, Bags).

### Statuts de Commande
- `pending` (En attente)
- `confirmed` (Confirmée par la marque)
- `delivery` (En cours de livraison)
- `rejected` (Annulée/Refusée)

### Sécurité & Qualité
- Validation des designs et de l'authenticité des marques (processus d'approbation admin).
- Protection des données personnelles et sécurisation des transactions.

## 6. Architecture Technique

### Structure du Dossier
```text
/
├── backend/        # API Laravel (Logique métier & Données)
├── frontend/       # Interface Vue 3 (Expérience utilisateur premium)
├── docker/         # Configuration infra (PHP, Nginx, Postgres, Redis)
├── uml/            # Conception (UseCase, Schema DB orienté Brand)
└── docker-compose.yml
```

### Communication API
- Format : JSON.
- Protocole : RESTful sécurisé.
- Versioning : `/api/v1/`.

## 7. Déploiement & Environnement
Le projet est standardisé via Docker pour assurer une stabilité maximale.
- **Ports** :
  - Frontend : `5173`
  - API Gateway (Nginx) : `8000`
  - DB : `5432`
  - Cache : `6379`
