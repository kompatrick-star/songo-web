import { createRouter, createWebHashHistory } from 'vue-router'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/MenuView.vue')
    },
    {
      path: '/credits',
      name: 'credits',
      component: () => import('../views/CreditsView.vue')
    },
    {
      path: '/jouer',
      name: 'jouer',
      component: () => import('../views/GameView.vue')
    }
  ]
})

export default router