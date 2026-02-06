<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Home Page</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="goToAbout">
            About
            <ion-icon slot="end" :icon="arrowForward"></ion-icon>
          </ion-button>
        </ion-buttons>

        <ion-buttons slot="end">
          <ion-button @click="goToInformation">
            Information
            <ion-icon slot="end" :icon="arrowForward"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>

  
    </ion-header>
    
    <ion-content class="ion-padding">
      <div class="ion-text-center">
        <ion-icon :icon="home" size="large" color="primary"></ion-icon>
        <h1>Welcome Home!</h1>
        <p>This is the home page of your Ionic Vue app</p>
      </div>
      
      <ion-list>
        <ion-item>
          <ion-label>Username</ion-label>
          <ion-input placeholder="Enter username"></ion-input>
        </ion-item>
        
        <ion-item>
          <ion-label>Password</ion-label>
          <ion-input type="password" placeholder="Enter password"></ion-input>
        </ion-item>
      </ion-list>
      
      
      <ion-button expand="block" @click="showAlert">
        <ion-icon slot="start" :icon="alert"></ion-icon>
        Show Alert
      </ion-button>
      
      <ion-button expand="block" color="secondary" @click="goToAbout">
        Go to About Page
      </ion-button>

        <ion-button expand="block" color="secondary" @click="goToInformation">
        Go to Information Page
      </ion-button>
      
      <ion-card>
        <ion-card-header>
          <ion-card-title>Quick Info</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <p>This is a simple Ionic Vue app with 2 pages.</p>
          <p>Current page: <strong>Home</strong></p>
        </ion-card-content>
      </ion-card>
    </ion-content>
  </ion-page>
</template>

<script>
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, 
  IonContent, IonButton, IonIcon, IonButtons,
  IonList, IonItem, IonLabel, IonInput,
  IonCard, IonCardHeader, IonCardTitle, IonCardContent,
  alertController 
} from '@ionic/vue'
import { home, arrowForward, alert } from 'ionicons/icons'

export default {
  name: 'HomePage',
  components: {
    IonPage, IonHeader, IonToolbar, IonTitle, 
    IonContent, IonButton, IonIcon, IonButtons,
    IonList, IonItem, IonLabel, IonInput,
    IonCard, IonCardHeader, IonCardTitle, IonCardContent
  },
  data() {
    return {
      home, arrowForward, alert
    }
  },
  methods: {
    async showAlert() {
      const alert = await alertController.create({
        header: 'Hello!',
        message: 'Welcome to Ionic Vue!',
        buttons: ['OK']
      })
      
      await alert.present()
    },
    
    goToAbout() {
      this.$router.push('/about')
    },
     goToInformation() {
      this.$router.push('/information')
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