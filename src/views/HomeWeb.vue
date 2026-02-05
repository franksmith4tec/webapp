<template>
  <div class="contact-app">
    <!-- Your existing web view code here -->
    <div class="app-header">
      <h1>Contact Form (Web)</h1>
      <p>Submit your contact information</p>
    </div>

    <div class="app-content">
      <div class="form-section">
        <div class="form-card">
          <h2>Add New Contact</h2>
          
          <form @submit.prevent="submitForm">
            <div class="input-group">
              <label>Full Name *</label>
              <input
                type="text"
                v-model="form.name"
                placeholder="Enter your name"
                :class="{ 'error': errors.name }"
                @input="clearError('name')"
              >
              <div v-if="errors.name" class="input-error">
                {{ errors.name }}
              </div>
            </div>

            <div class="input-group">
              <label>Email Address *</label>
              <input
                type="email"
                v-model="form.email"
                placeholder="Enter your email"
                :class="{ 'error': errors.email }"
                @input="clearError('email')"
              >
              <div v-if="errors.email" class="input-error">
                {{ errors.email }}
              </div>
            </div>

            <div class="input-group">
              <label>Phone Number (optional)</label>
              <input
                type="tel"
                v-model="form.phone"
                placeholder="Enter your phone"
                @input="clearError('phone')"
              >
            </div>

            <button 
              type="submit" 
              class="submit-btn"
              :disabled="submitting"
            >
              <span v-if="!submitting">Submit Contact</span>
              <span v-else>Submitting...</span>
            </button>
          </form>

          <div v-if="success" class="success-message">
            <div class="success-icon">✓</div>
            <div class="success-text">
              <h3>Success!</h3>
              <p>Thank you <strong>{{ submittedName }}</strong>! Contact saved.</p>
              <button @click="success = false" class="close-btn">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-toast">
      <div class="error-icon">!</div>
      <div class="error-text">{{ error }}</div>
      <button @click="error = ''" class="error-close">×</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

// Reuse the same logic as before
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

const showError = (message) => {
  error.value = message
  setTimeout(() => {
    error.value = ''
  }, 5000)
}

const submitForm = async () => {
  if (!validateForm()) {
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
      
      setTimeout(() => {
        success.value = false
      }, 5000)
    } else {
      showError(data.error || 'Failed to submit contact')
    }
  } catch (err) {
    showError('Network error. Please check your connection.')
    console.error('Submit error:', err)
  } finally {
    submitting.value = false
  }
}
</script>


<style scoped>
.contact-app {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.app-header {
  text-align: center;
  margin-bottom: 40px;
}

.app-header h1 {
  color: #2c3e50;
  margin-bottom: 10px;
  font-size: 2.5rem;
}

.app-header p {
  color: #7f8c8d;
  font-size: 1.1rem;
}

.app-content {
  display: block;
}

/* Form Styles */
.form-card {
  background: white;
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.form-card h2 {
  color: #2c3e50;
  margin-bottom: 25px;
  font-size: 1.5rem;
  text-align: center;
}

.input-group {
  margin-bottom: 25px;
}

.input-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.input-group input {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.input-group input:focus {
  outline: none;
  border-color: #3498db;
}

.input-group input.error {
  border-color: #e74c3c;
}

.input-error {
  color: #e74c3c;
  font-size: 14px;
  margin-top: 5px;
}

.submit-btn {
  width: 100%;
  padding: 15px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
  margin-top: 10px;
}

.submit-btn:hover:not(:disabled) {
  background: #2980b9;
}

.submit-btn:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

/* Success Message */
.success-message {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 6px;
  padding: 20px;
  margin-top: 25px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
}

.success-icon {
  background: #28a745;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 18px;
  flex-shrink: 0;
}

.success-text {
  flex: 1;
}

.success-text h3 {
  color: #155724;
  margin: 0 0 10px 0;
  font-size: 1.2rem;
}

.success-text p {
  color: #155724;
  margin: 0 0 15px 0;
}

.close-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.close-btn:hover {
  background: #218838;
}

/* Error Toast */
.error-toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 6px;
  padding: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  max-width: 400px;
  animation: slideIn 0.3s ease;
  z-index: 1000;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.error-icon {
  background: #dc3545;
  color: white;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  flex-shrink: 0;
}

.error-text {
  color: #721c24;
  flex: 1;
  font-size: 14px;
}

.error-close {
  background: none;
  border: none;
  color: #721c24;
  font-size: 20px;
  cursor: pointer;
  padding: 0;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.error-close:hover {
  color: #dc3545;
}

/* Responsive */
@media (max-width: 600px) {
  .contact-app {
    padding: 15px;
  }
  
  .app-header h1 {
    font-size: 2rem;
  }
  
  .form-card {
    padding: 20px;
  }
  
  .error-toast {
    left: 15px;
    right: 15px;
    max-width: none;
    bottom: 15px;
  }
}
</style>