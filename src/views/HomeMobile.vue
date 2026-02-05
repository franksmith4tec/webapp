<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Contact Form</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="refreshForm">
            <ion-icon slot="icon-only" :icon="refresh"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!-- Success Toast -->
      <ion-toast
        :is-open="success"
        message="Contact saved successfully!"
        :duration="3000"
        @didDismiss="success = false"
      ></ion-toast>

      <!-- Error Toast -->
      <ion-toast
        :is-open="!!error"
        :message="error"
        color="danger"
        :duration="3000"
        @didDismiss="error = ''"
      ></ion-toast>

      <div class="form-container">
        <div class="header-section">
          <h2 class="welcome-title">Welcome!</h2>
          <p class="welcome-subtitle">Submit your contact information</p>
        </div>

        <ion-card class="form-card">
          <ion-card-header>
            <ion-card-title>Add New Contact</ion-card-title>
            <ion-card-subtitle>Fill in your details below</ion-card-subtitle>
          </ion-card-header>

          <ion-card-content>
            <form @submit.prevent="submitForm">
              <!-- Name Field -->
              <ion-item :class="{ 'ion-invalid': errors.name }">
                <ion-label position="floating">
                  Full Name <span class="required">*</span>
                </ion-label>
                <ion-input
                  type="text"
                  v-model="form.name"
                  placeholder="Enter your name"
                  @ionInput="clearError('name')"
                  required
                ></ion-input>
                <ion-note slot="error" v-if="errors.name">
                  {{ errors.name }}
                </ion-note>
              </ion-item>

              <!-- Email Field -->
              <ion-item :class="{ 'ion-invalid': errors.email }" class="ion-margin-top">
                <ion-label position="floating">
                  Email Address <span class="required">*</span>
                </ion-label>
                <ion-input
                  type="email"
                  v-model="form.email"
                  placeholder="Enter your email"
                  @ionInput="clearError('email')"
                  required
                ></ion-input>
                <ion-note slot="error" v-if="errors.email">
                  {{ errors.email }}
                </ion-note>
              </ion-item>

              <!-- Phone Field -->
              <ion-item class="ion-margin-top">
                <ion-label position="floating">Phone Number (optional)</ion-label>
                <ion-input
                  type="tel"
                  v-model="form.phone"
                  placeholder="Enter your phone number"
                  @ionInput="clearError('phone')"
                ></ion-input>
              </ion-item>

              <!-- Submit Button -->
              <ion-button
                expand="block"
                type="submit"
                class="ion-margin-top"
                :disabled="submitting"
              >
                <ion-spinner v-if="submitting" name="crescent"></ion-spinner>
                <span v-else>Submit Contact</span>
              </ion-button>
            </form>

            <!-- Success Details Card -->
            <ion-card v-if="success && submittedName" color="success" class="ion-margin-top">
              <ion-card-header>
                <ion-card-title>
                  <ion-icon :icon="checkmarkCircle" class="ion-margin-end"></ion-icon>
                  Success!
                </ion-card-title>
              </ion-card-header>
              <ion-card-content>
                <div class="success-content">
                  <p>
                    Thank you <strong>{{ submittedName }}</strong>!<br>
                    Your contact information has been saved.
                  </p>
                </div>
                <ion-button
                  expand="block"
                  fill="clear"
                  @click="success = false"
                  class="ion-margin-top"
                >
                  Close
                </ion-button>
              </ion-card-content>
            </ion-card>
          </ion-card-content>
        </ion-card>

        <!-- Information Section -->
        <div class="info-section ion-margin-top">
          <ion-card>
            <ion-card-header>
              <ion-card-title>
                <ion-icon :icon="informationCircle" class="ion-margin-end"></ion-icon>
                Information
              </ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <p>Fields marked with <span class="required">*</span> are required.</p>
              <p>Your contact information will be stored securely.</p>
            </ion-card-content>
          </ion-card>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonItem,
  IonLabel,
  IonInput,
  IonButton,
  IonSpinner,
  IonIcon,
  IonNote,
  IonToast,
  IonButtons,
  toastController
} from '@ionic/vue'
import { checkmarkCircle, informationCircle, refresh } from 'ionicons/icons'

// Reuse the same logic
const API_URL = 'http://localhost/api/api.php'

const form = reactive({
  name: '',
  email: '',
  phone: ''
})

const errors = reactive({})
const submitting = ref(false)
const success = ref(false)
const submittedName = ref('')
const error = ref('')

const clearError = (field) => {
  if (errors[field]) {
    errors[field] = ''
  }
}

const validateForm = () => {
  let isValid = true
  errors.name = ''
  errors.email = ''
  
  if (!form.name.trim()) {
    errors.name = 'Name is required'
    isValid = false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email.trim()) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!emailRegex.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }
  
  return isValid
}

const refreshForm = () => {
  form.name = ''
  form.email = ''
  form.phone = ''
  success.value = false
  submittedName.value = ''
  error.value = ''
}

const showToast = async (message, color = 'success') => {
  const toast = await toastController.create({
    message,
    duration: 3000,
    color,
    position: 'bottom'
  })
  await toast.present()
}

const submitForm = async () => {
  if (!validateForm()) {
    await showToast('Please check the form for errors', 'danger')
    return
  }
  
  submitting.value = true
  error.value = ''
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: form.name.trim(),
        email: form.email.trim(),
        phone: form.phone.trim() || ''
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      success.value = true
      submittedName.value = form.name
      
      form.name = ''
      form.email = ''
      form.phone = ''
      
      await showToast('Contact saved successfully!')
    } else {
      error.value = data.error || 'Failed to submit contact'
      await showToast(error.value, 'danger')
    }
  } catch (err) {
    error.value = 'Network error. Please check your connection.'
    await showToast(error.value, 'danger')
    console.error('Submit error:', err)
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.form-container {
  max-width: 500px;
  margin: 0 auto;
}

.header-section {
  text-align: center;
  margin-bottom: 2rem;
}

.welcome-title {
  color: var(--ion-color-primary);
  margin-bottom: 0.5rem;
  font-size: 1.8rem;
  font-weight: 600;
}

.welcome-subtitle {
  color: var(--ion-color-medium);
  font-size: 1rem;
  margin: 0;
}

.form-card {
  margin: 0;
  border-radius: 16px;
}

.required {
  color: var(--ion-color-danger);
}

.success-content {
  text-align: center;
  padding: 1rem 0;
}

.success-content p {
  color: var(--ion-color-dark);
  margin: 0;
  line-height: 1.5;
}

.info-section ion-card {
  margin: 0;
}

.ion-margin-end {
  margin-right: 8px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .form-card {
    --background: var(--ion-card-background, var(--ion-item-background, var(--ion-background-color)));
  }
  
  .welcome-subtitle {
    color: var(--ion-color-medium-shade);
  }
}
</style>