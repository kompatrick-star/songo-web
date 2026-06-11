<?php
// backend/api/play_coup.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
require_once '../src/GameEngine.php';

// 1. Vérifier si une partie existe en session
if (!isset($_SESSION['songo_game'])) {
    http_response_code(400);
    echo json_encode(["message" => "Erreur : Aucune partie en cours. Veuillez initialiser le jeu."]);
    exit();
}

// 2. Récupérer les données JSON envoyées par le Frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['index_case']) || !isset($data['joueur'])) {
    http_response_code(400);
    echo json_encode(["message" => "Erreur : Données manquantes (index_case ou joueur)."]);
    exit();
}

$indexCase = (int)$data['index_case'];
$joueur = trim(htmlspecialchars($data['joueur']));

// 3. Reconstituer l'objet GameEngine depuis la session
$jeu = unserialize($_SESSION['songo_game']);

// 4. Tenter de jouer le coup
$coupValide = $jeu->jouerCase($indexCase, $joueur);

if ($coupValide) {
    // Si le coup est réussi, on sauvegarde le nouvel état dans la session
    $_SESSION['songo_game'] = serialize($jeu);
    
    // Obtenir le nouvel état pour le renvoyer
    $etatActuel = $jeu->getState();
    
    // Ajouter les coups valides pour le prochain joueur (pour que le Frontend sache quoi griser)
    $etatActuel['coups_valides'] = $jeu->obtenirCoupsValides($etatActuel['tour']);

    http_response_code(200);
    echo json_encode([
        "message" => "Coup exécuté avec succès.",
        "status" => $etatActuel
    ]);
} else {
    // Si le coup viole une règle (ex: Solidarité, case vide, pas son tour...)
    http_response_code(400);
    echo json_encode([
        "message" => "Coup invalide ! Vérifiez les règles du Songo (Solidarité/Camp).",
        "status" => $jeu->getState()
    ]);
}