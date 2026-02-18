<template>
  <ion-page>
    <!-- Modern Header with Blur Effect -->
    <ion-header class="modern-header" mode="ios" :class="{ 'header-elevated': isScrolled }">
      <ion-toolbar class="modern-toolbar">
        <ion-buttons slot="start">
          <ion-menu-button class="menu-button" color="dark"></ion-menu-button>
        </ion-buttons>
        
        <ion-title class="modern-title">
          <span class="title-gradient">Trademark Atlas</span>
        </ion-title>
        
        <ion-buttons slot="end">
          <ion-button class="icon-button" @click="goToAbout">
            <ion-icon :icon="personOutline" slot="icon-only"></ion-icon>
          </ion-button>
          <ion-button class="icon-button" @click="goToInformation">
            <ion-icon :icon="settingsOutline" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>
    
    <ion-content 
      class="modern-content" 
      :class="{ 'content-padded': !isScrolled }"
      @ionScroll="onScroll"
      :scroll-events="true"
    >
      <!-- Hero Section with Gradient -->
      <div class="hero-section">
        <div class="hero-backdrop"></div>
        <div class="hero-content">
          <div class="logo-wrapper">
            <img :src="logo" alt="Trademark Atlas" class="hero-logo" />
          </div>
          <h1 class="hero-title">Welcome Back!</h1>
          <p class="hero-subtitle">Register your trademark and protect your brand</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card" v-for="stat in stats" :key="stat.label">
          <div class="stat-icon" :style="{ background: stat.gradient }">
            <ion-icon :icon="stat.icon"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <!-- Login Form Card -->
      <div class="form-card">
        <h2 class="form-title">Sign In</h2>
        <p class="form-subtitle">Access your trademark portfolio</p>
        
        <ion-list class="modern-list">
          <ion-item class="modern-item" lines="none">
            <ion-icon :icon="mailOutline" slot="start" class="input-icon"></ion-icon>
            <ion-input 
              placeholder="Username or Email" 
              class="modern-input"
              @ionFocus="onInputFocus"
              @ionBlur="onInputBlur"
            ></ion-input>
          </ion-item>
          
          <ion-item class="modern-item" lines="none">
            <ion-icon :icon="lockClosedOutline" slot="start" class="input-icon"></ion-icon>
            <ion-input 
              type="password" 
              placeholder="Password" 
              class="modern-input"
              @ionFocus="onInputFocus"
              @ionBlur="onInputBlur"
            ></ion-input>
            <ion-button slot="end" fill="clear" class="password-toggle">
              <ion-icon :icon="eyeOutline"></ion-icon>
            </ion-button>
          </ion-item>
        </ion-list>

        <div class="form-options">
          <ion-checkbox label-placement="end">Remember me</ion-checkbox>
          <ion-button fill="clear" class="forgot-link" @click="showForgotPassword">
            Forgot Password?
          </ion-button>
        </div>

        <ion-button 
          expand="block" 
          class="login-button" 
          @click="showAlert"
        >
          <span>Sign In</span>
          <ion-icon :icon="arrowForward" slot="end"></ion-icon>
        </ion-button>

        <div class="signup-prompt">
          <span>Don't have an account?</span>
          <ion-button fill="clear" class="signup-link" @click="goToAbout">
            Sign Up
          </ion-button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="actions-grid">
        <div class="action-card" @click="goToAbout">
          <div class="action-icon about-icon">
            <ion-icon :icon="informationCircleOutline"></ion-icon>
          </div>
          <span class="action-label">About</span>
        </div>
        
        <div class="action-card" @click="goToInformation">
          <div class="action-icon info-icon">
            <ion-icon :icon="documentTextOutline"></ion-icon>
          </div>
          <span class="action-label">Information</span>
        </div>
        
        <div class="action-card" @click="showQuickHelp">
          <div class="action-icon help-icon">
            <ion-icon :icon="helpCircleOutline"></ion-icon>
          </div>
          <span class="action-label">Help</span>
        </div>
      </div>

      <!-- Info Card -->
      <div class="info-card">
        <div class="info-card-header">
          <ion-icon :icon="timeOutline" class="info-icon"></ion-icon>
          <h3>Quick Info</h3>
        </div>
        <div class="info-card-content">
          <p>Protect your brand identity with our comprehensive trademark registration service.</p>
          <div class="info-stats">
            <div class="info-stat-item">
              <span class="info-stat-value">10k+</span>
              <span class="info-stat-label">Trademarks</span>
            </div>
            <div class="info-stat-item">
              <span class="info-stat-value">150+</span>
              <span class="info-stat-label">Countries</span>
            </div>
            <div class="info-stat-item">
              <span class="info-stat-value">24/7</span>
              <span class="info-stat-label">Support</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Sheet Modal for Forgot Password -->
      <ion-modal
        :is-open="forgotModalOpen"
        @didDismiss="forgotModalOpen = false"
        class="bottom-sheet-modal"
        :breakpoints="[0, 0.4]"
        :initial-breakpoint="0.4"
      >
        <div class="modal-content">
          <div class="modal-handle"></div>
          <h3>Reset Password</h3>
          <p>Enter your email to receive reset instructions</p>
          <ion-item class="modal-input-item" lines="none">
            <ion-icon :icon="mailOutline" slot="start"></ion-icon>
            <ion-input placeholder="Email address"></ion-input>
          </ion-item>
          <ion-button expand="block" class="modal-button">Send Reset Link</ion-button>
        </div>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script>
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, 
  IonContent, IonButton, IonIcon, IonButtons,
  IonList, IonItem, IonLabel, IonInput,
  IonCard, IonCardHeader, IonCardTitle, IonCardContent,
  IonMenuButton, IonCheckbox, IonModal,
  alertController 
} from '@ionic/vue'
import { 
  home, 
  arrowForward, 
  alert,
  personOutline,
  settingsOutline,
  mailOutline,
  lockClosedOutline,
  eyeOutline,
  informationCircleOutline,
  documentTextOutline,
  helpCircleOutline,
  timeOutline,
  shieldOutline,
  globeOutline,
  headsetOutline
} from 'ionicons/icons'
import logo from '../assets/logo.svg'

export default {
  name: 'HomePage',
  components: {
    IonPage, IonHeader, IonToolbar, IonTitle, 
    IonContent, IonButton, IonIcon, IonButtons,
    IonList, IonItem, IonLabel, IonInput,
    IonCard, IonCardHeader, IonCardTitle, IonCardContent,
    IonMenuButton, IonCheckbox, IonModal
  },
  data() {
    return {
      // Icons
      home, arrowForward, alert, personOutline, settingsOutline,
      mailOutline, lockClosedOutline, eyeOutline,
      informationCircleOutline, documentTextOutline, helpCircleOutline,
      timeOutline, shieldOutline, globeOutline, headsetOutline,
      logo,
      
      // State
      isScrolled: false,
      forgotModalOpen: false,
      
      // Stats
      stats: [
        { icon: shieldOutline, label: 'Protected', value: '1,234', gradient: 'linear-gradient(135deg, #667eea, #764ba2)' },
        { icon: globeOutline, label: 'Countries', value: '150+', gradient: 'linear-gradient(135deg, #f093fb, #f5576c)' },
        { icon: headsetOutline, label: 'Support', value: '24/7', gradient: 'linear-gradient(135deg, #4facfe, #00f2fe)' }
      ]
    }
  },
  methods: {
    onScroll(ev) {
      this.isScrolled = ev.detail.scrollTop > 10
    },
    
    onInputFocus(ev) {
      ev.target.closest('.modern-item').classList.add('item-focused')
    },
    
    onInputBlur(ev) {
      ev.target.closest('.modern-item').classList.remove('item-focused')
    },
    
    async showAlert() {
      const alert = await alertController.create({
        header: 'Welcome!',
        message: 'Sign in functionality would be implemented here.',
        buttons: ['OK'],
        cssClass: 'modern-alert'
      })
      await alert.present()
    },
    
    showForgotPassword() {
      this.forgotModalOpen = true
    },
    
    showQuickHelp() {
      this.showAlert()
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

<style scoped>
/* Modern iOS-inspired Design System */
.modern-header {
  --background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid transparent;
  transition: all 0.3s ease;
}

.header-elevated {
  border-bottom-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.modern-toolbar {
  --background: transparent;
  --border-width: 0;
  --min-height: 60px;
}

.menu-button {
  --color: #333;
  font-size: 24px;
}

.modern-title {
  font-weight: 600;
  font-size: 18px;
  letter-spacing: -0.3px;
}

.title-gradient {
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.icon-button {
  --padding-start: 8px;
  --padding-end: 8px;
  --border-radius: 50%;
  width: 40px;
  height: 40px;
  --background: rgba(0, 0, 0, 0.03);
  transition: all 0.2s ease;
}

.icon-button:hover {
  --background: rgba(102, 126, 234, 0.1);
  transform: scale(1.05);
}

/* Hero Section */
.hero-section {
  position: relative;
  padding: 40px 20px 60px;
  margin-bottom: 20px;
  overflow: hidden;
}

.hero-backdrop {
  position: absolute;
  top: -50%;
  left: -50%;
  right: -50%;
  bottom: -50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
  animation: rotate 20s linear infinite;
  opacity: 0.1;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-content {
  position: relative;
  z-index: 1;
  text-align: center;
}

.logo-wrapper {
  display: inline-block;
  padding: 20px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 30px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.hero-logo{
    width: 85%;
    object-fit: contain;
    height: 35px;
}

.hero-title {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px;
  background: linear-gradient(135deg, #333, #667eea);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 16px;
  color: #666;
  margin: 0;
  max-width: 300px;
  margin: 0 auto;
  line-height: 1.5;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 0 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 20px;
  padding: 16px 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 18px;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
}

.stat-label {
  font-size: 11px;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Form Card */
.form-card {
  background: white;
  border-radius: 30px;
  padding: 24px 20px;
  margin: 0 16px 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.form-title {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 4px;
  color: #333;
}

.form-subtitle {
  font-size: 14px;
  color: #999;
  margin: 0 0 24px;
}

.modern-list {
  background: transparent;
  margin-bottom: 16px;
}

.modern-item {
  --background: #f8f9fa;
  --border-radius: 16px;
  --padding-start: 16px;
  --padding-end: 16px;
  --inner-padding-end: 0;
  margin-bottom: 12px;
  border-radius: 16px;
  border: 1px solid transparent;
  transition: all 0.3s ease;
}

.modern-item.item-focused {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  --background: white;
}

.input-icon {
  color: #999;
  font-size: 20px;
  margin-right: 12px;
}

.modern-input {
  --padding-start: 0;
  --placeholder-color: #999;
  --placeholder-opacity: 1;
  font-size: 15px;
}

.password-toggle {
  --color: #999;
  --padding-start: 8px;
  --padding-end: 8px;
  margin: 0;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.forgot-link {
  --color: #667eea;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
}

.login-button {
  --background: linear-gradient(135deg, #667eea, #764ba2);
  --border-radius: 16px;
  --padding-top: 16px;
  --padding-bottom: 16px;
  margin-bottom: 20px;
  font-weight: 600;
  font-size: 16px;
  text-transform: none;
  letter-spacing: 0.5px;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
}

.signup-prompt {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  color: #666;
}

.signup-link {
  --color: #667eea;
  font-weight: 600;
  margin: 0;
  padding: 0;
}

/* Quick Actions */
.actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 0 16px;
  margin-bottom: 24px;
}

.action-card {
  background: white;
  border-radius: 20px;
  padding: 16px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.about-icon {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.info-icon {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

.help-icon {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.action-label {
  font-size: 13px;
  font-weight: 500;
  color: #666;
}

/* Info Card */
.info-card {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 30px;
  padding: 24px;
  margin: 0 16px 30px;
  color: white;
  box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
}

.info-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.info-card-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.info-card-header .info-icon {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.info-card-content p {
  margin: 0 0 20px;
  font-size: 14px;
  line-height: 1.6;
  opacity: 0.9;
}

.info-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  text-align: center;
}

.info-stat-value {
  display: block;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 4px;
}

.info-stat-label {
  font-size: 11px;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Modal */
.bottom-sheet-modal {
  --height: auto;
  --border-radius: 30px 30px 0 0;
}

.modal-content {
  padding: 20px;
  background: white;
  border-radius: 30px 30px 0 0;
}

.modal-handle {
  width: 40px;
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
  margin: 0 auto 20px;
}

.modal-content h3 {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 700;
  color: #333;
}

.modal-content p {
  margin: 0 0 20px;
  font-size: 14px;
  color: #999;
}

.modal-input-item {
  --background: #f8f9fa;
  --border-radius: 16px;
  margin-bottom: 20px;
  border-radius: 16px;
}

.modal-button {
  --background: linear-gradient(135deg, #667eea, #764ba2);
  --border-radius: 16px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  margin: 0;
  text-transform: none;
  font-weight: 600;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .modern-header {
    --background: rgba(28, 28, 30, 0.8);
  }
  
  .hero-title {
    background: linear-gradient(135deg, #fff, #667eea);
    -webkit-background-clip: text;
  }
  
  .stat-card,
  .form-card,
  .action-card {
    background: #1c1c1e;
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .stat-value,
  .form-title,
  .action-label {
    color: white;
  }
  
  .modern-item {
    --background: #2c2c2e;
  }
  
  .modern-item.item-focused {
    --background: #3a3a3c;
  }
  
  .modal-content {
    background: #1c1c1e;
  }
  
  .modal-content h3 {
    color: white;
  }
}

/* Responsive */
@media (max-width: 380px) {
  .hero-title {
    font-size: 28px;
  }
  
  .stats-grid {
    gap: 8px;
  }
  
  .stat-card {
    padding: 12px 8px;
  }
  
  .stat-icon {
    width: 32px;
    height: 32px;
    font-size: 16px;
  }
  
  .stat-value {
    font-size: 16px;
  }
  
  .stat-label {
    font-size: 10px;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-section,
.stats-grid,
.form-card,
.actions-grid,
.info-card {
  animation: fadeInUp 0.6s ease-out forwards;
}

.stats-grid {
  animation-delay: 0.1s;
}

.form-card {
  animation-delay: 0.2s;
}

.actions-grid {
  animation-delay: 0.3s;
}

.info-card {
  animation-delay: 0.4s;
}
</style>