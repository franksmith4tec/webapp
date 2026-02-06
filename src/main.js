import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Import Ionic Vue and router
import { IonicVue } from '@ionic/vue'
import '@ionic/vue-router' // This is important!

// Import Ionic CSS
import '@ionic/core/css/ionic.bundle.css'

const app = createApp(App)

// Use Ionic Vue
app.use(IonicVue)
app.use(router)

router.isReady().then(() => {
    app.mount('#app')
})