import { createRouter as createIonicRouter, createWebHistory } from '@ionic/vue-router'

const routes = [
  {
    path: '/home',
    redirect: '/home'
  },

  {
    path: '/home',
    name: 'Home',
    component: () => import('@/pages/HomePage.vue')
  },



  {
    path: '/',
    name: 'Information',
    component: () => import('@/pages/InformationPage.vue')
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('@/pages/AboutPage.vue')
  }
]

const router = createIonicRouter({
  history: createWebHistory(),
  routes
})

export default router