<template>
  <div class="menu-container">
    <div class="menu-card">
      <div class="logo-area">
        <div class="songo-icon">🪘</div>
        <h1>SONGO</h1>
        <p class="subtitle">Le jeu traditionnel de stratégie</p>
      </div>

      <div class="form-group">
        <label for="player1">Joueur 1 (Camp SUD - Commence)</label>
        <input type="text" id="player1" v-model="joueurSud" placeholder="Nom du joueur 1">
      </div>

      <div class="form-group">
        <label for="player2">Joueur 2 (Camp NORD)</label>
        <input type="text" id="player2" v-model="joueurNord" placeholder="Nom du joueur 2">
      </div>

      <button class="btn-play" @click="commencerPartie">
        Lancer la partie 🚀
      </button>

      <div class="footer-links">
        <router-link to="/credits" class="lnk-credits">Concepteur de l'application</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      joueurSud: 'Joueur 1',
      joueurNord: 'Joueur 2'
    }
  },
  methods: {
    commencerPartie() {
      localStorage.setItem('songo_player_sud', this.joueurSud);
      localStorage.setItem('songo_player_nord', this.joueurNord);
      this.$router.push('/jouer');
    }
  }
}
</script>

<style scoped>
.menu-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f4f7f6;
  width: 100vw;
}
.menu-card {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 400px;
  text-align: center;
}
.songo-icon { font-size: 4rem; margin-bottom: 10px; }
h1 { margin: 0; font-size: 2.5rem; letter-spacing: 2px; color: #2c3e50; }
.subtitle { color: #7f8c8d; margin-top: 5px; margin-bottom: 30px; }
.form-group { text-align: left; margin-bottom: 20px; }
label { display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; color: #34495e; }
input { width: 100%; padding: 12px; border: 2px solid #ecf0f1; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
.btn-play { width: 100%; padding: 14px; background: #2ecc71; color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 10px; }
.btn-play:hover { background: #27ae60; }
.footer-links { margin-top: 25px; }
.lnk-credits { color: #3498db; text-decoration: none; font-size: 0.9rem; }
</style>
