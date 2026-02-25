# NutriSport — API E-Commerce (Laravel)

Projet réalisé dans le cadre d’un test technique.
Il s’agit d’une API REST développée avec Laravel permettant la gestion d’un catalogue multi-sites, clients, panier, commandes, back-office agents et flux catalogue publics.

---

# Installation

```bash
git clone https://github.com/azaou123/laravel-api-nutrisport
cd laravel-api-nutrisport
```

```bash
docker exec -it nutrisport_app bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurer la base de données et les services dans le fichier `.env`, puis :

```bash
php artisan migrate --seed
php artisan serve
```

---

# Routes API

| Méthode | Endpoint                      | Description                |
| ------- | ----------------------------- | -------------------------- |
| GET     | /api/products                 | Liste des produits         |
| GET     | /api/products/{id}            | Détail produit             |
| GET     | /api/cart                     | Contenu du panier          |
| POST    | /api/cart/add                 | Ajouter au panier          |
| POST    | /api/cart/remove              | Supprimer du panier        |
| DELETE  | /api/cart/clear               | Vider le panier            |
| POST    | /api/register                 | Inscription client         |
| POST    | /api/login/customer           | Connexion client           |
| POST    | /api/login/agent              | Connexion agent            |
| GET     | /api/me                       | Profil utilisateur         |
| POST    | /api/logout                   | Déconnexion                |
| POST    | /api/refresh                  | Refresh token              |
| POST    | /api/change-password          | Changer mot de passe       |
| POST    | /api/orders                   | Passer une commande        |
| GET     | /api/backoffice/orders/recent | Commandes récentes (agent) |
| POST    | /api/backoffice/products      | Créer un produit (agent)   |
| GET     | /api/feeds                    | Liens des flux catalogue   |
| GET     | /api/feeds/json               | Flux catalogue JSON        |
| GET     | /api/feeds/xml                | Flux catalogue XML         |

---
