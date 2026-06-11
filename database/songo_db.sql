-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS songo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE songo_db;

-- 1. Table des joueurs
CREATE TABLE IF NOT EXISTS joueurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    parties_jouees INT DEFAULT 0,
    victoires INT DEFAULT 0,
    defaites INT DEFAULT 0,
    nuls INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table des parties (Historique)
CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_joueur_1 INT NOT NULL,
    id_joueur_2 INT NOT NULL,
    score_joueur_1 INT DEFAULT 0,
    score_joueur_2 INT DEFAULT 0,
    id_vainqueur INT NULL, -- NULL en cas de match nul
    date_partie TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Clés étrangères pour lier les joueurs aux parties et garantir l'intégrité des données
    FOREIGN KEY (id_joueur_1) REFERENCES joueurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur_2) REFERENCES joueurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_vainqueur) REFERENCES joueurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;