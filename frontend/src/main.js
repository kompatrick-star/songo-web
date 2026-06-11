import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js' // On cible TRÈS précisément le fichier index.js

const app = createApp(App)

app.use(router)
app.mount('#app')