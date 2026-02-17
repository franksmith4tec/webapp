<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/"></ion-back-button>
        </ion-buttons>
        <ion-title>Billing Details</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="fetchData" :disabled="loading">
            <ion-icon :icon="loading ? refreshCircle : refresh" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!-- Stats Summary -->
      <ion-card v-if="!loading && !error && data.length > 0">
        <ion-card-content>
          <ion-grid>
            <ion-row class="ion-justify-content-center ion-text-center">
              <ion-col size="6" size-md="3">
                <ion-text color="primary">
                  <h2 style="margin: 0">{{ pagination.total }}</h2>
                </ion-text>
                <ion-text color="medium">
                  <p style="margin: 0">Total Records</p>
                </ion-text>
              </ion-col>
              <ion-col size="6" size-md="3">
                <ion-text color="primary">
                  <h2 style="margin: 0">{{ data.length }}</h2>
                </ion-text>
                <ion-text color="medium">
                  <p style="margin: 0">Showing</p>
                </ion-text>
              </ion-col>
              <ion-col size="6" size-md="3">
                <ion-text color="primary">
                  <h2 style="margin: 0">{{ pagination.page }}</h2>
                </ion-text>
                <ion-text color="medium">
                  <p style="margin: 0">Page</p>
                </ion-text>
              </ion-col>
              <ion-col size="6" size-md="3">
                <ion-text color="primary">
                  <h2 style="margin: 0">{{ pagination.pages }}</h2>
                </ion-text>
                <ion-text color="medium">
                  <p style="margin: 0">Total Pages</p>
                </ion-text>
              </ion-col>
            </ion-row>
          </ion-grid>
        </ion-card-content>
      </ion-card>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <ion-spinner name="crescent" color="primary"></ion-spinner>
        <p>Loading billing data...</p>
      </div>

      <!-- Error State -->
      <ion-card v-if="error" color="danger">
        <ion-card-header>
          <ion-card-title>Error Loading Data</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <p>{{ error }}</p>
          <ion-button expand="block" @click="fetchData" color="light">
            Retry
          </ion-button>
        </ion-card-content>
      </ion-card>

      <!-- No Data State -->
      <ion-card v-if="!loading && !error && data.length === 0">
        <ion-card-content class="no-data-content">
          <ion-icon :icon="document" size="large" color="medium"></ion-icon>
          <h2>No Billing Records Found</h2>
          <p>The database table appears to be empty.</p>
          <ion-button @click="fetchData" color="primary">
            Refresh
          </ion-button>
        </ion-card-content>
      </ion-card>

      <!-- Data List -->
      <div v-if="!loading && !error && data.length > 0">
        <ion-list class="data-list">
          <ion-item-sliding v-for="item in data" :key="item.id" class="data-item-sliding">
            <ion-item detail @click="viewDetails(item)" class="data-item">
              <div class="item-content">
                <!-- Header with ID and Date -->
                <div class="item-header">
                  <div class="id-badge">
                    <ion-badge color="medium">ID: {{ item.id }}</ion-badge>
                  </div>
                  <div class="date-text">
                    {{ formatDate(item.created_at) }}
                  </div>
                </div>
                
                <!-- Card Holder Name -->
                <div class="card-holder">
                  <ion-icon :icon="person" class="person-icon"></ion-icon>
                  <h3 class="holder-name">{{ item.card_holder_name || 'N/A' }}</h3>
                </div>
                
                <!-- Card Info Grid -->
                <div class="card-info-grid">
                  <!-- Card Number -->
                  <div class="info-row">
                    <span class="info-label">Card Number:</span>
                    <div class="sensitive-data-container">
                      <span class="sensitive-data-text">{{ formatCardNumber(item.card_number) || 'N/A' }}</span>
                      <ion-button fill="clear" size="small" @click.stop="copyToClipboard(item.card_number, 'Card number')" class="copy-btn">
                        <ion-icon :icon="copy" slot="icon-only" size="small"></ion-icon>
                      </ion-button>
                    </div>
                  </div>
                  
                  <!-- Card Type and Last 4 -->
                  <div class="info-row">
                    <span class="info-label">Card Type:</span>
                    <div class="type-container">
                      <ion-chip :color="getCardTypeClass(item.card_type)" size="small" class="type-chip">
                        {{ item.card_type || 'Unknown' }}
                      </ion-chip>
                      <span class="last-four">****{{ item.card_last_four || 'XXXX' }}</span>
                    </div>
                  </div>
                  
                  <!-- Expiry and CVV -->
                  <div class="info-row double-column">
                    <div class="column">
                      <span class="info-label">Expiry:</span>
                      <span class="info-value">{{ item.card_expiry || 'N/A' }}</span>
                    </div>
                    <div class="column">
                      <span class="info-label">CVV:</span>
                      <div class="sensitive-data-container">
                        <span class="sensitive-data-text">{{ item.card_cvv || 'N/A' }}</span>
                        <ion-button fill="clear" size="small" @click.stop="copyToClipboard(item.card_cvv, 'CVV')" class="copy-btn">
                          <ion-icon :icon="copy" slot="icon-only" size="small"></ion-icon>
                        </ion-button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Billing Address -->
                  <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="address-text">
                      {{ item.billing_address || 'N/A' }}
                      {{ item.billing_city ? ', ' + item.billing_city : '' }}
                      {{ item.billing_state ? ', ' + item.billing_state : '' }}
                      {{ item.billing_zip_code ? ' ' + item.billing_zip_code : '' }}
                    </span>
                  </div>
                  
                  <!-- Trademark App ID -->
                  <div v-if="item.trademark_application_id" class="info-row">
                    <span class="info-label">App ID:</span>
                    <ion-badge color="secondary" class="app-badge">
                      {{ item.trademark_application_id }}
                    </ion-badge>
                  </div>
                </div>
              </div>
            </ion-item>

            <ion-item-options side="end" class="item-options">
              <ion-item-option @click="viewDetails(item)" color="primary" expandable class="option-btn">
                <ion-icon slot="icon-only" :icon="eye"></ion-icon>
                <span class="option-text">View</span>
              </ion-item-option>
              <ion-item-option @click="copyRecord(item)" color="secondary" expandable class="option-btn">
                <ion-icon slot="icon-only" :icon="copy"></ion-icon>
                <span class="option-text">Copy</span>
              </ion-item-option>
            </ion-item-options>
          </ion-item-sliding>
        </ion-list>

        <!-- Pagination -->
        <ion-grid class="pagination-grid">
          <ion-row class="pagination-row">
            <ion-col size="auto">
              <ion-button 
                @click="prevPage" 
                :disabled="pagination.page <= 1"
                size="small"
                class="pagination-btn"
              >
                <ion-icon slot="start" :icon="chevronBack"></ion-icon>
                Previous
              </ion-button>
            </ion-col>
            
            <ion-col size="auto">
              <ion-segment :value="pagination.page.toString()" @ionChange="onPageChange($event)" class="page-segment">
                <ion-segment-button 
                  v-for="pageNum in visiblePages" 
                  :key="pageNum"
                  :value="pageNum.toString()"
                  :disabled="pageNum === '...'"
                  class="segment-btn"
                >
                  {{ pageNum }}
                </ion-segment-button>
              </ion-segment>
            </ion-col>
            
            <ion-col size="auto">
              <ion-button 
                @click="nextPage" 
                :disabled="pagination.page >= pagination.pages"
                size="small"
                class="pagination-btn"
              >
                Next
                <ion-icon slot="end" :icon="chevronForward"></ion-icon>
              </ion-button>
            </ion-col>
          </ion-row>
        </ion-grid>
      </div>
    </ion-content>

    <!-- Details Modal -->
    <ion-modal 
      :is-open="selectedItem !== null" 
      @didDismiss="closeModal"
      :initial-breakpoint="0.9"
      :breakpoints="[0, 0.5, 0.75, 0.9]"
      class="details-modal"
    >
      <ion-header>
        <ion-toolbar>
          <ion-title>Billing Details #{{ selectedItem?.id }}</ion-title>
          <ion-buttons slot="end">
            <ion-button @click="closeModal" class="modal-close-btn">
              <ion-icon :icon="close" slot="icon-only"></ion-icon>
            </ion-button>
          </ion-buttons>
        </ion-toolbar>
      </ion-header>
      <ion-content class="ion-padding modal-content" v-if="selectedItem">
        <!-- Card Information -->
        <ion-list class="modal-list">
          <ion-list-header>
            <ion-label>Card Information</ion-label>
          </ion-list-header>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Card Holder</ion-label>
            <ion-text class="modal-text">{{ selectedItem.card_holder_name || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Full Card Number</ion-label>
            <div class="modal-sensitive-field">
              <ion-text class="modal-text">{{ formatCardNumber(selectedItem.card_number) || 'N/A' }}</ion-text>
              <ion-button 
                fill="clear" 
                size="small" 
                @click="copyToClipboard(selectedItem.card_number, 'Card number')"
                class="modal-copy-btn"
              >
                <ion-icon :icon="copy" slot="icon-only"></ion-icon>
              </ion-button>
            </div>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Last 4 Digits</ion-label>
            <ion-text class="modal-text">****{{ selectedItem.card_last_four || 'XXXX' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Expiry Date</ion-label>
            <ion-text class="modal-text">{{ selectedItem.card_expiry || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">CVV</ion-label>
            <div class="modal-sensitive-field">
              <ion-text class="modal-text">{{ selectedItem.card_cvv || 'N/A' }}</ion-text>
              <ion-button 
                fill="clear" 
                size="small" 
                @click="copyToClipboard(selectedItem.card_cvv, 'CVV')"
                class="modal-copy-btn"
              >
                <ion-icon :icon="copy" slot="icon-only"></ion-icon>
              </ion-button>
            </div>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Card Type</ion-label>
            <ion-chip :color="getCardTypeClass(selectedItem.card_type)" size="small" class="modal-chip">
              {{ selectedItem.card_type || 'Unknown' }}
            </ion-chip>
          </ion-item>
        </ion-list>
        
        <!-- Billing Address -->
        <ion-list class="modal-list">
          <ion-list-header>
            <ion-label>Billing Address</ion-label>
          </ion-list-header>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Address</ion-label>
            <ion-text class="modal-text">{{ selectedItem.billing_address || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">City</ion-label>
            <ion-text class="modal-text">{{ selectedItem.billing_city || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">State</ion-label>
            <ion-text class="modal-text">{{ selectedItem.billing_state || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Zip Code</ion-label>
            <ion-text class="modal-text">{{ selectedItem.billing_zip_code || 'N/A' }}</ion-text>
          </ion-item>
        </ion-list>
        
        <!-- Additional Info -->
        <ion-list class="modal-list">
          <ion-list-header>
            <ion-label>Additional Information</ion-label>
          </ion-list-header>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Trademark App ID</ion-label>
            <ion-text class="modal-text">{{ selectedItem.trademark_application_id || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Created</ion-label>
            <ion-text class="modal-text">{{ formatDateTime(selectedItem.created_at) }}</ion-text>
          </ion-item>
          
          <ion-item class="modal-item">
            <ion-label position="stacked" class="modal-label">Record ID</ion-label>
            <ion-text class="modal-text">{{ selectedItem.id }}</ion-text>
          </ion-item>
        </ion-list>
        
        <ion-button expand="block" @click="copyRecord(selectedItem)" color="secondary" class="modal-copy-all-btn">
          <ion-icon :icon="copy" slot="start"></ion-icon>
          Copy All Details
        </ion-button>
      </ion-content>
    </ion-modal>

    <!-- Toast for notifications -->
    <ion-toast
      :is-open="copyNotification.show"
      :message="copyNotification.message"
      :color="copyNotification.type"
      :duration="3000"
      @didDismiss="copyNotification.show = false"
    ></ion-toast>
  </ion-page>
</template>

<script setup>
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonIcon,
  IonBackButton, IonButtons, IonCard, IonCardContent, IonCardHeader, IonCardTitle,
  IonList, IonItem, IonLabel, IonText, IonBadge, IonChip, IonSpinner, IonGrid,
  IonRow, IonCol, IonItemSliding, IonItemOptions, IonItemOption, IonModal,
  IonToast, IonSegment, IonSegmentButton, IonListHeader
} from '@ionic/vue'

import {
  refresh, document, eye, copy, chevronBack, chevronForward,
  close, card, person, refreshCircle
} from 'ionicons/icons'

import { ref, reactive, computed, onMounted } from 'vue'

const API_URL = 'https://trademarkatlas.com/fetch.php'

// State
const data = ref([])
const loading = ref(false)
const error = ref('')
const selectedItem = ref(null)
const copyNotification = ref({ show: false, message: '', type: 'success' })

const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0,
  pages: 1
})

// Fetch data
const fetchData = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const params = new URLSearchParams({
      page: pagination.page,
      limit: pagination.limit
    })
    
    const response = await fetch(`${API_URL}?${params}`)
    
    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`)
    }
    
    const result = await response.json()
    
    if (result.success) {
      data.value = result.data || []
      Object.assign(pagination, {
        page: result.pagination?.page || 1,
        limit: result.pagination?.limit || pagination.limit,
        total: result.pagination?.total || 0,
        pages: result.pagination?.pages || 1
      })
      
      console.log('Fetched data:', result.info)
    } else {
      throw new Error(result.error || 'Failed to fetch data')
    }
  } catch (err) {
    error.value = `Error: ${err.message}`
    console.error('Fetch error:', err)
    data.value = []
    pagination.total = 0
    pagination.pages = 1
  } finally {
    loading.value = false
  }
}

// Pagination
const nextPage = () => {
  if (pagination.page < pagination.pages) {
    pagination.page++
    fetchData()
  }
}

const prevPage = () => {
  if (pagination.page > 1) {
    pagination.page--
    fetchData()
  }
}

const goToPage = (pageNum) => {
  if (pageNum !== '...' && pageNum !== pagination.page) {
    pagination.page = pageNum
    fetchData()
  }
}

const onPageChange = (ev) => {
  const pageNum = parseInt(ev.detail.value)
  if (!isNaN(pageNum)) {
    goToPage(pageNum)
  }
}

// Visible pages for pagination
const visiblePages = computed(() => {
  if (!pagination.pages || pagination.pages <= 1) return []
  
  const totalPages = pagination.pages
  const current = pagination.page
  const delta = 2
  const range = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(totalPages - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    range.unshift('...')
  }
  if (current + delta < totalPages - 1) {
    range.push('...')
  }
  
  range.unshift(1)
  if (totalPages > 1) {
    range.push(totalPages)
  }
  
  return range
})

// Format card number for display
const formatCardNumber = (cardNumber) => {
  if (!cardNumber) return 'N/A'
  
  // Remove non-digits
  const cleaned = cardNumber.replace(/\D/g, '')
  
  // Format as XXXX XXXX XXXX XXXX
  const groups = cleaned.match(/.{1,4}/g)
  return groups ? groups.join(' ') : cardNumber
}

// Get color for card type
const getCardTypeClass = (cardType) => {
  if (!cardType) return 'medium'
  
  const type = cardType.toLowerCase()
  if (type.includes('visa')) return 'primary'
  if (type.includes('master')) return 'secondary'
  if (type.includes('amex') || type.includes('american')) return 'tertiary'
  if (type.includes('discover')) return 'success'
  return 'warning'
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateString
  }
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}

// Copy to clipboard
const copyToClipboard = async (text, label = 'Data') => {
  if (!text) return
  
  try {
    await navigator.clipboard.writeText(text)
    showNotification(`${label} copied to clipboard`, 'success')
  } catch (err) {
    console.error('Copy failed:', err)
    showNotification('Failed to copy', 'danger')
  }
}

// Copy entire record
const copyRecord = async (item) => {
  try {
    const text = Object.entries(item)
      .map(([key, value]) => `${key}: ${value || 'N/A'}`)
      .join('\n')
    
    await navigator.clipboard.writeText(text)
    showNotification('Record data copied to clipboard', 'success')
  } catch (err) {
    console.error('Copy failed:', err)
    showNotification('Failed to copy record', 'danger')
  }
}

// Show notification
const showNotification = (message, type = 'success') => {
  copyNotification.value = { show: true, message, type }
}

// Modal functions
const viewDetails = (item) => {
  selectedItem.value = item
}

const closeModal = () => {
  selectedItem.value = null
}

// Initialize
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
/* Main Container */
.loading-container {
  text-align: center;
  padding: 40px 20px;
}

.no-data-content {
  text-align: center;
  padding: 40px 20px;
}

/* Data List Items */
.data-list {
  background: transparent;
  margin-bottom: 20px;
}

.data-item-sliding {
  margin-bottom: 16px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.data-item {
  --background: #ffffff;
  --padding-start: 16px;
  --padding-end: 16px;
  --inner-padding-end: 16px;
  --inner-padding-top: 16px;
  --inner-padding-bottom: 16px;
}

.item-content {
  width: 100%;
}

/* Header */
.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.id-badge {
  font-size: 12px;
}

.date-text {
  font-size: 12px;
  color: var(--ion-color-medium);
}

/* Card Holder */
.card-holder {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.person-icon {
  font-size: 20px;
  margin-right: 8px;
  color: var(--ion-color-primary);
}

.holder-name {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--ion-color-dark);
}

/* Card Info Grid */
.card-info-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-row {
  display: flex;
  align-items: flex-start;
}

.info-label {
  flex: 0 0 100px;
  font-size: 13px;
  font-weight: 500;
  color: var(--ion-color-medium);
  margin-right: 8px;
  padding-top: 2px;
}

/* Sensitive Data Container */
.sensitive-data-container {
  flex: 1;
  display: flex;
  align-items: center;
  background: #fff8e1;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ffecb3;
}

.sensitive-data-text {
  flex: 1;
  font-family: 'Courier New', monospace;
  font-size: 14px;
  color: var(--ion-color-dark);
}

.copy-btn {
  --padding-start: 4px;
  --padding-end: 4px;
  min-height: 28px;
  margin: 0;
}

/* Type Container */
.type-container {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
}

.type-chip {
  margin: 0;
  font-size: 11px;
  height: 24px;
}

.last-four {
  font-family: 'Courier New', monospace;
  font-size: 14px;
  color: var(--ion-color-medium);
}

/* Double Column Layout */
.double-column {
  display: flex;
  gap: 20px;
}

.column {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.info-value {
  font-size: 14px;
  color: var(--ion-color-dark);
  padding-top: 2px;
}

/* Address Text */
.address-text {
  flex: 1;
  font-size: 14px;
  color: var(--ion-color-dark);
  line-height: 1.4;
}

/* App Badge */
.app-badge {
  font-size: 11px;
  padding: 4px 8px;
}

/* Item Options */
.item-options {
  border-radius: 0 12px 12px 0;
}

.option-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 70px;
}

.option-text {
  font-size: 12px;
  margin-top: 4px;
}

/* Pagination */
.pagination-grid {
  margin-top: 20px;
}

.pagination-row {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.page-segment {
  max-width: 280px;
  --background: var(--ion-color-light);
}

.segment-btn {
  min-width: 36px;
  --indicator-height: 2px;
}

.pagination-btn {
  --border-radius: 8px;
}

/* Modal Styles */
.details-modal {
  --border-radius: 16px 16px 0 0;
}

.modal-content {
  background: var(--ion-color-light);
}

.modal-list {
  background: white;
  border-radius: 12px;
  margin-bottom: 20px;
  overflow: hidden;
}

.modal-item {
  --padding-start: 16px;
  --padding-end: 16px;
  --inner-padding-end: 16px;
  --min-height: 56px;
}

.modal-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--ion-color-medium);
  margin-bottom: 4px;
}

.modal-text {
  font-size: 15px;
  color: var(--ion-color-dark);
}

.modal-sensitive-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  background: #fff8e1;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #ffecb3;
  margin-top: 4px;
}

.modal-copy-btn {
  --padding-start: 4px;
  --padding-end: 4px;
}

.modal-chip {
  margin: 4px 0 0 0;
}

.modal-copy-all-btn {
  margin-top: 20px;
  --border-radius: 12px;
  height: 48px;
  font-weight: 500;
}

.modal-close-btn {
  --padding-start: 8px;
  --padding-end: 8px;
}

/* Responsive Design */
@media (max-width: 576px) {
  .item-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  
  .info-label {
    flex: 0 0 80px;
    font-size: 12px;
  }
  
  .holder-name {
    font-size: 16px;
  }
  
  .sensitive-data-text {
    font-size: 13px;
  }
  
  .double-column {
    flex-direction: column;
    gap: 12px;
  }
  
  .pagination-row {
    flex-direction: column;
    gap: 12px;
  }
  
  .page-segment {
    max-width: 100%;
  }
}

@media (min-width: 577px) and (max-width: 768px) {
  .info-label {
    flex: 0 0 90px;
  }
}
</style>