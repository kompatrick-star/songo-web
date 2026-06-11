<template>
  <div class="songo-authentic-container">
    
    <div class="main-board-wrapper">
      
      <div class="score-store store-left">
        <div class="store-counter-badge">{{ scoreNord }}</div>
        <div class="store-trough">
          <div class="store-label">NORD</div>
          <div class="captured-seeds-grid">
            <div 
              v-for="n in Math.min(scoreNord, 40)" 
              :key="'nord-seed-'+n" 
              class="metallic-gold-seed stack-seed"
            ></div>
          </div>
        </div>
      </div>

      <div class="wooden-carved-board">
        
        <div class="board-status-overlay">
          <div class="status-badge-wood">A vous de jouer</div>
        </div>

        <div class="pits-grid-row row-north">
          <div 
            v-for="(index, idx) in rangeeNordIndices" 
            :key="index" 
            class="songo-pit-box"
            :class="{ 'active-playable': tour === 'NORD' && plateau[index] > 0 }"
            @click="jouerCase(index)"
          >
            <div class="pit-numerical-indicator">{{ plateau[index] }}</div>
            <div class="organic-pit-hole" :class="{ 'current-turn-blink': tour === 'NORD' && index === 13 }">
              <div class="seeds-cluster">
                <div 
                  v-for="n in Math.min(plateau[index], 12)" 
                  :key="'n-seed-'+index+'-'+n" 
                  class="metallic-gold-seed"
                  :style="getSeedScatterStyle(n)"
                ></div>
              </div>
            </div>
            <span class="pit-identifier-text">N{{ idx + 1 }}</span>
          </div>
        </div>

        <div class="pits-grid-row row-south">
          <div 
            v-for="(index, idx) in rangeSudIndices" 
            :key="index" 
            class="songo-pit-box"
            :class="{ 'active-playable': tour === 'SUD' && plateau[index] > 0 }"
            @click="jouerCase(index)"
          >
            <div class="pit-numerical-indicator">{{ plateau[index] }}</div>
            <div class="organic-pit-hole">
              <div class="seeds-cluster">
                <div 
                  v-for="n in Math.min(plateau[index], 12)" 
                  :key="'s-seed-'+index+'-'+n" 
                  class="metallic-gold-seed"
                  :style="getSeedScatterStyle(n)"
                ></div>
              </div>
            </div>
            <span class="pit-identifier-text">S{{ 7 - idx }}</span>
          </div>
        </div>

      </div>

      <div class="score-store store-right">
        <div class="store-counter-badge">{{ scoreSud }}</div>
        <div class="store-trough">
          <div class="store-label">SUD</div>
          <div class="captured-seeds-grid">
            <div 
              v-for="n in Math.min(scoreSud, 40)" 
              :key="'sud-seed-'+n" 
              class="metallic-gold-seed stack-seed"
            ></div>
          </div>
        </div>
      </div>

    </div>

    <div class="dashboard-footer-layout">
      
      <div class="status-card panel-last-move">
        <h3 class="panel-heading">DERNIER COUP</h3>
        <div class="card-content-body">
          <p v-if="lastMove" class="emphasized-move-text">{{ lastMove }}</p>
          <p v-else class="fallback-empty-text">Aucun coup joué pour le moment.</p>
        </div>
      </div>

      <div class="status-card panel-history">
        <h3 class="panel-heading">HISTORIQUE</h3>
        <div class="card-content-body log-scrollable" ref="historyScrollContainer">
          <div 
            v-for="(log, i) in history" 
            :key="'log-'+i" 
            class="history-log-row"
            :class="{ 'highlighted-latest': i === 0 }"
          >
            {{ history.length - i }}. {{ log }}
          </div>
          <p v-if="history.length === 0" class="fallback-empty-text">Le journal est vide.</p>
        </div>
      </div>

    </div>

  </div>
</template>

<script>
export default {
  data() {
    return {
      plateau: [0, 3, 2, 2, 4, 4, 1, 1, 3, 3, 4, 4, 0, 1], // Exemple de valeurs de ton image modèle
      tour: 'NORD',
      scoreSud: 14, // Score SUD de ton image
      scoreNord: 32, // Score NORD de ton image
      rangeeNordIndices: [7, 8, 9, 10, 11, 12, 13], // Correspond à N1, N2, N3, N4, N5, N6, N7
      rangeSudIndices: [6, 5, 4, 3, 2, 1, 0],       // Correspond à S7, S6, S5, S4, S3, S2, S1
      history: [
        "Sud joue S1, capture 0.",
        "Nord joue N7, capture 3.",
        "Sud joue S5, capture 0.",
        "Nord joue N1, capture 0.",
        "Sud joue S3, capture 0."
      ],
      lastMove: "Nord joue N2, derniere graine en N7, aucune capture."
    }
  },
  methods: {
    jouerCase(index) {
      if (this.tour === 'SUD' && (index < 0 || index > 6)) return;
      if (this.tour === 'NORD' && (index < 7 || index > 13)) return;
      
      const count = this.plateau[index];
      if (count === 0) return;

      const sideLabel = index < 7 ? 'Sud' : 'Nord';
      const positionLabel = index < 7 ? `S${index + 1}` : `N${index - 6}`;
      
      this.plateau[index] = 0;
      let current = index;
      
      for (let i = 0; i < count; i++) {
        current = (current + 1) % 14;
        if (current === index) { i--; continue; }
        this.plateau[current]++;
      }

      // Logique simple de capture Songo (2 ou 3 pions)
      let captured = 0;
      if (this.plateau[current] === 2 || this.plateau[current] === 3) {
        if (this.tour === 'SUD' && current >= 7) {
          captured = this.plateau[current];
          this.scoreSud += captured;
          this.plateau[current] = 0;
        } else if (this.tour === 'NORD' && current <= 6) {
          captured = this.plateau[current];
          this.scoreNord += captured;
          this.plateau[current] = 0;
        }
      }

      const captureMsg = captured > 0 ? `capture ${captured}.` : "aucune capture.";
      this.lastMove = `${sideLabel} joue ${positionLabel}, derniere graine en ${current < 7 ? 'S'+(current+1) : 'N'+(current-6)}, ${captureMsg}`;
      this.history.unshift(`${sideLabel} joue ${positionLabel}, ${captureMsg}`);
      
      this.tour = this.tour === 'SUD' ? 'NORD' : 'SUD';
    },
    getSeedScatterStyle(n) {
      const angle = (n * 137.5) % 360;
      const distance = Math.min(n * 2.5, 14);
      return {
        transform: `translate(${Math.cos(angle * Math.PI / 180) * distance}px, ${Math.sin(angle * Math.PI / 180) * distance}px)`
      };
    }
  }
}
</script>

<style scoped>
/* AMBIANCE TEXTURÉE NATURELLE */
.songo-authentic-container {
  background-color: #e6dfd5;
  background-image: radial-gradient(rgba(255, 255, 255, 0.5) 20%, rgba(214, 206, 190, 0.8) 100%);
  min-height: 100vh;
  width: 100vw;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* LE CONTENEUR DU PLATEAU COMPLIQUANT LES MAGASINS */
.main-board-wrapper {
  display: flex;
  align-items: stretch;
  justify-content: center;
  gap: 12px;
  width: 100%;
  max-width: 1100px;
  background: #3a2316;
  padding: 20px;
  border-radius: 35px;
  box-shadow: 0 25px 50px rgba(45, 30, 20, 0.45);
  border: 5px solid #28180e;
  box-sizing: border-box;
}

/* COMPARTIMENTS GAUCHE ET DROITE (STORES) */
.score-store {
  width: 100px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.store-counter-badge {
  background: #ffffff;
  color: #000000;
  font-weight: 700;
  font-size: 22px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.25);
}

.store-trough {
  flex: 1;
  width: 100%;
  background: #201108;
  border-radius: 50px;
  box-shadow: inset 10px 0 20px rgba(0,0,0,0.7);
  padding: 25px 5px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
}

.store-label {
  font-size: 12px;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.35);
  letter-spacing: 1.5px;
}

.captured-seeds-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  justify-content: center;
  padding: 10px 6px;
  max-height: 340px;
  overflow-y: auto;
}

/* LE RECTANGLE DE BOIS CENTRAL */
.wooden-carved-board {
  flex: 1;
  background: #603f26;
  background-image: repeating-linear-gradient(45deg, #573821 0px, #573821 3px, #66432a 3px, #66432a 6px);
  border-radius: 22px;
  padding: 35px 20px 20px 20px;
  box-shadow: inset 0 0 50px rgba(0,0,0,0.45);
  display: flex;
  flex-direction: column;
  gap: 40px;
  position: relative;
}

/* BOUTON CENTRAL AVEC SOUCHETTE BRUNE */
.board-status-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 5;
}

.status-badge-wood {
  background: #311e14;
  color: #ffffff;
  padding: 8px 24px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
  border: 1px solid #4a3022;
}

/* LIGNES DES TROUS (PITS) */
.pits-grid-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.songo-pit-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 12%;
}

.pit-numerical-indicator {
  background: #ffffff;
  color: #000000;
  font-weight: bold;
  font-size: 14px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 6px;
  box-shadow: 0 3px 5px rgba(0,0,0,0.2);
}

.organic-pit-hole {
  width: 100%;
  aspect-ratio: 1/1;
  background: #1c0f07;
  border-radius: 50%;
  box-shadow: inset 6px 8px 15px rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.current-turn-blink {
  border: 2px solid #5a3c28;
  box-shadow: inset 6px 8px 15px rgba(0, 0, 0, 0.9), 0 0 10px rgba(90, 60, 40, 0.5);
}

.active-playable .organic-pit-hole {
  cursor: pointer;
}

.seeds-cluster {
  position: absolute;
  width: 35px;
  height: 35px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* LES BILLES DORÉES TRÈS PROPRES */
.metallic-gold-seed {
  position: absolute;
  width: 13px;
  height: 13px;
  background: radial-gradient(circle at 35% 35%, #fffde0 0%, #cca327 45%, #7a5e12 100%);
  border-radius: 50%;
  box-shadow: 1px 2px 3px rgba(0,0,0,0.65);
}

.stack-seed {
  position: static;
  width: 11px;
  height: 11px;
}

.pit-identifier-text {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.3);
  font-weight: bold;
  margin-top: 8px;
}

/* PANNEAUX BLANCS EN BAS */
.dashboard-footer-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
  width: 100%;
  max-width: 1100px;
  margin-top: 30px;
}

.status-card {
  background: #faf8f5;
  border-radius: 10px;
  padding: 25px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.05);
  box-sizing: border-box;
}

.panel-heading {
  font-size: 12px;
  color: #8a8377;
  margin: 0 0 15px 0;
  letter-spacing: 1.2px;
}

.card-content-body {
  color: #242220;
  font-size: 16px;
  line-height: 1.6;
}

.emphasized-move-text {
  font-weight: bold;
  font-size: 19px;
  color: #1a1918;
  margin: 0;
}

.log-scrollable {
  max-height: 140px;
  overflow-y: auto;
}

.history-log-row {
  padding: 6px 0;
  border-bottom: 1px solid #eeebe5;
  font-size: 15px;
}

.highlighted-latest {
  font-weight: bold;
  color: #000000;
}

.fallback-empty-text {
  color: #a19a8e;
  font-style: italic;
}
</style>