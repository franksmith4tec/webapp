<template>
  <ion-page>
    <!-- PIN Overlay (shown when app is locked) -->
    <div v-if="showPinScreen" class="pin-overlay">
      <div class="pin-container">
        <!-- App Logo -->
        <div class="pin-logo-section">
          <img :src="logo" alt="Trademark Atlas" class="pin-logo" />

            <div v-if="pinMode === 'enter'"  style="display: inline-block;">
          
              <h2 style="color: #000 !important;display: inline-block;">Authentication Required</h2>
          
        </div>
          
           
        
          <p>{{ pinMessage }}</p>
            <div v-if="pinMode === 'enter'" class="master-hint"  >
          <ion-icon :icon="keyOutline"></ion-icon>
        </div>
          <!-- Hidden master PIN indicator (only shows when master PIN is used) -->
          <div v-if="showMasterIndicator" class="master-indicator">
            <ion-icon :icon="shieldCheckmarkOutline"></ion-icon>
            <span>Master PIN activated</span>
          </div>
        </div>

        <!-- PIN Dots -->
        <div class="pin-dots" ref="pinDots">
          <div 
            v-for="i in 4" 
            :key="i"
            class="pin-dot"
            :class="{ 'pin-dot-filled': pin.length >= i }"
          ></div>
        </div>

        <!-- Error Message -->
        <div v-if="pinError" class="pin-error">
          {{ pinError }}
        </div>

        <!-- Number Pad -->
        <div class="pin-number-pad">
          <div 
            v-for="n in 9" 
            :key="n" 
            class="pin-number"
            @click="addPinNumber(n)"
          >
            {{ n }}
          </div>
          <div class="pin-number" @click="clearPin">C</div>
          <div class="pin-number" @click="addPinNumber(0)">0</div>
          <div class="pin-number" @click="removePinNumber">⌫</div>
        </div>

        <!-- Settings Button (only in enter mode) -->
        <div v-if="pinMode === 'enter'" class="pin-settings-link">
          <ion-button fill="clear" @click="showPinSettings = true">
            <ion-icon :icon="settingsOutline" slot="start"></ion-icon>
            Change PIN
          </ion-button>
        </div>

        <!-- Master PIN Hint (subtle, only visible to those who know) -->
        <!-- <div class="master-hint" v-if="pinMode === 'enter'">
          <ion-icon :icon="keyOutline"></ion-icon>
        </div> -->
      </div>
    </div>

    <!-- Main App Content -->
    <div class="app-content" :class="{ 'app-content-hidden': showPinScreen }">
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
            <!-- PIN Settings Button -->
            <ion-button class="icon-button" @click="showPinSettings = true">
              <ion-icon :icon="keyOutline" slot="icon-only"></ion-icon>
            </ion-button>
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
        fullscreen
      >
        <!-- Hero Section with Gradient -->
        <div class="hero-section">
          <div class="hero-backdrop"></div>
          <div class="hero-content">
            <div class="logo-wrapper">
              <img :src="logo" alt="Trademark Atlas" class="hero-logo" />
            </div>
            <h1 class="hero-title">Your Workspace, Simplified</h1>
            <p class="hero-subtitle">Designed to help you stay productive, focused, and in control — effortlessly.</p>
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
      </ion-content>
    </div>

    <!-- PIN Settings Modal -->
    <ion-modal
      :is-open="showPinSettings"
      @didDismiss="showPinSettings = false"
      class="pin-settings-modal"
    >
      <div class="modal-content">
        <div class="modal-handle"></div>
        <h3>PIN Settings</h3>
        
        <!-- Toggle PIN -->
        <ion-item class="settings-item" lines="none">
          <ion-icon :icon="lockClosedOutline" slot="start"></ion-icon>
          <ion-label>Enable PIN</ion-label>
          <ion-toggle 
            :checked="pinEnabled" 
            @ionChange="togglePin($event)"
          ></ion-toggle>
        </ion-item>

        <!-- Change PIN (only if enabled) -->
        <template v-if="pinEnabled">
          <div class="change-pin-section">
            <h4>Change PIN</h4>
            
            <ion-item class="settings-item" lines="none">
              <ion-label position="stacked">Current PIN</ion-label>
              <ion-input 
                type="password" 
                v-model="currentPin"
                maxlength="4"
                placeholder="Enter current PIN"
                @keyup.enter="changePin"
              ></ion-input>
            </ion-item>

            <ion-item class="settings-item" lines="none">
              <ion-label position="stacked">New PIN</ion-label>
              <ion-input 
                type="password" 
                v-model="newPin"
                maxlength="4"
                placeholder="Enter new PIN"
                @keyup.enter="changePin"
              ></ion-input>
            </ion-item>

            <ion-item class="settings-item" lines="none">
              <ion-label position="stacked">Confirm PIN</ion-label>
              <ion-input 
                type="password" 
                v-model="confirmPin"
                maxlength="4"
                placeholder="Confirm new PIN"
                @keyup.enter="changePin"
              ></ion-input>
            </ion-item>

            <ion-button 
              expand="block" 
              class="save-pin-btn"
              @click="changePin"
            >
              Update PIN
            </ion-button>
          </div>
        </template>

        <!-- Master PIN Reset Option (only visible when master PIN is used) -->
        <div v-if="isMasterPinUser" class="master-pin-section">
          <div class="master-divider">
            <span>Master Developer Options</span>
          </div>
          
          <ion-item class="settings-item master-item" lines="none" button @click="resetUserPin">
            <ion-icon :icon="refreshOutline" slot="start" class="master-icon"></ion-icon>
            <ion-label>Reset User PIN (Force Setup)</ion-label>
          </ion-item>
          
          <ion-item class="settings-item master-item" lines="none" button @click="viewUserPin">
            <ion-icon :icon="eyeOutline" slot="start" class="master-icon"></ion-icon>
            <ion-label>View Current User PIN</ion-label>
          </ion-item>
          
          <div v-if="showUserPin" class="user-pin-display">
            <strong>Current User PIN:</strong> {{ userPinValue }}
          </div>
        </div>

        <ion-button 
          fill="clear" 
          class="close-settings-btn"
          @click="showPinSettings = false"
        >
          Close
        </ion-button>
      </div>
    </ion-modal>

    <!-- Success Toast -->
    <ion-toast
      :is-open="showSuccessToast"
      :message="successMessage"
      @didDismiss="showSuccessToast = false"
      :duration="2000"
      class="success-toast"
      position="top"
    ></ion-toast>

    <!-- Error Toast -->
    <ion-toast
      :is-open="showErrorToast"
      :message="errorMessage"
      @didDismiss="showErrorToast = false"
      :duration="2000"
      class="error-toast"
      position="top"
    ></ion-toast>
  </ion-page>
</template>

<script>
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, 
  IonContent, IonButton, IonIcon, IonButtons,
  IonItem, IonLabel, IonInput,
  IonCard, IonCardHeader, IonCardTitle, IonCardContent,
  IonMenuButton, IonModal, IonToggle, IonToast,
  alertController 
} from '@ionic/vue'
import { 
  home, 
  arrowForward, 
  alert,
  personOutline,
  settingsOutline,
  keyOutline,
  mailOutline,
  lockClosedOutline,
  eyeOutline,
  informationCircleOutline,
  documentTextOutline,
  helpCircleOutline,
  timeOutline,
  shieldOutline,
  globeOutline,
  headsetOutline,
  shieldCheckmarkOutline,
  refreshOutline,
  trashOutline
} from 'ionicons/icons'
import logo from '../assets/logo.svg'

export default {
  name: 'HomePage',
  components: {
    IonPage, IonHeader, IonToolbar, IonTitle, 
    IonContent, IonButton, IonIcon, IonButtons,
    IonItem, IonLabel, IonInput,
    IonCard, IonCardHeader, IonCardTitle, IonCardContent,
    IonMenuButton, IonModal, IonToggle, IonToast
  },
  data() {
    return {
      // Icons
      home, arrowForward, alert, personOutline, settingsOutline, keyOutline,
      mailOutline, lockClosedOutline, eyeOutline,
      informationCircleOutline, documentTextOutline, helpCircleOutline,
      timeOutline, shieldOutline, globeOutline, headsetOutline,
      shieldCheckmarkOutline, refreshOutline, trashOutline,
      logo,
      
      // State
      isScrolled: false,
      
      // PIN related
      showPinScreen: true,
      pinMode: 'enter', // 'enter', 'create', 'confirm'
      pin: '',
      tempPin: '',
      pinError: '',
      pinMessage: 'Enter PIN to continue',
      
      // Master PIN (change this to your desired master PIN)
      masterPin: '0437', // You can change this to any 4-digit code
      showMasterIndicator: false,
      isMasterPinUser: false,
      
      // PIN Settings
      showPinSettings: false,
      pinEnabled: false,
      currentPin: '',
      newPin: '',
      confirmPin: '',
      
      // Toast notifications
      showSuccessToast: false,
      showErrorToast: false,
      successMessage: '',
      errorMessage: '',
      
      // Master options
      showUserPin: false,
      userPinValue: '',
      
      // Stats
      stats: [
        { icon: shieldOutline, label: 'Protected', value: '1,234', gradient: 'linear-gradient(135deg, #667eea, #764ba2)' },
        { icon: globeOutline, label: 'Countries', value: '150+', gradient: 'linear-gradient(135deg, #f093fb, #f5576c)' },
       
      ]
    }
  },
  mounted() {
    this.checkPinStatus();
    // Force a resize event to ensure content renders properly
    setTimeout(() => {
      window.dispatchEvent(new Event('resize'));
    }, 100);
  },
  methods: {
    // Original methods
    onScroll(ev) {
      this.isScrolled = ev.detail.scrollTop > 10
    },
    
    async showAlert() {
      const alert = await alertController.create({
        header: 'Quick Help',
        message: 'This is a quick help message. For more information, visit the About or Information pages.',
        buttons: ['OK'],
        cssClass: 'modern-alert'
      })
      await alert.present()
    },
    
    showQuickHelp() {
      this.showAlert()
    },
    
    goToAbout() {
      this.$router.push('/about')
    },
    
    goToInformation() {
      this.$router.push('/information')
    },
    
    // PIN Methods
    checkPinStatus() {
      const storedPin = localStorage.getItem('app_pin');
      this.pinEnabled = storedPin !== null;
      
      if (storedPin) {
        this.pinMode = 'enter';
        this.pinMessage = 'Enter PIN to continue';
        this.showPinScreen = true;
      } else {
        this.pinMode = 'create';
        this.pinMessage = 'Create a new PIN';
        this.showPinScreen = true;
      }
    },
    
    addPinNumber(num) {
      if (this.pin.length < 4) {
        this.pin += num;
        this.pinError = '';
        
        if (this.pin.length === 4) {
          this.submitPin();
        }
      }
    },
    
    removePinNumber() {
      this.pin = this.pin.slice(0, -1);
      this.pinError = '';
    },
    
    clearPin() {
      this.pin = '';
      this.pinError = '';
    },
    
    submitPin() {
      if (this.pinMode === 'enter') {
        this.verifyPin();
      } else if (this.pinMode === 'create') {
        this.tempPin = this.pin;
        this.pinMode = 'confirm';
        this.pinMessage = 'Confirm your PIN';
        this.pin = '';
      } else if (this.pinMode === 'confirm') {
        if (this.pin === this.tempPin) {
          localStorage.setItem('app_pin', this.pin);
          this.pinEnabled = true;
          this.showPinScreen = false;
          this.pin = '';
          // Show success message
          this.successMessage = 'PIN created successfully!';
          this.showSuccessToast = true;
          // Force content to redraw
          this.$nextTick(() => {
            window.dispatchEvent(new Event('resize'));
          });
        } else {
          this.pinError = 'PINs do not match';
          this.pin = '';
          this.pinMode = 'create';
          this.pinMessage = 'Create a new PIN';
          this.shakePin();
        }
      }
    },
    
    verifyPin() {
      const storedPin = localStorage.getItem('app_pin');
      
      // Check if it's the master PIN
      if (this.pin === this.masterPin) {
        // Master PIN entered - grant access and enable developer mode
        this.showPinScreen = false;
        this.pin = '';
        this.isMasterPinUser = true;
        this.showMasterIndicator = true;
        
        // Show success message
        this.successMessage = 'Master PIN accepted. Developer mode active.';
        this.showSuccessToast = true;
        
        // Hide master indicator after 3 seconds
        setTimeout(() => {
          this.showMasterIndicator = false;
        }, 3000);
        
        // Force content to redraw
        this.$nextTick(() => {
          window.dispatchEvent(new Event('resize'));
        });
        
        return;
      }
      
      // Regular PIN verification
      if (this.pin === storedPin) {
        this.showPinScreen = false;
        this.pin = '';
        this.isMasterPinUser = false;
        // Force content to redraw
        this.$nextTick(() => {
          window.dispatchEvent(new Event('resize'));
        });
      } else {
        this.pinError = 'Wrong PIN';
        this.pin = '';
        this.shakePin();
      }
    },
    
    shakePin() {
      const dots = this.$refs.pinDots;
      if (dots) {
        dots.classList.add('pin-shake');
        setTimeout(() => dots.classList.remove('pin-shake'), 500);
      }
    },
    
    togglePin(event) {
      if (event.detail.checked) {
        this.pinMode = 'create';
        this.pinMessage = 'Create a new PIN';
        this.showPinSettings = false;
        this.showPinScreen = true;
        this.pin = '';
      } else {
        localStorage.removeItem('app_pin');
        this.pinEnabled = false;
        this.showPinScreen = false;
        this.successMessage = 'PIN disabled successfully';
        this.showSuccessToast = true;
      }
    },
    
    changePin() {
      // Validate inputs
      if (!this.currentPin || !this.newPin || !this.confirmPin) {
        this.errorMessage = 'Please fill all fields';
        this.showErrorToast = true;
        return;
      }
      
      if (this.newPin !== this.confirmPin) {
        this.errorMessage = 'New PINs do not match';
        this.showErrorToast = true;
        // Clear the new PIN fields
        this.newPin = '';
        this.confirmPin = '';
        return;
      }
      
      if (this.newPin.length !== 4) {
        this.errorMessage = 'PIN must be 4 digits';
        this.showErrorToast = true;
        this.newPin = '';
        this.confirmPin = '';
        return;
      }
      
      if (this.currentPin === this.newPin) {
        this.errorMessage = 'New PIN must be different from current PIN';
        this.showErrorToast = true;
        this.newPin = '';
        this.confirmPin = '';
        return;
      }
      
      const storedPin = localStorage.getItem('app_pin');
      
      // Allow master PIN to change user PIN without knowing current PIN
      if (this.isMasterPinUser) {
        // Master user can change PIN directly
        localStorage.setItem('app_pin', this.newPin);
        this.successMessage = 'PIN updated successfully (Master)';
        this.showSuccessToast = true;
        
        // Clear form
        this.currentPin = '';
        this.newPin = '';
        this.confirmPin = '';
        
        // Close modal after a short delay
        setTimeout(() => {
          this.showPinSettings = false;
        }, 1500);
        
        return;
      }
      
      // Regular PIN change verification
      if (this.currentPin === storedPin) {
        localStorage.setItem('app_pin', this.newPin);
        this.successMessage = 'PIN updated successfully!';
        this.showSuccessToast = true;
        
        // Clear form
        this.currentPin = '';
        this.newPin = '';
        this.confirmPin = '';
        
        // Close modal after a short delay
        setTimeout(() => {
          this.showPinSettings = false;
        }, 1500);
      } else {
        this.errorMessage = 'Current PIN is incorrect';
        this.showErrorToast = true;
        this.currentPin = '';
        this.newPin = '';
        this.confirmPin = '';
      }
    },
    
    // Master PIN methods
    resetUserPin() {
      localStorage.removeItem('app_pin');
      this.pinEnabled = false;
      this.successMessage = 'User PIN reset. App will now ask to create new PIN.';
      this.showSuccessToast = true;
      
      // Close settings and show PIN screen
      this.showPinSettings = false;
      this.showPinScreen = true;
      this.pinMode = 'create';
      this.pinMessage = 'Create a new PIN';
    },
    
    viewUserPin() {
      const storedPin = localStorage.getItem('app_pin');
      if (storedPin) {
        this.userPinValue = storedPin;
        this.showUserPin = true;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
          this.showUserPin = false;
        }, 5000);
      } else {
        this.errorMessage = 'No user PIN set';
        this.showErrorToast = true;
      }
    }
  }
}
</script>

<style scoped>
/* Add these new styles */
.master-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 10px;
  padding: 8px 12px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 30px;
  color: white;
  font-size: 14px;
  animation: fadeIn 0.3s;
}


  .modern-title[data-v-2d1117dc] {
    font-weight: 600;
    font-size: 18px;
    letter-spacing: -0.3px;
    padding: 10px !important;
    text-align: left;
}

.master-indicator ion-icon {
  font-size: 18px;
}

.master-hint {
  margin-top: 15px;
  opacity: 0.3;
  font-size: 20px;
  color: #999;
}

.master-pin-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px dashed #667eea;
}

.master-divider {
  text-align: center;
  margin-bottom: 15px;
  position: relative;
}

.master-divider span {
  background: white;
  padding: 0 10px;
  color: #667eea;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.master-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  z-index: -1;
}

.master-item {
  --background: rgba(102, 126, 234, 0.1);
  margin-bottom: 8px;
  border: 1px solid rgba(102, 126, 234, 0.3);
}

.master-icon {
  color: #667eea;
}

.user-pin-display {
  margin-top: 15px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 12px;
  text-align: center;
  font-size: 18px;
  border: 2px solid #667eea;
  animation: pulse 2s infinite;
}

.user-pin-display strong {
  color: #667eea;
  margin-right: 8px;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dark mode support for master section */
@media (prefers-color-scheme: dark) {
  .master-divider span {
    background: #1c1c1e;
    color: #667eea;
  }
  
  .master-item {
    --background: rgba(102, 126, 234, 0.2);
  }
  
  .user-pin-display {
    background: #2c2c2e;
    color: white;
  }
  
  .master-hint {
    color: #666;
  }
}

.success-toast {
  --background: #4caf50;
  --color: white;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
  font-weight: 500;
  text-align: center;
  --button-color: white;
  max-width: 300px;
  margin: 0 auto;
}

.error-toast {
  --background: #f5576c;
  --color: white;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
  font-weight: 500;
  text-align: center;
  --button-color: white;
  max-width: 300px;
  margin: 0 auto;
}

/* Dark mode support for toasts */
@media (prefers-color-scheme: dark) {
  .success-toast {
    --background: #2e7d32;
  }
  
  .error-toast {
    --background: #c62828;
  }
}


/* PIN Overlay Styles */
.pin-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.pin-container {
  background: white;
  width: 90%;
  max-width: 350px;
  border-radius: 30px;
  padding: 30px 20px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.pin-logo-section {
  margin-bottom: 30px;
}

.pin-logo {
  width: 250px;
margin-top: 10px;
  margin-bottom: 30px;
}

.pin-logo-section h2 {
  margin: 0;
  color: #333;
  font-size: 16px;
}

.pin-logo-section p {
  margin: 5px 0 0;
  color: #999;
  font-size: 14px;
}

.pin-dots {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}

.pin-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #e0e0e0;
  transition: all 0.3s;
}

.pin-dot-filled {
  background: linear-gradient(135deg, #667eea, #764ba2);
  transform: scale(1.2);
}

.pin-error {
  color: #f5576c;
  font-size: 14px;
  margin-bottom: 20px;
  min-height: 20px;
}

.pin-number-pad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.pin-number {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 50%;
  font-size: 24px;
  font-weight: 500;
  color: #333;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.pin-number:active {
  transform: scale(0.95);
  background: #e0e0e0;
}

.pin-settings-link {
  margin-top: 10px;
}

.pin-settings-link ion-button {
  --color: #999;
}

.pin-shake {
  animation: pinShake 0.5s;
}

@keyframes pinShake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* App Content */
.app-content {
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
}

/* Hide app content when PIN overlay is shown */
.app-content-hidden {
  opacity: 0;
  pointer-events: none;
  visibility: hidden;
}

/* Ensure ion-content takes full height */
ion-content {
  --height: 100%;
  --overflow: auto;
}

.modern-content {
  --background: #f8f9fa;
}

/* PIN Settings Modal */
.pin-settings-modal {
  --height: auto;
  --border-radius: 30px 30px 0 0;
}

.pin-settings-modal .modal-content {
  padding: 20px;
  background: white;
  border-radius: 30px 30px 0 0;
  max-height: 80vh;
  overflow-y: auto;
}

.pin-settings-modal h3 {
  margin: 0 0 20px;
  font-size: 20px;
  font-weight: 700;
  color: #333;
}

.pin-settings-modal h4 {
  margin: 20px 0 10px;
  font-size: 16px;
  color: #666;
}

.settings-item {
  --background: #f8f9fa;
  --border-radius: 12px;
  margin-bottom: 12px;
  border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
}

.change-pin-section {
  margin-top: 20px;
}

.save-pin-btn {
  --background: linear-gradient(135deg, #667eea, #764ba2);
  --border-radius: 12px;
  margin: 20px 0;
}

.close-settings-btn {
  --color: #999;
  margin-top: 10px;
}

/* Keep all your original styles below this line */
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
  padding:15px 10px 12px 10px;
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
  font-size: 24px;
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
  grid-template-columns: repeat(2, 1fr);
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
  .action-card {
    background: #1c1c1e;
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .stat-value,
  .action-label {
    color: white;
  }
  
  .modal-content {
    background: #1c1c1e;
  }
  
  .modal-content h3 {
    color: white;
  }

  .pin-container {
    background: #1c1c1e;
  }

  .pin-logo-section h2 {
    color: white;
  }

  .pin-number {
    background: #2c2c2e;
    color: white;
  }

  .pin-settings-modal .modal-content {
    background: #1c1c1e;
  }

  .pin-settings-modal h3,
  .pin-settings-modal h4 {
    color: white;
  }

  .settings-item {
    --background: #2c2c2e;
    --color: white;
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
.actions-grid,
.info-card {
  animation: fadeInUp 0.6s ease-out forwards;
}

.stats-grid {
  animation-delay: 0.1s;
}

.actions-grid {
  animation-delay: 0.2s;
}

.info-card {
  animation-delay: 0.3s;
}
</style>