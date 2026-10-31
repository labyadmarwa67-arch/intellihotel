# 🏨 IntelliHotel

Application web de **gestion hôtelière** développée en PHP / MySQL : chambres, clients, réservations, paiements, maintenance et tableaux de bord.

## 📌 Fonctionnalités

- **Authentification** avec 3 rôles : administrateur, réceptionniste, technicien
- **Gestion des chambres** : types (Single, Double, Suite), vue (Mer, Jardin, Piscine, Ville), prix et état (vide, réservée, occupée)
- **Gestion des clients** : ajout, modification, liste
- **Gestion des réservations** : création, modification, suivi
- **Tarification saisonnière** : coefficient de prix selon la saison (haute / basse saison)
- **Paiements et facturation** : encaissement (espèces, carte bancaire, virement), statut du paiement, génération de facture
- **Maintenance** : suivi des interventions par chambre et par technicien
- **Recherche** multicritères
- **Tableau de bord analytique** et **alertes**
- **Espace client** : consultation des chambres et réservation en ligne

## 🛠️ Technologies

| Côté | Outils |
|------|--------|
| Back-end | PHP (MySQLi) |
| Base de données | MySQL |
| Front-end | HTML, CSS, JavaScript |
| Environnement | XAMPP (Apache + MySQL) |

## 🗂️ Structure du projet

```
intellihotel/
├── admin/          # Tableau de bord et performances (administrateur)
├── client/         # Espace client : chambres et réservation
├── modules/        # Modules métier
│   ├── auth/           # Connexion / déconnexion
│   ├── chambres/
│   ├── clients/
│   ├── reservations/
│   ├── paiements/
│   ├── maintenance/
│   ├── recherche/
│   ├── dashboard/
│   └── utilisateurs/
├── config/         # Connexion à la base de données
├── includes/       # Éléments partagés
├── assets/         # CSS, JavaScript
├── database.sql    # Structure et données de démonstration
└── index.php       # Page d'accueil
```

## 🚀 Installation

1. Installer [XAMPP](https://www.apachefriends.org/) et démarrer **Apache** et **MySQL**.
2. Copier le dossier du projet dans `C:\xampp\htdocs\intellihotel`.
3. Ouvrir `http://localhost/phpmyadmin` et créer une base de données nommée **`intellihotel`**.
4. Dans cette base, cliquer sur **Importer** et choisir le fichier `database.sql`.
5. Vérifier les paramètres de connexion dans `config/database.php` (par défaut : `localhost`, `root`, sans mot de passe).
6. Ouvrir `http://localhost/intellihotel` dans le navigateur.

## 🔑 Compte de démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | `admin@intellihotel.ma` | `admin123` |

> ⚠️ Les données du fichier `database.sql` sont fictives et servent uniquement à la démonstration.

## 📸 Aperçu

*(Captures d'écran à ajouter : page d'accueil, tableau de bord, liste des réservations.)*

## 🔮 Améliorations prévues

- Remplacer le hachage MD5 des mots de passe par `password_hash()` / `password_verify()`
- Utiliser des requêtes préparées sur l'ensemble des formulaires
- Ajouter des tests et une documentation de l'API

## 👤 Auteur

**LABIAD Marwa**
GitHub : [@labyadmarwa67-arch](https://github.com/labyadmarwa67-arch)
