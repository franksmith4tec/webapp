import { createRouter as createIonicRouter, createWebHistory } from '@ionic/vue-router'

const routes = [
  {
    path: '/',
    redirect: '/home'
  },

  {
    path: '/home',
    name: 'Home',
    component: () => import('@/pages/HomePage.vue')
  },


  {
    path: '/information',
    name: 'Information',
    component: () => import('@/pages/InformationPage.vue')
  },

  {
    path: '/cardtwo',
    name: 'cardtwo',
    component: () => import('@/pages/CardTwo.vue')
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