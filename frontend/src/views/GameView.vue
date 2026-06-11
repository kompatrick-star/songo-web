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
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Orbitron:wght@500;800&family=Plus+Jakarta+Sans:wght@400;600;800&family=Poppins:wght@300;600&display=swap');

/* FOND CAPTIVANT EN DUNE DE SABLE CHAUDE */
.songo-epic-sand-container {
  background: #fdf2e9 url('http://googleusercontent.com/image_collection/image_retrieval/7159787163013233157') repeat;
  background-blend-mode: multiply;
  background-image: radial-gradient(#fff 20%, #eadccb 100%);
  min-height: 100vh;
  width: 100vw;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  box-sizing: border-box;
  font-family: 'Plus Jakarta Sans', sans-serif;
  overflow-x: hidden;
}

/* INTERFACE SUPÉRIEURE - GLASSMORPHISME LUXUEUX */
.glass-dashboard {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 1280px;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid rgba(212, 175, 55, 0.4);
  padding: 15px 35px;
  border-radius: 20px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255,255,255,0.2);
  box-sizing: border-box;
}

/* SIGNATURE MÉTALLISÉE CHROMÉE */
.designer-badge {
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
  border: 1px solid #d4af37;
  padding: 10px 25px;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.designer-badge .metal-shine {
  position: absolute;
  top: 0; left: -100%; width: 50%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
  transform: skewX(-25deg);
  animation: shine 4s infinite ease-in-out;
}
.designer-badge .label { font-size: 9px; color: #a48630; letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 2px; }
.designer-badge .name { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 14px; color: #1a0f0a; text-shadow: 0 1px 1px rgba(0,0,0,0.1); }

/* CONTROLES ET STATUT CENTRE */
.status-center { text-align: center; }
.tour-indicator { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 6px; }
.tour-indicator h2 { margin: 0; font-size: 1.15rem; color: #3e2723; font-weight: 400; }
.current-player-text { color: #bf9b30; font-weight: 800; text-shadow: 0 0 10px rgba(211, 175, 55, 0.2); }

.pulse-glow-gold {
  width: 10px;
  height: 10px;
  background-color: #f1c40f;
  border-radius: 50%;
  box-shadow: 0 0 12px #f1c40f;
  animation: pulse-ring 1.5s infinite;
}

.btn-abandon-text {
  color: #c0392b;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: bold;
  letter-spacing: 1px;
}

/* SCOREBOARD AVEC EFFET TROU DANS LE VERRE */
.scoreboard-hollow { display: flex; gap: 18px; }
.score-tag {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.3);
  padding: 6px 18px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}
.score-tag.turn-active { border-color: #d4af37; background: rgba(212,175,55,0.08); box-shadow: 0 0 15px rgba(212,175,55,0.1); }
.dot-led { width: 9px; height: 9px; border-radius: 50%; }
.dot-led.red { background: #e74c3c; box-shadow: 0 0 8px #e74c3c; }
.dot-led.green { background: #2ecc71; box-shadow: 0 0 8px #2ecc71; }
.p-info { display: flex; flex-direction: column; }
.p-name { font-weight: 600; font-size: 0.9rem; color: #1a0f0a; }
.p-score { font-family: 'Orbitron', sans-serif; font-size: 0.85rem; color: #1a0f0a; font-weight: 800; margin-top: 2px; }

/* LE PLATEAU HYPER-RÉALISTE EN BOIS CLAIR 3D SUR SABLE */
.perspective-board-wrapper {
  perspective: 1200px;
  width: 100%;
  max-width: 1280px;
  margin: 25px 0;
}

.songo-wood-board-3d {
  background: #eadccb;
  background-image: 
    radial-gradient(circle at 50% 50%, rgba(255,255,255,0.3) 0%, rgba(216,208,191,0.9) 100%),
    url('http://googleusercontent.com/image_collection/image_retrieval/7717830303867664326') repeat;
  padding: 40px 30px;
  border-radius: 40px;
  box-shadow: 
    0 40px 80px rgba(0, 0, 0, 0.1),
    inset 0 4px 8px rgba(255, 255, 255, 0.2),
    inset 0 -10px 20px rgba(0, 0, 0, 0.15);
  border: 10px solid #c9bcae;
  display: flex;
  align-items: center;
  gap: 25px;
  position: relative;
  transform: rotateX(10deg);
  transform-style: preserve-3d;
}

/* ZONE TECHNIQUE CENTRALE (14 TROUS) */
.pits-grid-game {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 40px;
  position: relative;
}

.laser-carved-divider {
  position: absolute;
  top: 50%; left: 0; right: 0; height: 3px;
  background: rgba(0, 0, 0, 0.1);
  box-shadow: 0 1px 1px rgba(255,255,255,0.05);
  transform: translateY(-50%);
}

.row-pits { display: flex; justify-content: space-between; gap: 16px; }
.pit-interactive-box { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.pit-id-text { font-family: 'Poppins', sans-serif; font-size: 0.75rem; font-weight: 600; color: #a48630; }

/* LES CAVITÉS SPHÉRIQUES PROFONDES & CLAIRES */
.pit-spherical-cavity {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #eadccb;
  background-image: radial-gradient(rgba(255,255,255,0.2) 0%, rgba(18,7,4,0.1) 100%);
  border-radius: 50%;
  position: relative;
  box-shadow: 
    inset 8px 12px 24px rgba(0, 0, 0, 0.2),
    1px 2px 2px rgba(255, 255, 255, 0.05);
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.cavity-specular {
  position: absolute;
  top: 10%; left: 15%; width: 30%; height: 15%;
  background: linear-gradient(to bottom, rgba(255,255,255,0.2), transparent);
  border-radius: 50%;
}

/* EFFET INTERACTIF QUAND JOUABLE */
.pit-interactive-box.clickable-playable .pit-spherical-cavity {
  cursor: pointer;
  border: 1.5px solid rgba(212,175,55,0.4);
}
.pit-interactive-box.clickable-playable .pit-spherical-cavity:hover {
  transform: translateY(-5px) scale(1.05);
  border-color: #d4af37;
  box-shadow: inset 4px 6px 12px rgba(0,0,0,0.1), 0 10px 25px rgba(212, 175, 55, 0.15);
}

.seeds-cluster-wrapper { position: absolute; width: 32px; height: 32px; }

/* LES MAGASINS ÉPIQUES ULTRA-CREUSÉS & CLAIRS */
.epic-store-store {
  width: 95px;
  height: 270px;
  background: #eadccb;
  background-image: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.2) 0%, rgba(18,7,4,0.1) 100%);
  border: 3px solid #c9bcae;
  border-radius: 50px;
  display: flex;
  flex-direction: column;
  padding: 12px 6px;
  box-shadow: inset 0 5px 15px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.05);
  box-sizing: border-box;
}

.inner-hollow-cavity {
  flex: 1;
  background: rgba(18,7,4,0.05);
  border-radius: 40px;
  position: relative;
  box-shadow: inset 6px 12px 25px rgba(0,0,0,0.15);
  overflow: hidden;
}

.store-soft-glow {
  position: absolute;
  bottom: 0; width: 100%; height: 40%;
  background: radial-gradient(circle at 50% 100%, rgba(212,175,55,0.08), transparent);
}

.store-label-plaque { text-align: center; margin-top: 10px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.store-label-plaque .badge { background: #d4af37; color: #fff; font-family: 'Orbitron', sans-serif; font-size: 0.8rem; font-weight: 800; padding: 2px 10px; border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
.store-label-plaque .label { font-size: 0.65rem; font-weight: 800; color: #a48630; letter-spacing: 1px; }

/* GRAINES EN MÉTAL PRÉCIEUX ET POLI AVEC REFLETS LUMINEUX */
.metallic-gold-seed {
  position: absolute;
  width: 14px;
  height: 14px;
  background: radial-gradient(circle at 35% 35%, #ffffff 0%, #e8bc3c 40%, #7e5c0d 100%);
  border-radius: 50%;
  box-shadow: 2px 3px 5px rgba(0,0,0,0.5), inset -1px -1px 2px rgba(0,0,0,0.2);
}

/* LE BOUTON ÉPIQUE DE RÉINITIALISATION SUR LE SABLE */
.action-restart-wrapper { margin: 25px 0; }
.btn-epic-restart {
  background: rgba(234, 220, 203, 0.8);
  border: 1px solid rgba(192, 169, 143, 0.6);
  border-radius: 50px;
  padding: 10px 40px;
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
  transition: all 0.2s ease;
}
.btn-epic-restart:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); background: #fdf2e9; }
.restart-icon { font-size: 1.1rem; }
.restart-text { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1rem; color: #bf9b30; }

/* TERMINAL DE CONTRÔLE ET HUD DU BAS (GLASS) */
.bottom-panel { display: grid; grid-template-columns: 1.4fr 0.6fr; gap: 30px; }
.hud-log-section h3 { font-family: 'Orbitron', sans-serif; font-size: 0.8rem; margin: 0 0 12px 0; color: #bf9b30; letter-spacing: 1px; text-transform: uppercase; }
.terminal-list-log { height: 95px; overflow-y: auto; font-family: 'monospace'; font-size: 0.78rem; color: #1a0f0a; }
.terminal-list-log p { margin: 5px 0; padding-bottom: 4px; border-bottom: 1px solid rgba(0,0,0,0.05); }
.neon-gold-text { color: #d35400; font-weight: bold; text-shadow: 0 0 8px rgba(243, 156, 18, 0.1); }

.action-alert-msg { font-family: 'Cinzel', serif; font-size: 1.1rem; color: #1a0f0a; font-weight: 700; margin: 10px 0 0 0; text-shadow: 0 1px 1px rgba(0,0,0,0.05); }
.empty-list-msg { font-style: italic; color: rgba(18,7,4,0.3); font-size: 0.8rem; margin-top: 10px; }

/* ANIMATIONS EFFETS GLOBAUX */
@keyframes shine {
  0% { left: -100%; }
  30% { left: 150%; }
  100% { left: 150%; }
}
@keyframes pulse-ring {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.5); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(243, 156, 18, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(243, 156, 18, 0); }
}
</style>