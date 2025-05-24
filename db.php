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

-- Table: entrepots
CREATE TABLE entrepots (
    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

-- Table: stock_entrepot
CREATE TABLE stock_entrepot (
    id INT NOT NULL AUTO_INCREMENT,
    produit_id INT NOT NULL,
    entrepot_id INT NOT NULL,
    qte INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (produit_id) REFERENCES produits(id),
    FOREIGN KEY (entrepot_id) REFERENCES entrepots(id)
);

