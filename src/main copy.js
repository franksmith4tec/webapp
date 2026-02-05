import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Import platform detection utility
import { getPlatform } from './utils/platform'

// Ionic imports
import { IonicVue } from '@ionic/vue'

// Import Ionic CSS
import '@ionic/vue/css/core.css'
import '@ionic/vue/css/normalize.css'
import '@ionic/vue/css/structure.css'
import '@ionic/vue/css/typography.css'
import '@ionic/vue/css/padding.css'
import '@ionic/vue/css/float-elements.css'
import '@ionic/vue/css/text-alignment.css'
import '@ionic/vue/css/text-transformation.css'
import '@ionic/vue/css/flex-utils.css'
import '@ionic/vue/css/display.css'

const app = createApp(App)

// Use Ionic only for mobile platforms
const platform = getPlatform()
const isMobile = platform === 'android' || platform === 'ios'

if (isMobile) {
    app.use(IonicVue)
}

app.use(createPinia())
app.use(router)

app.mount('#app')