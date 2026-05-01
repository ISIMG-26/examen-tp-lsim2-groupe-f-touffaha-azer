
									 TechStore - Plateforme E-commerce High-Tech
Touffaha Azer Groupe F

 Description du Projet
TechStore est une solution e-commerce complète dédiée à la vente de produits informatiques et high-tech. Le projet a été conçu pour offrir une expérience utilisateur fluide et moderne, tout en garantissant une gestion administrative robuste.

 Fonctionnalités Clés :
   Catalogue Dynamique : Affichage des produits avec filtrage par catégorie et recherche textuelle.
   Gestion de Panier AJAX : Ajout d'articles au panier sans rechargement de page pour une meilleure UX.
   Système de Commande : Processus de checkout sécurisé avec calcul automatique de la TVA (10/19%), du timbre fiscal et des remises.
   Gestion de Stock Intelligente : Décrémentation automatique du stock lors de chaque achat et masquage des produits en rupture.
   Espace Client : Historique des commandes, accès aux factures et système d'avis clients.
   Panel Administration : CRUD complet des produits, suivi des commandes et gestion des statuts (Payée, Livrée, etc.).
   Design Premium : Interface en mode sombre (Dark Mode) par défaut, entièrement responsive.



 Stack Technique
   Backend : PHP 8.x (Natif)
   Base de données : MySQL
   Frontend : HTML5, CSS3 (Variables CSS, Flexbox/Grid), JavaScript (Vanilla, Fetch API)
   Serveur : XAMPP / Apache



 Répartition des Tâches (Task Breakdown)

| Module            | Responsable           | Tâches spécifiques |
| Architecture & DB | Développeur Backend   | Modélisation SQL, connexion PDO/MySQLi, helpers PHP. |
| Gestion Produits  | Développeur Backend   | CRUD Admin, gestion des catégories, logique de stock. |
| UI/UX & Design    | Développeur Frontend  | Design system, mode sombre, responsive design. |
| Ventes & Panier   | Développeur Fullstack | Logique du panier (AJAX), tunnel de commande (Checkout). |
| Espace Client     | Développeur Fullstack | Authentification, historique commandes, système d'avis. |
| Facturation       | Développeur Backend   | Génération des factures, calculs fiscaux (TVA/Timbre). |


 Installation
1.  Cloner le projet dans le répertoire `htdocs` de votre serveur local.
2.  Importer le fichier `database/ecommerce_db.sql` dans phpMyAdmin.
3.  Configurer les accès dans `back/db.php` si nécessaire.
4.  Accéder au projet via `http://localhost/project`.

Compte Admin par défaut :
   Login : admin.admin@admin.com
   Password : adminadmin
=======
# azer
TechStore est une solution e-commerce high-tech en PHP natif. Elle intègre un catalogue dynamique, un panier AJAX et un tunnel d'achat complet avec gestion auto des stocks et calculs fiscaux. Inclut un panel admin pour le suivi des ventes et un design premium responsive en mode sombre. Espace client avec facturation détaillée.
>>>>>>> 2eecfcaa56652cc703d378f1536f748eab8db3e0
