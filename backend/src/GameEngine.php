<?php
// backend/src/GameEngine.php

class GameEngine {
    // Le plateau est un tableau de 14 cases
    // Index 0 à 6  : Territoire NORD (Adversaire)
    // Index 7 à 13 : Territoire SUD (Joueur actif par défaut)
    private array $plateau;
    
    private int $scoreSud;
    private int $scoreNord;
    private string $tour; // 'SUD' ou 'NORD'
    private string $statut; // 'EN_COURS', 'TERMINE', 'NUL'
    private ?string $vainqueur;

    // Le constructeur initialise le jeu
    public function __construct() {
        // Chaque case commence avec 5 graines (70 graines au total)
        $this->plateau = array_fill(0, 14, 5);
        $this->scoreSud = 0;
        $this->scoreNord = 0;
        $this->tour = 'SUD'; // Le joueur SUD commence
        $this->statut = 'EN_COURS';
        $this->vainqueur = null;
    }

    // Getters pour récupérer l'état actuel (utile pour notre API JSON)
    public function getState(): array {
        return [
            'plateau' => $this->plateau,
            'score_sud' => $this->scoreSud,
            'score_nord' => $this->scoreNord,
            'tour' => $this->tour,
            'statut' => $this->statut,
            'vainqueur' => $this->vainqueur
        ];
    }

    /**
     * Exécute le semis des graines à partir d'une case choisie
     * Le sens de rotation est anti-horaire (on décrémente les index)
     */
    private function semer(int $indexDepart): int {
        $graines = $this->plateau[$indexDepart];
        $this->plateau[$indexDepart] = 0; // On ramasse toutes les graines

        $indexCourant = $indexDepart;

        while ($graines > 0) {
            // Déplacement anti-horaire : on recule dans le tableau.
            // Si on dépasse 0, on revient à 13 (boucle fermée de 14 cases)
            $indexCourant = ($indexCourant - 1 + 14) % 14;

            // RÈGLE DES 13+ GRAINES : Si on revient sur la case de départ, on la saute
            if ($indexCourant === $indexDepart) {
                continue;
            }

            // On dépose une graine
            $this->plateau[$indexCourant]++;
            $graines--;
        }

        // On retourne l'index de la dernière case où une graine a été déposée
        return $indexCourant;
    }

    /**
     * Gère les captures de graines en chaîne et applique la règle anti-famine
     */
    private function recolter(int $indexFin, string $joueurActuel): void {
        // Définir les limites du camp adverse
        $debutAdversaire = ($joueurActuel === 'SUD') ? 0 : 7;
        $finAdversaire = ($joueurActuel === 'SUD') ? 6 : 13;
        $case1Adversaire = ($joueurActuel === 'SUD') ? 0 : 7;

        // Si la dernière graine n'est pas chez l'adversaire, aucune prise possible
        if ($indexFin < $debutAdversaire || $indexFin > $finAdversaire) {
            return;
        }

        // Sauvegarde du plateau au cas où le coup violerait la règle anti-famine
        $sauvegardePlateau = $this->plateau;
        $grainesCapturees = 0;
        
        $indexVerification = $indexFin;
        $recolteEnCours = true;

        while ($recolteEnCours && $indexVerification >= $debutAdversaire && $indexVerification <= $finAdversaire) {
            
            // Règle spécifique pour la Case n°1 de l'adversaire
            if ($indexVerification === $case1Adversaire) {
                // La prise standard de 2,3,4 ne s'applique pas directement sur la case 1
                // Sauf si elle fait partie d'une chaîne initiée ailleurs.
                if ($indexVerification !== $indexFin) {
                    // Inclusion dans la chaîne
                    $grainesCapturees += $this->plateau[$indexVerification];
                    $this->plateau[$indexVerification] = 0;
                }
                break; // On arrête l'analyse ici
            }

            // Condition de prise standard : la case contient 2, 3 ou 4 graines
            if ($this->plateau[$indexVerification] >= 2 && $this->plateau[$indexVerification] <= 4) {
                $grainesCapturees += $this->plateau[$indexVerification];
                $this->plateau[$indexVerification] = 0; // On vide la case
                
                // Au Songo, la chaîne remonte le sens de la distribution (sens horaire pour l'analyse)
                $indexVerification = ($indexVerification + 1) % 14;
            } else {
                $recolteEnCours = false; // La chaîne est brisée
            }
        }

        // --- INTERDIT N°2 : RÈGLE ANTI-FAMINE ---
        // Vérifier si le camp adverse a été complètement vidé
        $campAdverseVide = true;
        for ($i = $debutAdversaire; $i <= $finAdversaire; $i++) {
            if ($this->plateau[$i] > 0) {
                $campAdverseVide = false;
                break;
            }
        }

        if ($campAdverseVide && $grainesCapturees > 0) {
            // Annulation complète des prises ! On restaure le plateau
            $this->plateau = $sauvegardePlateau;
            return;
        }

        // Si tout est valide, on attribue les points
        if ($joueurActuel === 'SUD') {
            $this->scoreSud += $grainesCapturees;
        } else {
            $this->scoreNord += $grainesCapturees;
        }
    }

    /**
     * Simule un coup depuis une case donnée et compte combien de graines 
     * atterrissent dans le territoire adverse.
     */
    private function simulerGrainesPourAdversaire(int $indexCase, string $joueurActuel): int {
        $graines = $this->plateau[$indexCase];
        $indexCourant = $indexCase;
        $grainesPourAdversaire = 0;

        // Définir les limites du territoire adverse
        $debutAdversaire = ($joueurActuel === 'SUD') ? 0 : 7;
        $finAdversaire = ($joueurActuel === 'SUD') ? 6 : 13;

        while ($graines > 0) {
            $indexCourant = ($indexCourant - 1 + 14) % 14;

            // Règle des 13+ : on saute la case de départ
            if ($indexCourant === $indexCase) {
                continue;
            }

            // Si la graine tombe chez l'adversaire, on la compte
            if ($indexCourant >= $debutAdversaire && $indexCourant <= $finAdversaire) {
                $grainesPourAdversaire++;
            }

            $graines--;
        }

        return $grainesPourAdversaire;
    }

    /**
     * Détermine la liste des cases valides (index du tableau) qu'un joueur a le droit de jouer,
     * en appliquant strictement les règles de solidarité et de famine.
     */
    public function obtenirCoupsValides(string $joueurActuel): array {
        $coupsValides = [];
        
        // 1. Définir les zones du tableau
        $debutMien = ($joueurActuel === 'SUD') ? 7 : 0;
        $finMien = ($joueurActuel === 'SUD') ? 13 : 6;
        $debutAdversaire = ($joueurActuel === 'SUD') ? 0 : 7;
        $finAdversaire = ($joueurActuel === 'SUD') ? 6 : 13;

        // 2. Vérifier si le camp adverse est complètement vide
        $adversaireVide = true;
        for ($i = $debutAdversaire; $i <= $finAdversaire; $i++) {
            if ($this->plateau[$i] > 0) {
                $adversaireVide = false;
                break;
            }
        }

        // Trouver toutes mes cases qui contiennent au moins une graine
        $mesCasesDisponibles = [];
        for ($i = $debutMien; $i <= $finMien; $i++) {
            if ($this->plateau[$i] > 0) {
                $mesCasesDisponibles[] = $i;
            }
        }

        // Si l'adversaire n'est pas vide, le joueur est libre de jouer n'importe quelle case contenant des graines
        if (!$adversaireVide) {
            return $mesCasesDisponibles;
        }

        // --- APPLICATION DE LA SOLIDARITÉ ---
        $coupsDispensant7Graines = [];
        $meilleurCoupDeSecours = [];
        $maxGrainesEnvoyees = 0;

        foreach ($mesCasesDisponibles as $indexCase) {
            $grainesEnvoyees = $this->simulerGrainesPourAdversaire($indexCase, $joueurActuel);

            // Condition 1 : Le coup envoie au moins 7 graines
            if ($grainesEnvoyees >= 7) {
                $coupsDispensant7Graines[] = $indexCase;
            }

            // Préparation de la Condition 2 (Coup de secours apportant le maximum)
            if ($grainesEnvoyees > $maxGrainesEnvoyees) {
                $maxGrainesEnvoyees = $grainesEnvoyees;
                $meilleurCoupDeSecours = [$indexCase]; // Nouveau record
            } elseif ($grainesEnvoyees === $maxGrainesEnvoyees && $grainesEnvoyees > 0) {
                $meilleurCoupDeSecours[] = $indexCase; // Égalité de record
            }
        }

        // Si on a des coups qui donnent 7+ graines, ce sont les SEULS coups autorisés
        if (!empty($coupsDispensant7Graines)) {
            return $coupsDispensant7Graines;
        }

        // Si aucun coup ne fait 7+, mais qu'on peut envoyer un maximum inférieur à 7
        if ($maxGrainesEnvoyees > 0) {
            return $meilleurCoupDeSecours;
        }

        // Si aucun coup ne peut atteindre le camp adverse (Famine absolue)
        return [];
    }

    /**
     * Point d'entrée principal pour jouer un coup
     */
    public function jouerCase(int $indexCase, string $joueur): bool {
        // Validations de sécurité de base
        if ($this->statut !== 'EN_COURS' || $this->tour !== $joueur) {
            return false;
        }

        // --- VÉRIFICATION STRICTE DES COUPS AUTORISÉS (Solidarité incluse) ---
        $coupsAutorises = $this->obtenirCoupsValides($joueur);
        
        // Si la liste est vide, c'est que la solidarité est impossible -> Fin de partie
        if (empty($coupsAutorises)) {
            $this->gererFinDePartieParFamine();
            return false;
        }

        // Si le joueur clique sur une case non autorisée par la solidarité, on rejette le coup
        if (!in_array($indexCase, $coupsAutorises)) {
            return false;
        }

        // 1. Lancer le semis réel
        $indexFin = $this->semer($indexCase);

        // 2. Calculer les récoltes
        $this->recolter($indexFin, $joueur);

        // 3. Vérifier les conditions standards de fin de partie
        $this->verifierConditionsFinStandards();

        // 4. Changer de tour si la partie continue
        if ($this->statut === 'EN_COURS') {
            $this->tour = ($this->tour === 'SUD') ? 'NORD' : 'SUD';
            
            // Vérification immédiate pour le joueur suivant : peut-il jouer (solidarité) ?
            $coupsSuivants = $this->obtenirCoupsValides($this->tour);
            if (empty($coupsSuivants)) {
                $this->gererFinDePartieParFamine();
            }
        }

        return true;
    }

    /**
     * Gère la fin de partie lorsque la solidarité est impossible
     */
    private function gererFinDePartieParFamine(): void {
        $this->statut = 'TERMINE';
        
        // Règle : Les graines restantes reviennent au propriétaire de chaque camp
        for ($i = 0; $i <= 6; $i++) { $this->scoreNord += $this->plateau[$i]; $this->plateau[$i] = 0; }
        for ($i = 7; $i <= 13; $i++) { $this->scoreSud += $this->plateau[$i]; $this->plateau[$i] = 0; }

        $this->designerVainqueurFinal();
    }

    /**
     * Vérifie les conditions de fin : Score de 40 ou moins de 10 graines sur le tablier
     */
    private function verifierConditionsFinStandards(): void {
        if ($this->scoreSud >= 40) {
            $this->statut = 'TERMINE';
            $this->vainqueur = 'SUD';
            return;
        }
        if ($this->scoreNord >= 40) {
            $this->statut = 'TERMINE';
            $this->vainqueur = 'NORD';
            return;
        }

        // Règle : Moins de 10 graines restantes sur le tablier global
        if (array_sum($this->plateau) < 10) {
            $this->gererFinDePartieParFamine();
        }
    }

    /**
     * Détermine le vainqueur selon les règles du Songo (au moins 40 graines remportées)
     */
    private function designerVainqueurFinal(): void {
        if ($this->scoreSud >= 40) {
            $this->vainqueur = 'SUD';
        } elseif ($this->scoreNord >= 40) {
            $this->vainqueur = 'NORD';
        } else {
            // "Si aucun joueur n'accumule plus de 39 graines, la partie est nulle."
            $this->statut = 'NUL';
            $this->vainqueur = null;
        }
    }
}