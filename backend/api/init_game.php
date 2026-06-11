<?php
// backend/api/init_game.php

// Autoriser le Frontend (Vue.js) à communiquer avec l'API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Démarrer la session pour mémoriser la partie
session_start();

// Inclure le moteur de jeu
require_once '../src/GameEngine.php';

// Créer une nouvelle instance du jeu du Songo
$jeu = new GameEngine();

// Stocker l'objet complet dans la session
$_SESSION['songo_game'] = serialize($jeu);

// Renvoyer l'état initial au format JSON pour le Frontend
http_response_code(200);
echo json_encode([
    "message" => "Partie de Songo initialisée !",
    "status" => $jeu->getState()
]);