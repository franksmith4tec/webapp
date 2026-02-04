<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Contact Manager</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <h1>Mobile Contact Form</h1>
      <p>Submit your contact information</p>
      
      <ion-card>
        <ion-card-header>
          <ion-card-title>Add Contact</ion-card-title>
        </ion-card-header>
        
        <ion-card-content>
          <ion-item>
            <ion-label position="stacked">Full Name</ion-label>
            <ion-input v-model="form.name" placeholder="Enter your name"></ion-input>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Email Address</ion-label>
            <ion-input v-model="form.email" type="email" placeholder="Enter your email"></ion-input>
          </ion-item>
          
          <ion-button expand="block" @click="submitForm" class="ion-margin-top">
            Submit Contact
          </ion-button>
        </ion-card-content>
      </ion-card>
      
      <div v-if="submitted" class="ion-margin-top">
        <ion-card color="success">
          <ion-card-content>
            Thank you {{ form.name }}! Your contact has been submitted.
          </ion-card-content>
        </ion-card>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
  IonPage, IonHeader, IonToolbar, IonTitle,
  IonContent, IonCard, IonCardHeader, IonCardTitle,
  IonCardContent, IonItem, IonLabel, IonInput,
  IonButton
} from '@ionic/vue'

const form = reactive({
  name: '',
  email: ''
})

const submitted = ref(false)

const submitForm = () => {
  if (!form.name || !form.email) {
    alert('Please fill in all fields')
    return
  }
  
  console.log('Submitting:', form)
  submitted.value = true
  
  // In real app, you would send to API here
  // fetch('http://your-api.com/api.php', {...})
}
</script>

<style scoped>
h1 {
  text-align: center;
  color: #3880ff;
}

p {
  text-align: center;
  color: #666;
  margin-bottom: 30px;
}
</style>
