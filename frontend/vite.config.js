import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  // Cette ligne est cruciale pour que ton jeu fonctionne dans un sous-dossier comme /songo/
  base: './'
})