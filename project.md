# 📄 Cahier de charge – Projet _Ejoutiya_

## 🧾 1. Présentation du projet

**Ejoutiya** est une application web e-commerce spécialisée dans la vente de vêtements _streetwear_, inspirée des plateformes modernes comme les marketplaces de créateurs (ex: Gumroad), adaptée au contexte local avec paiement à la livraison (_Cash on Delivery_).

L’objectif est de permettre à des **boutiques (stores)** de créer leur propre vitrine en ligne et de vendre leurs produits directement aux **clients**, tout en étant supervisées par un **administrateur**.

---

## 🎯 2. Objectifs du projet

- Créer une plateforme multi-vendeurs simple et scalable
- Permettre aux stores de gérer leurs produits et leur image de marque
- Offrir une expérience d’achat fluide pour les clients
- Gérer les commandes sans intégration de paiement en ligne (COD)
- Fournir un système d’administration pour contrôler la plateforme

---

## 👥 3. Types d’utilisateurs

### 👤 Client

- Création de compte
- Consultation des produits
- Gestion du panier (Redis)
- Passation de commandes
- Fourniture des informations de livraison :
  - téléphone
  - ville
  - code postal
  - adresse

---

### 🏪 Store (Boutique)

- Création et gestion d’un espace boutique
- Personnalisation de la page :
  - nom
  - bio
  - logo
  - image de couverture (_hero section_)

- Gestion des produits :
  - ajout / modification / suppression
  - gestion du stock
  - upload d’images multiples

---

### 🛠️ Admin

- Gestion globale de la plateforme
- Actions possibles :
  - bannir un utilisateur
  - suspendre une boutique
  - valider ou refuser une boutique

---

## 🧱 4. Modèle fonctionnel (basé sur le diagramme)

### 📦 Produits

- Chaque produit appartient à une seule boutique
- Un produit possède :
  - nom, description, prix, stock
  - catégorie (_T-shirt, Hoodie, etc._)
  - statut (_actif, hors stock, caché_)

- Un produit peut avoir plusieurs images

---

### 🛒 Panier (Redis - Cache uniquement)

- Le panier n’est **pas persisté en base de données**
- Il est stocké dans **Redis** pour optimiser les performances

#### 🔹 Structure

- Clé : `cart:{client_id}`
- Données :
  - product_id
  - quantity
  - price (snapshot)

#### 🔹 Règles

- Un seul panier actif par client
- Créé à la première interaction
- Mis à jour en temps réel
- Supprimé après validation de la commande
- Expiration automatique possible (TTL: 24h)

#### 🔹 Flux

1. Ajout produit → stockage Redis
2. Consultation panier → lecture Redis
3. Checkout → création Order en DB
4. Suppression du panier Redis

---

### 🧾 Commandes

- Une commande appartient à un client
- Une commande contient plusieurs éléments (_OrderItems_)
- Chaque élément contient :
  - produit
  - quantité
  - prix (snapshot)

- Statuts possibles :
  - PENDING
  - CONFIRMED
  - DELIVERY
  - REJECTED

---

### 🏪 Boutiques

- Une boutique possède plusieurs produits
- Statuts :
  - ACTIVE
  - SUSPENDED
  - PENDING_APPROVAL

---

## ⚙️ 5. Stack technique

### 🔹 Backend

- Laravel 13
- API REST
- Laravel Sanctum
- Redis (cache + cart)

### 🔹 Détails backend (implémentation actuelle)

#### Authentification

- Auth client séparée de l auth store
- Endpoints en place :
  - `POST /api/auth/register` (client)
  - `POST /api/auth/register-store` (store)
  - `POST /api/auth/login`
  - `POST /api/auth/logout`

#### Profil

- Endpoints client en place :
  - `GET /api/me`
  - `PUT /api/me`

#### Boutiques (Store)

- Public :
  - `GET /api/stores`
  - `GET /api/stores/{id}`

- Store owner :
  - `GET /api/store/me`
  - `PUT /api/store/me`

#### Produits

- Public :
  - `GET /api/products`
  - `GET /api/products/{id}`

- Store owner :
  - `POST /api/store/products`
  - `PUT /api/store/products/{id}`
  - `DELETE /api/store/products/{id}`

- Images produits :
  - support de `images[]` (paths) sur create/update
  - liens exposés: `product.main_image_url` et `product.images[].image_url`

#### Panier (Redis)

- Endpoints client en place :
  - `GET /api/cart`
  - `POST /api/cart/items`
  - `PUT /api/cart/items/{productId}`
  - `DELETE /api/cart/items/{productId}`
  - `DELETE /api/cart`

- Clé Redis: `cart:{client_id}`
- TTL: 24h

#### Commandes

- Côté client :
  - `POST /api/orders/checkout`
  - `GET /api/orders/me`
  - `GET /api/orders/me/{orderId}`

- Côté store :
  - `GET /api/store/orders`
  - `PATCH /api/store/orders/{orderId}/status` (confirmed/rejected)

#### Administration

- Endpoints admin en place :
  - `GET /api/admin/users`
  - `PATCH /api/admin/users/{id}/ban`
  - `PATCH /api/admin/users/{id}/unban`
  - `GET /api/admin/stores`
  - `PATCH /api/admin/stores/{id}/approve`
  - `PATCH /api/admin/stores/{id}/suspend`
  - `PATCH /api/admin/stores/{id}/reject`

#### Middlewares

- `role`
- `not.banned`
- `store.approved`

### 🔹 Frontend

- Vue.js 3 + TypeScript
- Pinia
- Vue Router
- TailwindCSS

### 🔹 Infrastructure

- Docker
  - PHP
  - Redis
  - PostgreSQL / MySQL
  - Nginx

---

## 🔐 6. Règles de gestion

- Un utilisateur peut être Client, Store ou Admin
- Une boutique doit être validée avant activation
- Un produit caché n’est pas visible
- Une commande est immuable après validation
- Paiement uniquement à la livraison
- Le panier est volatile (Redis)

---

## 🚀 7. Fonctionnalités principales (MVP)

### Client

- Parcourir produits
- Gérer panier
- Passer commande

### Store

- Gérer boutique
- Gérer produits
- Suivre commandes

### Admin

- Gérer utilisateurs
- Gérer boutiques

---

## 📈 8. Évolutions possibles

- Reviews
- Wishlist
- Tracking livraison
- Dashboard analytics
- Notifications temps réel

---

## 🏁 Conclusion

Ejoutiya est une plateforme moderne, performante et scalable, utilisant Redis pour optimiser l’expérience utilisateur côté panier, avec une architecture robuste basée sur Laravel, Vue.js et Docker.

---

## this project inspire from [gumroad](https://gumroad.com/)

--- as u see we use name Ejoutiya and we fix it to be in one nich "streetwear"

## pages :

- store-global :
  _(1) landing_page inspire from [gumroad](https://gumroad.com/) "but fix it to be streetwearlanding page gangstars"
  _(2) products_page this page hava all products fetching by category
  _(3) product_details this page have details of this product with gallery of img product and price and info store
  _(4) stores_page this page have list of all stores any store whit his infos
  \*(5) store_detail this page for one store have all details of this store from name bio imgs and pruducts all
- auth-modals
  _(6) login modal
  _(7) register_client modal
  \*(8) register_store modal
- store-pages
  _(9) store_detail this page for one store have all details of this store from name (landing_page)
  bio imgs and pruducts all
  _(10) store_dashboard this page when he can manage all hes store can edit hes landig page infos like img hero and bio and logo and mangage hes products and manage the orders
- client-pages
  _(11) cart this should be modals side
  _(12) profils page this page have settings when he can modif hes infos and sttings app and orders when he can see all hes oreder he make and see the progress status and helpcenter when he can see size guide of all products and some infos
- admin-pages
  \*(13) dashboard page when he can maange all system

## permetions for pages :

- we have this users and can see :

* guest(not log) =>(1-2-3-4-5-6-7-8-9-12)
* client => (1-2-3-4-5-9-11-12)
* store => (9-10)
* admin => (1-13)

## accecibility :

- user can redirect easy with many btn redirect to back pages last (if he in page details prdouct or store can redirect easy to page store of product of page sotres not redirect to home )
- load easy for imgs

## theme :

- streetwear store multi vendors or stores as brand "brands"
- gangstars usa black people
- rebellion only
- drugs
- skeatboard
- hiphop
- buggy

# palette colors :

:root {
--black: #000000;
--charcoal: #222222;
--mid-gray: #666666;
--light-gray: #CCCCCC;
--white: #FFFFFF;
}

## inspire ui :

- https://kith.com/
- https://row.representclo.com/
- https://slamjam.com/en-row
- https://www.culturekings.com/
- https://denimtears.com/
- https://nude-project.com/?country=ES
-

## infos ui :

- befoare enter the app user see btn join whe he click he see the landing_page
- landing_page scrolling animation
- no raduis in any btn or header or div
- focus in dark and with and blood
- use texture in ui type lace

## notification :

- create costume notif not using alers from browser

## icons :

- https://fontawesome.com/icons
