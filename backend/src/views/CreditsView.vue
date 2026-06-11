<template>
  <div class="credits-container">
    <div class="credits-card">
      <h2>À propos de l'application</h2>
      <hr>
      <p>Cette application web moderne du jeu traditionnel <strong>Songo</strong> a été entièrement conçue et développée dans le cadre des projets d'ingénierie logicielle.</p>
      
      <div class="author-section">
        <span class="label">Développeur Principal & Designer :</span>
        <p class="author-name">K.O.M.</p>
      </div>

      <p class="rules-mention">Respect strict des règles de distribution anti-horaire, des sauts de cases (13+) et du système de solidarité algorithmique.</p>

      <router-link to="/" class="btn-back">⬅️ Retour au Menu</router-link>
    </div>
  </div>
</template>

<style scoped>
.credits-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f4f7f6;
  width: 100vw;
}
.credits-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  max-width: 450px;
  text-align: center;
}
h2 { color: #2c3e50; margin-bottom: 15px; }
hr { border: 0; height: 1px; background: #eee; margin-bottom: 20px; }
.author-section {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin: 20px 0;
}
.label { font-size: 0.85rem; color: #7f8c8d; text-transform: uppercase; }
.author-name { font-size: 1.4rem; font-weight: bold; color: #e67e22; margin: 5px 0 0 0; }
.rules-mention { font-size: 0.9rem; color: #95a5a6; font-style: italic; }
.btn-back {
  display: inline-block;
  margin-top: 20px;
  color: #3498db;
  text-decoration: none;
}
</style>