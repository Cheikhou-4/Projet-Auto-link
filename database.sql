CREATE DATABASE IF NOT EXISTS projet_auto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_auto;
CREATE TABLE IF NOT EXISTS voitures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    annee INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    type ENUM('vente','location') NOT NULL,
    description TEXT,
    image VARCHAR(255),
    vedette BOOLEAN DEFAULT 0,
    ville VARCHAR(100),
        categorie VARCHAR(50) NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table messages (contact)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_superadmin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Ajout de la colonne is_superadmin si elle n'existe pas déjà
ALTER TABLE admins ADD COLUMN IF NOT EXISTS is_superadmin TINYINT(1) DEFAULT 0;

-- Ajout d'un administrateur par défaut superadmin
INSERT INTO admins (username, email, password, is_superadmin) VALUES ('admin', 'Cheikhouniang395@gmail.com', 'Chvro12', 1);

-- Table des images associées à une voiture
CREATE TABLE IF NOT EXISTS images_voiture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voiture_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (voiture_id) REFERENCES voitures(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS publicites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('image','video') NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    titre VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les médias des publicités (images/vidéos multiples)
CREATE TABLE IF NOT EXISTS medias_pub (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pub_id INT NOT NULL,
    type ENUM('image','video') NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    FOREIGN KEY (pub_id) REFERENCES publicites(id) ON DELETE CASCADE
);

ALTER TABLE voitures 
ADD COLUMN kilometrage INT DEFAULT NULL,
ADD COLUMN transmission VARCHAR(30) DEFAULT NULL,
ADD COLUMN carburant VARCHAR(30) DEFAULT NULL;

ALTER TABLE publicites 
ADD COLUMN date_debut DATE DEFAULT NULL,
ADD COLUMN date_fin DATE DEFAULT NULL;

ALTER TABLE publicites ADD COLUMN lien VARCHAR(255) DEFAULT NULL;

-- Ajout de la colonne categorie si elle n'existe pas déjà
ALTER TABLE voitures ADD COLUMN IF NOT EXISTS categorie VARCHAR(50) NOT NULL;
