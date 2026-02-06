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
      <div v-if="loading" class="ion-text-center ion-padding">
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
        <ion-card-content class="ion-text-center">
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
        <ion-list>
          <ion-item-sliding v-for="item in data" :key="item.id">
            <ion-item detail @click="viewDetails(item)">
              <ion-label>
                <!-- Card Holder Name -->
                <h2>
                  <ion-icon :icon="person" slot="start" color="primary"></ion-icon>
                  {{ item.card_holder_name || 'N/A' }}
                </h2>
                
                <!-- Card Type and Last 4 Digits -->
                <p>
                  <ion-chip :color="getCardTypeClass(item.card_type)" size="small">
                    <ion-icon :icon="card" slot="start"></ion-icon>
                    {{ item.card_type || 'Unknown' }}
                  </ion-chip>
                  <ion-text color="medium" style="margin-left: 8px">
                    ****{{ item.card_last_four || 'XXXX' }}
                  </ion-text>
                </p>
                
                <!-- Full Card Number -->
                <p class="card-data">
                  <ion-text color="dark">
                    <strong>Card Number:</strong>
                  </ion-text>
                  <ion-text class="sensitive-data">
                    {{ formatCardNumber(item.card_number) || 'N/A' }}
                  </ion-text>
                  <ion-button fill="clear" size="small" @click.stop="copyToClipboard(item.card_number, 'Card number')">
                    <ion-icon :icon="copy" slot="icon-only" size="small"></ion-icon>
                  </ion-button>
                </p>
                
                <!-- Expiry Date -->
                <p class="card-data">
                  <ion-text color="dark">
                    <strong>Expiry:</strong>
                  </ion-text>
                  <ion-text>
                    {{ item.card_expiry || 'N/A' }}
                  </ion-text>
                </p>
                
                <!-- CVV -->
                <p class="card-data">
                  <ion-text color="dark">
                    <strong>CVV:</strong>
                  </ion-text>
                  <ion-text class="sensitive-data">
                    {{ item.card_cvv || 'N/A' }}
                  </ion-text>
                  <ion-button fill="clear" size="small" @click.stop="copyToClipboard(item.card_cvv, 'CVV')">
                    <ion-icon :icon="copy" slot="icon-only" size="small"></ion-icon>
                  </ion-button>
                </p>
                
                <!-- Billing Address -->
                <p class="card-data">
                  <ion-text color="dark">
                    <strong>Address:</strong>
                  </ion-text>
                  <ion-text color="medium">
                    {{ item.billing_address || 'N/A' }}
                    {{ item.billing_city ? ', ' + item.billing_city : '' }}
                    {{ item.billing_state ? ', ' + item.billing_state : '' }}
                    {{ item.billing_zip_code ? ' ' + item.billing_zip_code : '' }}
                  </ion-text>
                </p>
                
                <!-- Created Date and ID -->
                <p class="meta-data">
                  <ion-text color="medium">
                    {{ formatDate(item.created_at) }}
                  </ion-text>
                  <ion-badge color="medium">{{ item.id }}</ion-badge>
                  <ion-badge v-if="item.trademark_application_id" color="secondary">
                    App ID: {{ item.trademark_application_id }}
                  </ion-badge>
                </p>
              </ion-label>
            </ion-item>

            <ion-item-options side="end">
              <ion-item-option @click="viewDetails(item)" color="primary" expandable>
                <ion-icon slot="icon-only" :icon="eye"></ion-icon>
              </ion-item-option>
              <ion-item-option @click="copyRecord(item)" color="secondary" expandable>
                <ion-icon slot="icon-only" :icon="copy"></ion-icon>
              </ion-item-option>
            </ion-item-options>
          </ion-item-sliding>
        </ion-list>

        <!-- Pagination -->
        <ion-grid class="ion-padding">
          <ion-row class="ion-justify-content-center ion-align-items-center">
            <ion-col size="auto">
              <ion-button 
                @click="prevPage" 
                :disabled="pagination.page <= 1"
                size="small"
              >
                <ion-icon slot="start" :icon="chevronBack"></ion-icon>
                Previous
              </ion-button>
            </ion-col>
            
            <ion-col size="auto">
              <ion-segment :value="pagination.page.toString()" @ionChange="onPageChange($event)">
                <ion-segment-button 
                  v-for="pageNum in visiblePages" 
                  :key="pageNum"
                  :value="pageNum.toString()"
                  :disabled="pageNum === '...'"
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
    >
      <ion-header>
        <ion-toolbar>
          <ion-title>Billing Details #{{ selectedItem?.id }}</ion-title>
          <ion-buttons slot="end">
            <ion-button @click="closeModal">
              <ion-icon :icon="close" slot="icon-only"></ion-icon>
            </ion-button>
          </ion-buttons>
        </ion-toolbar>
      </ion-header>
      <ion-content class="ion-padding" v-if="selectedItem">
        <!-- Card Information -->
        <ion-list>
          <ion-list-header>
            <ion-label>Card Information</ion-label>
          </ion-list-header>
          
          <ion-item>
            <ion-label position="stacked">Card Holder</ion-label>
            <ion-text>{{ selectedItem.card_holder_name || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Full Card Number</ion-label>
            <div class="sensitive-field">
              <ion-text>{{ formatCardNumber(selectedItem.card_number) || 'N/A' }}</ion-text>
              <ion-button 
                fill="clear" 
                size="small" 
                @click="copyToClipboard(selectedItem.card_number, 'Card number')"
              >
                <ion-icon :icon="copy" slot="icon-only"></ion-icon>
              </ion-button>
            </div>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Last 4 Digits</ion-label>
            <ion-text>****{{ selectedItem.card_last_four || 'XXXX' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Expiry Date</ion-label>
            <ion-text>{{ selectedItem.card_expiry || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">CVV</ion-label>
            <div class="sensitive-field">
              <ion-text>{{ selectedItem.card_cvv || 'N/A' }}</ion-text>
              <ion-button 
                fill="clear" 
                size="small" 
                @click="copyToClipboard(selectedItem.card_cvv, 'CVV')"
              >
                <ion-icon :icon="copy" slot="icon-only"></ion-icon>
              </ion-button>
            </div>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Card Type</ion-label>
            <ion-chip :color="getCardTypeClass(selectedItem.card_type)" size="small">
              {{ selectedItem.card_type || 'Unknown' }}
            </ion-chip>
          </ion-item>
        </ion-list>
        
        <!-- Billing Address -->
        <ion-list>
          <ion-list-header>
            <ion-label>Billing Address</ion-label>
          </ion-list-header>
          
          <ion-item>
            <ion-label position="stacked">Address</ion-label>
            <ion-text>{{ selectedItem.billing_address || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">City</ion-label>
            <ion-text>{{ selectedItem.billing_city || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">State</ion-label>
            <ion-text>{{ selectedItem.billing_state || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Zip Code</ion-label>
            <ion-text>{{ selectedItem.billing_zip_code || 'N/A' }}</ion-text>
          </ion-item>
        </ion-list>
        
        <!-- Additional Info -->
        <ion-list>
          <ion-list-header>
            <ion-label>Additional Information</ion-label>
          </ion-list-header>
          
          <ion-item>
            <ion-label position="stacked">Trademark App ID</ion-label>
            <ion-text>{{ selectedItem.trademark_application_id || 'N/A' }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Created</ion-label>
            <ion-text>{{ formatDateTime(selectedItem.created_at) }}</ion-text>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Record ID</ion-label>
            <ion-text>{{ selectedItem.id }}</ion-text>
          </ion-item>
        </ion-list>
        
        <ion-button expand="block" @click="copyRecord(selectedItem)" color="secondary">
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
.sensitive-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  background: var(--ion-color-warning-tint);
  padding: 8px;
  border-radius: 8px;
  border: 1px solid var(--ion-color-warning-shade);
}

.sensitive-data {
  background: var(--ion-color-warning-tint);
  padding: 4px 8px;
  border-radius: 4px;
  font-family: monospace;
  border: 1px solid var(--ion-color-warning-shade);
}

ion-chip {
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
  font-weight: 600;
}

ion-segment {
  max-width: 300px;
}

ion-item-sliding {
  margin-bottom: 12px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--ion-color-light-shade);
}

ion-item-option {
  padding: 0 20px;
}

.card-data {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 4px 0;
  flex-wrap: wrap;
}

.card-data ion-text {
  font-size: 14px;
}

.meta-data {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  flex-wrap: wrap;
}

.meta-data ion-text {
  font-size: 12px;
}

.meta-data ion-badge {
  font-size: 10px;
  padding: 2px 6px;
}

h2 {
  display: flex;
  align-items: center;
  gap: 8px;
}

ion-item {
  --padding-start: 16px;
  --padding-end: 16px;
  --inner-padding-end: 16px;
}
</style>