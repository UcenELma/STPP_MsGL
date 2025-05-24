# Système de Gestion de Traçabilité des Produits Périssables

Ce projet est un système simple de gestion de la traçabilité des produits périssables, incluant les utilisateurs, les produits, les entrepôts et le stock.

## 📦 Fonctionnalités principales

- Gestion des utilisateurs avec rôles (`admin`, `fournisseur`, `transporteur`)
- Gestion des produits périssables
- Suivi du stock dans les entrepôts

---

## 🛠️ Prérequis

Avant d’utiliser ce code, vous devez créer la base de données et les tables nécessaires.

### 📂 Création de la base de données

Connectez-vous à votre serveur MySQL et exécutez le script SQL suivant :

```sql
CREATE DATABASE IF NOT EXISTS bd_gestion_traceabilite;

USE bd_gestion_traceabilite;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe_hash VARCHAR(255),
    role ENUM('admin', 'fournisseur', 'transporteur') NOT NULL
);

CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    type VARCHAR(50),
    date_production DATE,
    date_peremption DATE,
    fournisseur_id INT,
    FOREIGN KEY (fournisseur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE entrepots (
    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE stock_entrepot (
    id INT NOT NULL AUTO_INCREMENT,
    produit_id INT NOT NULL,
    entrepot_id INT NOT NULL,
    qte INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (produit_id) REFERENCES produits(id),
    FOREIGN KEY (entrepot_id) REFERENCES entrepots(id)
);
```
## Licence
Ce projet est proposé à des fins éducatives et peut être librement modifié et adapté.

## Auteur
Projet réalisé par Hocine Elma
Dans le cadre du Master Génie Logistique – Faculté des Sciences Aïn-Chock – Université Hassan II de Casablanca.
