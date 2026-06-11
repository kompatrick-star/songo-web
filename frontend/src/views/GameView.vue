<template>
  <div v-if="gagnant" class="victory-overlay">
    <div class="victory-card">
      <h2>🎉 Partie Terminée !</h2>
      <p>{{ messageVictoire }}</p>
      <button @click="reinitialiserPartie" class="btn-epic-restart">Rejouer</button>
    </div>
  </div>

  <div class="songo-epic-sand-container">
    <div class="glass-dashboard top-panel">
      <div class="designer-badge">
        <div class="metal-shine"></div>
        <span class="label">CONCEPTEUR APPLICATION</span>
        <span class="name">GUEMNENG KOM PATRICK BRYAN</span>
      </div>

      <div class="status-center">
        <div class="tour-indicator">
          <span class="pulse-glow-gold"></span>
          <h2>Tour de : <span class="current-player-text">{{ tour === 'SUD' ? nomJoueurSud : nomJoueurNord }}</span></h2>
        </div>
        <router-link to="/" class="btn-abandon-text">🏳️ Abandonner la partie</router-link>
      </div>

      <div class="scoreboard-hollow">
        <div class="score-tag tag-nord" :class="{ 'turn-active': tour === 'NORD' }">
          <span class="dot-led red"></span>
          <div class="info">
            <span class="p-name">{{ nomJoueurNord }}</span>
            <span class="p-score">GRAINES : {{ scoreNord }}</span>
          </div>
        </div>
        <div class="score-tag tag-sud" :class="{ 'turn-active': tour === 'SUD' }">
          <span class="dot-led green"></span>
          <div class="info">
            <span class="p-name">{{ nomJoueurSud }}</span>
            <span class="p-score">GRAINES : {{ scoreSud }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="perspective-board-wrapper">
      <div class="songo-wood-board-3d">
        <div class="sand-grain-overlay"></div>
        <div class="epic-store-store store-nord">
          <div class="inner-hollow-cavity">
            <div class="store-soft-glow"></div>
            <div v-for="n in Math.min(scoreNord, 32)" :key="'n-'+n" class="metallic-gold-seed seed-nord" :style="generateStoreGrainStyle(n)"></div>
          </div>
          <div class="store-label-plaque">
            <span class="badge">{{ scoreNord }}</span>
            <span class="label">NORD</span>
          </div>
        </div>

        <div class="pits-grid-game">
          <div class="laser-carved-divider"></div>
          <div class="row-pits row-nord">
            <div v-for="(index, idx) in rangeeNordIndices" :key="index" class="pit-interactive-box" :class="{ 'clickable-playable': tour === 'NORD' && plateau[index] > 0 }" @click="jouerCase(index)">
              <span class="pit-id-text">N{{ 7 - idx }}</span>
              <div class="pit-spherical-cavity">
                <div class="cavity-specular"></div>
                <div class="seeds-cluster-wrapper">
                  <div v-for="n in Math.min(plateau[index], 12)" :key="'pit-'+index+'-'+n" class="metallic-gold-seed" :style="generateGrainStyle(n)"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row-pits row-sud">
            <div v-for="(index, idx) in rangeeSudIndices" :key="index" class="pit-interactive-box" :class="{ 'clickable-playable': tour === 'SUD' && plateau[index] > 0 }" @click="jouerCase(index)">
              <div class="pit-spherical-cavity">
                <div class="cavity-specular"></div>
                <div class="seeds-cluster-wrapper">
                  <div v-for="n in Math.min(plateau[index], 12)" :key="'pit-'+index+'-'+n" class="metallic-gold-seed" :style="generateGrainStyle(n)"></div>
                </div>
              </div>
              <span class="pit-id-text">S{{ idx + 1 }}</span>
            </div>
          </div>
        </div>

        <div class="epic-store-store store-sud">
          <div class="inner-hollow-cavity">
            <div class="store-soft-glow"></div>
            <div v-for="n in Math.min(scoreSud, 32)" :key="'s-'+n" class="metallic-gold-seed seed-sud" :style="generateStoreGrainStyle(n)"></div>
          </div>
          <div class="store-label-plaque">
            <span class="badge">{{ scoreSud }}</span>
            <span class="label">SUD</span>
          </div>
        </div>
      </div>
    </div>

    <div class="action-restart-wrapper">
      <button @click="reinitialiserPartie" class="btn-epic-restart">
        <div class="restart-icon">🔄</div>
        <div class="restart-text">Réessayer</div>
      </button>
    </div>

    <div class="glass-dashboard bottom-panel">
      <div class="hud-log-section">
        <h3>📜 Historique de Combat</h3>
        <div class="terminal-list-log">
          <p v-for="(log, i) in history" :key="i" :class="{ 'neon-gold-text': i === history.length - 1 }">{{ log }}</p>
          <p v-if="history.length === 0" class="empty-list-msg">En attente des hostilités...</p>
        </div>
      </div>
      <div class="hud-log-section centered-alert">
        <h3>⚡ Dernier Mouvement</h3>
        <p v-if="lastMove" class="action-alert-msg">{{ lastMove }}</p>
        <p v-else class="empty-list-msg">Aucune action enregistrée</p>
      </div>
    </div>
  </div>
</template>

<script>
import { io } from "socket.io-client";
const socket = io(import.meta.env.VITE_SOCKET_URL); // Change ici pour ton adresse ngrok plus tard

export default {
  data() {
    return {
      roomID: "",
      nomJoueurSud: localStorage.getItem('songo_player_sud') || 'Joueur 1',
      nomJoueurNord: localStorage.getItem('songo_player_nord') || 'Joueur 2',
      plateau: Array(14).fill(5),
      tour: 'SUD',
      scoreSud: 0, 
      scoreNord: 0,
      rangeeNordIndices: [13, 12, 11, 10, 9, 8, 7],
      rangeeSudIndices: [0, 1, 2, 3, 4, 5, 6],
      history: [],
      lastMove: ""
    }
  },
  mounted() {
    // Demander le nom du salon au chargement
    this.roomID = prompt("Entrez le nom du salon pour jouer avec un ami :") || "partie-par-defaut";
    socket.emit("rejoindre_partie", this.roomID);

    socket.on("coup_recu", (data) => {
      this.executerLogiqueCoup(data.index);
    });
  },
  methods: {
    jouerCase(index) {
      if (this.tour === 'SUD' && (index < 0 || index > 6)) return;
      if (this.tour === 'NORD' && (index < 7 || index > 13)) return;
      
      this.executerLogiqueCoup(index);
      
      // On envoie le roomID avec le coup
      socket.emit("jouer_coup", { roomID: this.roomID, index: index });
    },
    executerLogiqueCoup(index) {
      let nbrPions = this.plateau[index];
      if (nbrPions === 0) return;

      const pitName = index < 7 ? `S${index + 1}` : `N${index - 6}`;
      const joueurActuel = this.tour === 'SUD' ? this.nomJoueurSud : this.nomJoueurNord;
      
      this.lastMove = `${joueurActuel} a vidé la case [${pitName}]`;
      this.history.push(`[${new Date().toLocaleTimeString('fr-FR')}] ⚔️ ${this.lastMove}`);

      this.plateau[index] = 0;
      let currentIdx = index;

      while (nbrPions > 0) {
        currentIdx = (currentIdx + 1) % 14;
        if (currentIdx === index) continue; 
        this.plateau[currentIdx]++;
        nbrPions--;
      }

      if (this.tour === 'SUD' && currentIdx >= 7 && currentIdx <= 13) {
        if (this.plateau[currentIdx] === 2 || this.plateau[currentIdx] === 3) {
          this.scoreSud += this.plateau[currentIdx];
          this.plateau[currentIdx] = 0;
        }
      } else if (this.tour === 'NORD' && currentIdx >= 0 && currentIdx <= 6) {
        if (this.plateau[currentIdx] === 2 || this.plateau[currentIdx] === 3) {
          this.scoreNord += this.plateau[currentIdx];
          this.plateau[currentIdx] = 0;
        }
      }
      this.tour = this.tour === 'SUD' ? 'NORD' : 'SUD';
    },
    reinitialiserPartie() {
      this.plateau = Array(14).fill(5);
      this.tour = 'SUD';
      this.scoreSud = 0;
      this.scoreNord = 0;
      this.history = [];
      this.lastMove = "";
    },
    generateGrainStyle(n) {
      const angle = (n * 132.5) % 360;
      const radius = Math.min(n * 2.2, 17);
      const x = Math.cos(angle * Math.PI / 180) * radius;
      const y = Math.sin(angle * Math.PI / 180) * radius;
      return { transform: `translate(${x}px, ${y}px) scale(${1 - (n * 0.012)})`, zIndex: n };
    },
    generateStoreGrainStyle(n) {
      const row = Math.floor((n - 1) / 4);
      const col = (n - 1) % 4;
      return { top: `${20 + row * 16}px`, left: `${14 + col * 13}px` };
    }
  }
}
</script>

<style scoped>
/* (Ton style reste inchangé) */
</style>