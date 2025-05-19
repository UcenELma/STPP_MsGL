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

CREATE TABLE tracabilite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT,
    temperature FLOAT,
    localisation VARCHAR(255),
    date_mesure DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT,
    FOREIGN KEY (produit_id) REFERENCES produits(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT,
    message TEXT,
    date_alerte DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('non_resolu', 'resolu') DEFAULT 'non_resolu',
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE TABLE seuils_critique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature_max FLOAT,
    temperature_min FLOAT,
    duree_stockage_max INT, -- en jours
    date_config DATETIME DEFAULT CURRENT_TIMESTAMP
);

<!-- //////////////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////////////////////////////////////////////////////////// -->



CREATE DATABASE IF NOT EXISTS bd_gestion_traceabilite;

USE bd_gestion_traceabilite;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe_hash VARCHAR(255),
    role ENUM('admin', 'fournisseur', 'transporteur') NOT NULL
);

CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    type VARCHAR(50),
    date_production DATE,
    date_peremption DATE,
    fournisseur_id INT,
    FOREIGN KEY (fournisseur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS tracabilite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT,
    temperature FLOAT,
    localisation VARCHAR(255),
    date_mesure DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT,
    FOREIGN KEY (produit_id) REFERENCES produits(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT,
    message TEXT,
    date_alerte DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('non_resolu', 'resolu') DEFAULT 'non_resolu',
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE TABLE IF NOT EXISTS seuils_critique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature_max FLOAT,
    temperature_min FLOAT,
    duree_stockage_max INT, -- en jours
    date_config DATETIME DEFAULT CURRENT_TIMESTAMP
);
