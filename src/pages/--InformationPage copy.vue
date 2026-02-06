<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="secondary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/home"></ion-back-button>
        </ion-buttons>
        <ion-title>Information Page</ion-title>
      </ion-toolbar>
    </ion-header>
    
    <ion-content class="ion-padding">
      <div class="ion-text-center">
        <ion-icon :icon="informationCircle" size="large" color="secondary"></ion-icon>
        <h1>About This App</h1>
        <p>This is a simple Ionic Vue.js application</p>
      </div>
      
      <ion-card>
        <ion-card-header>
          <ion-card-title>App Details</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <ion-list>
            <ion-item>
              <ion-label>Framework</ion-label>
              <ion-text color="primary">Vue.js 3</ion-text>
            </ion-item>
            
            <ion-item>
              <ion-label>UI Framework</ion-label>
              <ion-text color="primary">Ionic 7</ion-text>
            </ion-item>
            
            <ion-item>
              <ion-label>Pages</ion-label>
              <ion-badge color="success">2</ion-badge>
            </ion-item>
          </ion-list>
        </ion-card-content>
      </ion-card>
      
      <ion-card>
        <ion-card-header>
          <ion-card-title>Features</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <ion-list>
            <ion-item v-for="feature in features" :key="feature.id">
              <ion-icon :icon="feature.icon" slot="start" :color="feature.color"></ion-icon>
              <ion-label>{{ feature.name }}</ion-label>
            </ion-item>
          </ion-list>
        </ion-card-content>
      </ion-card>
      
      <ion-button expand="block" @click="goToHome">
        <ion-icon slot="start" :icon="home"></ion-icon>
        Back to Home
      </ion-button>
    </ion-content>
  </ion-page>
</template>

<script>
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, 
  IonContent, IonButton, IonIcon, IonBackButton, 
  IonButtons, IonCard, IonCardHeader, IonCardTitle, 
  IonCardContent, IonList, IonItem, IonLabel, 
  IonText, IonBadge, alertController 
} from '@ionic/vue'


import { 
  informationCircle, home, checkmarkCircle, 
  phonePortrait, flash, colorPalette 
} from 'ionicons/icons'

export default {
  name: 'AboutPage',
  components: {
    IonPage, IonHeader, IonToolbar, IonTitle, 
    IonContent, IonButton, IonIcon, IonBackButton, 
    IonButtons, IonCard, IonCardHeader, IonCardTitle, 
    IonCardContent, IonList, IonItem, IonLabel, 
    IonText, IonBadge 
  },
  data() {
    return {
      informationCircle, home, checkmarkCircle, 
      phonePortrait, flash, colorPalette,
      features: [
        { id: 1, name: 'Vue 3 Composition API', icon: flash, color: 'warning' },
        { id: 2, name: 'Ionic Components', icon: colorPalette, color: 'success' },
        { id: 3, name: 'Mobile Responsive', icon: phonePortrait, color: 'primary' },
        { id: 4, name: 'Easy Navigation', icon: checkmarkCircle, color: 'tertiary' }
      ]
    }
  },
  methods: {
    goToHome() {
      this.$router.push('/home')
    }
  }
}
</script>

<style>
.ion-text-center {
  text-align: center;
  margin: 2rem 0;
}
</style>