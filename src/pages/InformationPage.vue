<template>
  <ion-page>
    <!-- Modern Header with Blur Effect -->
    <ion-header class="modern-header" :class="{ 'header-elevated': isScrolled }" mode="ios">
      <ion-toolbar class="modern-toolbar">
        <ion-buttons slot="start">
          <ion-back-button 
            default-href="/" 
            text="" 
            class="modern-back-button"
            :icon="chevronBack"
          ></ion-back-button>
        </ion-buttons>
        
        <ion-title class="modern-title">
          <div class="title-container">
            <div class="title-icon-wrapper">
              <ion-icon :icon="card" class="title-icon"></ion-icon>
            </div>
            <div class="title-text-container">
              <h1 class="title-main">Billing</h1>
              <p class="title-sub">{{ data.length }} of {{ pagination.total }} records</p>
            </div>
          </div>
        </ion-title>
        
        <ion-buttons slot="end">
          <ion-button 
            @click="fetchData" 
            :disabled="loading"
            fill="clear"
            class="modern-icon-button"
          >
            <ion-icon 
              :icon="loading ? refreshCircle : refresh" 
              slot="icon-only"
              :class="{ 'rotate-animation': loading }"
            ></ion-icon>
          </ion-button>
          <ion-button 
            fill="clear"
            @click="toggleTheme"
            class="modern-icon-button"
          >
            <ion-icon :icon="themeIcon" slot="icon-only"></ion-icon>
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
      <!-- Quick Stats Cards -->
      <div v-if="!loading && !error && data.length > 0" class="stats-grid">
        <div class="stat-card" v-for="stat in stats" :key="stat.label">
          <div class="stat-icon-wrapper" :style="{ background: stat.gradient }">
            <ion-icon :icon="stat.icon" class="stat-icon"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <!-- Pull to Refresh Indicator -->
      <div v-if="isPullToRefresh" class="ptr-indicator">
        <ion-spinner name="crescent"></ion-spinner>
        <span>Pull to refresh</span>
      </div>

      <!-- Loading State with Skeleton -->
      <div v-if="loading" class="loading-container">
        <div class="skeleton-header"></div>
        <div class="skeleton-list">
          <div v-for="n in 5" :key="n" class="skeleton-item">
            <div class="skeleton-avatar"></div>
            <div class="skeleton-lines">
              <div class="skeleton-line" style="width: 60%"></div>
              <div class="skeleton-line" style="width: 40%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State with Illustration -->
      <div v-if="error" class="error-container">
        <div class="error-illustration">
          <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
            <path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="error-title">Something went wrong</h3>
        <p class="error-message">{{ error }}</p>
        <ion-button @click="fetchData" class="retry-button">
          <ion-icon :icon="refresh" slot="start"></ion-icon>
          Try Again
       </ion-button>
      </div>

      <!-- Empty State with Illustration -->
      <div v-if="!loading && !error && data.length === 0" class="empty-container">
        <div class="empty-illustration">
          <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="empty-title">No billing records</h3>
        <p class="empty-message">Your billing history will appear here</p>
        <ion-button @click="fetchData" fill="outline" class="refresh-button">
          <ion-icon :icon="refresh" slot="start"></ion-icon>
          Refresh
        </ion-button>
      </div>

      <!-- Data List with Modern Cards -->
      <div v-if="!loading && !error && data.length > 0" class="list-container">
        <div class="list-header">
          <span class="list-header-title">Recent Transactions</span>
          <ion-button fill="clear" size="small" @click="sortData">
            <ion-icon :icon="funnel" slot="start"></ion-icon>
            Filter
          </ion-button>
        </div>
        
        <div class="card-list">
          <div 
            v-for="(item, index) in data" 
            :key="item.id" 
            class="record-card"
            :class="{ 'card-expanded': expandedCard === item.id }"
            @click="toggleCardExpansion(item.id)"
          >
            <!-- Card Header -->
            <div class="card-header">
              <div class="card-header-left">
                <div class="card-avatar" :style="{ background: getGradientByType(item.card_type) }">
                  <span class="card-type-icon">{{ getCardTypeEmoji(item.card_type) }}</span>
                </div>
                <div class="card-info">
                  <h4 class="card-title">{{ item.card_holder_name || 'Unknown' }}</h4>
                  <p class="card-subtitle">
                    <span class="card-number">{{ formatCardNumberShort(item.card_number) }}</span>
                    <span class="card-badge" :class="getCardTypeClass(item.card_type)">
                      {{ getCardTypeAbbreviation(item.card_type) }}
                    </span>
                  </p>
                </div>
              </div>
              <div class="card-header-right">
                <span class="card-amount">{{ formatCurrency(item.amount) }}</span>
                <ion-icon 
                  :icon="expandedCard === item.id ? chevronUp : chevronDown" 
                  class="expand-icon"
                ></ion-icon>
              </div>
            </div>

            <!-- Expanded Content -->
            <div class="card-expanded-content" v-if="expandedCard === item.id">
              <div class="expanded-section">
                <div class="info-row">
                  <span class="info-label">
                    <ion-icon :icon="calendar" class="info-icon"></ion-icon>
                    Date
                  </span>
                  <span class="info-value">{{ formatDateTime(item.created_at) }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">
                    <ion-icon :icon="location" class="info-icon"></ion-icon>
                    Address
                  </span>
                  <span class="info-value">{{ formatAddressShort(item) }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">
                    <ion-icon :icon="key" class="info-icon"></ion-icon>
                    CVV
                  </span>
                  <span class="info-value sensitive-data" @click.stop="copyToClipboard(item.card_cvv, 'CVV')">
                    {{ item.card_cvv || '•••' }}
                    <ion-icon :icon="copyOutline" class="copy-small-icon"></ion-icon>
                  </span>
                </div>
              </div>

              <div class="card-actions">
                <ion-button 
                  fill="clear" 
                  size="small" 
                  @click.stop="showQuickView(item)"
                  class="card-action-button"
                >
                  <ion-icon :icon="eye" slot="start"></ion-icon>
                  Details
                </ion-button>
                <ion-button 
                  fill="clear" 
                  size="small" 
                  @click.stop="copyRecord(item)"
                  class="card-action-button"
                >
                  <ion-icon :icon="copy" slot="start"></ion-icon>
                  Copy
                </ion-button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modern Pagination -->
      <div v-if="!loading && !error && data.length > 0" class="pagination-container">
        <div class="pagination-info">
          Showing {{ (pagination.page - 1) * pagination.limit + 1 }} - 
          {{ Math.min(pagination.page * pagination.limit, pagination.total) }} 
          of {{ pagination.total }}
        </div>
        
        <div class="pagination-controls-modern">
          <ion-button 
            @click="prevPage" 
            :disabled="pagination.page <= 1"
            fill="clear"
            class="pagination-arrow"
          >
            <ion-icon :icon="chevronBack"></ion-icon>
          </ion-button>
          
          <div class="page-dots">
            <span 
              v-for="pageNum in visiblePages" 
              :key="pageNum"
              class="page-dot"
              :class="{ 
                'active': pageNum === pagination.page,
                'dot': pageNum !== '...'
              }"
              @click="goToPage(pageNum)"
            ></span>
          </div>
          
          <ion-button 
            @click="nextPage" 
            :disabled="pagination.page >= pagination.pages"
            fill="clear"
            class="pagination-arrow"
          >
            <ion-icon :icon="chevronForward"></ion-icon>
          </ion-button>
        </div>
      </div>

      <!-- Bottom Sheet Modal -->
      <ion-modal 
        :is-open="quickViewVisible" 
        @didDismiss="closeQuickView"
        class="bottom-sheet-modal"
        :breakpoints="[0, 0.5, 0.9]"
        :initial-breakpoint="0.5"
        handle-behavior="cycle"
      >
        <div class="bottom-sheet-content" v-if="selectedItem">
          <!-- Modal Header -->
          <div class="sheet-header">
            <div class="sheet-handle"></div>
            <div class="sheet-title-container">
              <div class="sheet-avatar" :style="{ background: getGradientByType(selectedItem.card_type) }">
                {{ getCardTypeEmoji(selectedItem.card_type) }}
              </div>
              <div class="sheet-title-info">
                <h2 class="sheet-title">{{ selectedItem.card_holder_name || 'Billing Details' }}</h2>
                <p class="sheet-subtitle">ID: {{ selectedItem.id }}</p>
              </div>
              <ion-button fill="clear" @click="closeQuickView" class="sheet-close">
                <ion-icon :icon="close"></ion-icon>
              </ion-button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="sheet-body">
            <!-- Card Preview -->
            <div class="card-preview" :style="{ background: getCardGradient(selectedItem.card_type) }">
              <div class="card-chip"></div>
              <div class="card-number-preview">
                {{ formatCardNumber(selectedItem.card_number) || '**** **** **** ****' }}
              </div>
              <div class="card-details-preview">
                <div class="card-holder-preview">
                  <span class="preview-label">Card Holder</span>
                  <span class="preview-value">{{ selectedItem.card_holder_name || 'N/A' }}</span>
                </div>
                <div class="card-expiry-preview">
                  <span class="preview-label">Expires</span>
                  <span class="preview-value">{{ selectedItem.card_expiry || 'N/A' }}</span>
                </div>
              </div>
            </div>

            <!-- Detailed Info -->
            <div class="details-section">
              <h3 class="section-title">Card Information</h3>
              <div class="details-grid">
                <div class="detail-item">
                  <span class="detail-label">Card Number</span>
                  <div class="detail-value-wrapper">
                    <span class="detail-value">{{ selectedItem.card_number || 'N/A' }}</span>
                    <ion-button 
                      fill="clear" 
                      size="small" 
                      @click="copyToClipboard(selectedItem.card_number, 'Card number')"
                      class="copy-button"
                    >
                      <ion-icon :icon="copyOutline"></ion-icon>
                    </ion-button>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Card Type</span>
                  <span class="detail-value">
                    <ion-chip :color="getCardTypeClass(selectedItem.card_type)" size="small">
                      {{ selectedItem.card_type || 'Unknown' }}
                    </ion-chip>
                  </span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">CVV</span>
                  <div class="detail-value-wrapper">
                    <span class="detail-value">{{ selectedItem.card_cvv || 'N/A' }}</span>
                    <ion-button 
                      fill="clear" 
                      size="small" 
                      @click="copyToClipboard(selectedItem.card_cvv, 'CVV')"
                      class="copy-button"
                    >
                      <ion-icon :icon="copyOutline"></ion-icon>
                    </ion-button>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Expiry Date</span>
                  <span class="detail-value">{{ selectedItem.card_expiry || 'N/A' }}</span>
                </div>
              </div>
            </div>

            <div class="details-section">
              <h3 class="section-title">Billing Address</h3>
              <div class="address-display">
                <ion-icon :icon="location" class="address-icon"></ion-icon>
                <div class="address-text">
                  <p>{{ selectedItem.billing_address || 'N/A' }}</p>
                  <p>{{ selectedItem.billing_city }}{{ selectedItem.billing_state ? ', ' + selectedItem.billing_state : '' }} {{ selectedItem.billing_zip_code || '' }}</p>
                </div>
              </div>
            </div>

            <div class="details-section">
              <h3 class="section-title">Additional Information</h3>
              <div class="meta-grid">
                <div class="meta-item">
                  <span class="meta-label">Application ID</span>
                  <span class="meta-value">{{ selectedItem.trademark_application_id || 'N/A' }}</span>
                </div>
                <div class="meta-item">
                  <span class="meta-label">Created</span>
                  <span class="meta-value">{{ formatDateTime(selectedItem.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="sheet-footer">
            <ion-button expand="block" @click="copyRecord(selectedItem)" class="footer-button">
              <ion-icon :icon="copy" slot="start"></ion-icon>
              Copy All Details
            </ion-button>
          </div>
        </div>
      </ion-modal>

      <!-- Toast Notification -->
      <ion-toast
        :is-open="copyNotification.show"
        :message="copyNotification.message"
        :color="copyNotification.type"
        :duration="2000"
        @didDismiss="copyNotification.show = false"
        position="bottom"
        class="modern-toast"
      ></ion-toast>
    </ion-content>
  </ion-page>
</template>

<script setup>
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonIcon,
  IonBackButton, IonButtons, IonBadge, IonChip, IonSpinner, IonModal, IonToast
} from '@ionic/vue'

import {
  refresh, chevronBack, chevronForward, chevronDown, chevronUp, close, card, 
  personCircle, location, eye, copy, copyOutline, warning, documentText,
  home, business, informationCircle, refreshCircle, moon, sunny,
  calendar, key, funnel, cash
} from 'ionicons/icons'

import { ref, reactive, computed, onMounted } from 'vue'

// API Configuration
const API_URL = 'https://trademarkatlas.com/fetch.php'

// State
const data = ref([])
const loading = ref(false)
const error = ref('')
const selectedItem = ref(null)
const quickViewVisible = ref(false)
const copyNotification = ref({ show: false, message: '', type: 'success' })
const darkMode = ref(false)
const expandedCard = ref(null)
const isScrolled = ref(false)
const isPullToRefresh = ref(false)

const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0,
  pages: 1
})

// Stats computed
const stats = computed(() => [
  {
    icon: card,
    label: 'Total Records',
    value: pagination.total,
    gradient: 'linear-gradient(135deg, #6366f1, #8b5cf6)'
  },
  {
    icon: cash,
    label: 'This Page',
    value: data.value.length,
    gradient: 'linear-gradient(135deg, #10b981, #34d399)'
  },
  {
    icon: calendar,
    label: 'Current Page',
    value: pagination.page,
    gradient: 'linear-gradient(135deg, #f59e0b, #fbbf24)'
  },
  {
    icon: documentText,
    label: 'Total Pages',
    value: pagination.pages,
    gradient: 'linear-gradient(135deg, #ef4444, #f87171)'
  }
])

// Computed
const themeIcon = computed(() => darkMode.value ? sunny : moon)

// Formatting functions
const formatCardNumberShort = (cardNumber) => {
  if (!cardNumber) return 'N/A'
  const cleaned = cardNumber.replace(/\D/g, '')
  if (cleaned.length >= 16) {
    return `•••• •••• •••• ${cleaned.slice(-4)}`
  }
  return cardNumber
}

const formatCurrency = (amount) => {
  if (!amount) return '$0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatAddressShort = (item) => {
  if (item.billing_city) return item.billing_city
  if (item.billing_address) {
    return item.billing_address.length > 20 
      ? item.billing_address.substring(0, 20) + '...'
      : item.billing_address
  }
  return 'N/A'
}

const formatCardNumber = (cardNumber) => {
  if (!cardNumber) return 'N/A'
  const cleaned = cardNumber.replace(/\D/g, '')
  const groups = cleaned.match(/.{1,4}/g)
  return groups ? groups.join(' ') : cleaned
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  } catch {
    return dateString
  }
}

const getCardTypeAbbreviation = (cardType) => {
  if (!cardType) return '?'
  const type = cardType.toLowerCase()
  if (type.includes('visa')) return 'VISA'
  if (type.includes('master')) return 'MC'
  if (type.includes('amex')) return 'AMEX'
  if (type.includes('discover')) return 'DISC'
  return cardType.substring(0, 4).toUpperCase()
}

const getCardTypeEmoji = (cardType) => {
  if (!cardType) return '💳'
  const type = cardType.toLowerCase()
  if (type.includes('visa')) return '💙'
  if (type.includes('master')) return '🟡'
  if (type.includes('amex')) return '💚'
  if (type.includes('discover')) return '🟠'
  return '💳'
}

const getGradientByType = (cardType) => {
  const type = cardType?.toLowerCase() || ''
  if (type.includes('visa')) return 'linear-gradient(135deg, #1a1f71, #0e1a4a)'
  if (type.includes('master')) return 'linear-gradient(135deg, #eb001b, #f79e1b)'
  if (type.includes('amex')) return 'linear-gradient(135deg, #006fcf, #2c7bb6)'
  if (type.includes('discover')) return 'linear-gradient(135deg, #ff6000, #ff8c42)'
  return 'linear-gradient(135deg, #6366f1, #8b5cf6)'
}

const getCardGradient = (cardType) => {
  return getGradientByType(cardType)
}

const getCardTypeClass = (cardType) => {
  if (!cardType) return 'medium'
  const type = cardType.toLowerCase()
  if (type.includes('visa')) return 'primary'
  if (type.includes('master')) return 'secondary'
  if (type.includes('amex')) return 'tertiary'
  if (type.includes('discover')) return 'success'
  return 'warning'
}

// UI Actions
const toggleCardExpansion = (id) => {
  expandedCard.value = expandedCard.value === id ? null : id
}

const onScroll = (ev) => {
  isScrolled.value = ev.detail.scrollTop > 10
}

const sortData = () => {
  // Implement sorting logic
  console.log('Sort data')
}

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
    } else {
      throw new Error(result.error || 'Failed to fetch data')
    }
  } catch (err) {
    error.value = err.message
    console.error('Fetch error:', err)
    data.value = []
  } finally {
    loading.value = false
  }
}

// Pagination functions
const nextPage = () => {
  if (pagination.page < pagination.pages) {
    pagination.page++
    fetchData()
    expandedCard.value = null
  }
}

const prevPage = () => {
  if (pagination.page > 1) {
    pagination.page--
    fetchData()
    expandedCard.value = null
  }
}

const goToPage = (pageNum) => {
  if (pageNum !== '...' && pageNum !== pagination.page) {
    pagination.page = Math.max(1, Math.min(pageNum, pagination.pages))
    fetchData()
    expandedCard.value = null
  }
}

const visiblePages = computed(() => {
  if (!pagination.pages || pagination.pages <= 1) return []
  
  const totalPages = pagination.pages
  const current = pagination.page
  const delta = 1
  
  if (totalPages <= 5) {
    return Array.from({ length: totalPages }, (_, i) => i + 1)
  }
  
  const range = []
  
  if (current - delta > 2) {
    range.push(1, '...')
  } else {
    range.push(1, 2, 3)
  }
  
  if (current > delta + 2 && current < totalPages - delta - 1) {
    for (let i = current - delta; i <= current + delta; i++) {
      if (!range.includes(i)) range.push(i)
    }
  }
  
  if (current + delta < totalPages - 1) {
    if (range[range.length - 1] !== '...') range.push('...')
    range.push(totalPages - 2, totalPages - 1, totalPages)
  } else {
    for (let i = totalPages - 2; i <= totalPages; i++) {
      if (!range.includes(i)) range.push(i)
    }
  }
  
  return range
})

// Copy functions
const copyToClipboard = async (text, label = 'Data') => {
  if (!text) return
  
  try {
    await navigator.clipboard.writeText(text.toString())
    showNotification(`${label} copied`, 'success')
  } catch (err) {
    console.error('Copy failed:', err)
    showNotification('Failed to copy', 'danger')
  }
}

const copyRecord = async (item) => {
  try {
    const text = Object.entries(item)
      .map(([key, value]) => `${key}: ${value || 'N/A'}`)
      .join('\n')
    
    await navigator.clipboard.writeText(text)
    showNotification('Record copied to clipboard', 'success')
  } catch (err) {
    console.error('Copy failed:', err)
    showNotification('Failed to copy record', 'danger')
  }
}

const showNotification = (message, type = 'success') => {
  copyNotification.value = { show: true, message, type }
}

// Modal functions
const showQuickView = (item) => {
  selectedItem.value = item
  quickViewVisible.value = true
  expandedCard.value = null
}

const closeQuickView = () => {
  quickViewVisible.value = false
  selectedItem.value = null
}

// Theme toggle
const toggleTheme = () => {
  darkMode.value = !darkMode.value
  document.body.classList.toggle('dark-theme', darkMode.value)
}

// Initialize
onMounted(() => {
  fetchData()
  
  // Check for dark mode preference
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    darkMode.value = true
    document.body.classList.add('dark-theme')
  }
})
</script>

<style scoped>
/* ============================================
   MODERN DESIGN SYSTEM - iOS INSPIRED
   ============================================ */

.card-amount {display: none;}


/* Variables */
:root {
  --primary: #6366f1;
  --primary-light: #818cf8;
  --primary-dark: #4f46e5;
  --secondary: #8b5cf6;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
  
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  
  --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ============================================
   HEADER STYLES
   ============================================ */
.modern-header {
  --background: transparent;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.8);
  transition: var(--transition);
  border-bottom: 1px solid transparent;
}

.dark-theme .modern-header {
  background: rgba(17, 24, 39, 0.8);
}

.header-elevated {
  border-bottom-color: var(--gray-200);
  box-shadow: var(--shadow-sm);
}

.modern-toolbar {
  --background: transparent;
  --border-width: 0;
  --min-height: 60px;
}

.modern-back-button {
  --color: var(--primary);
  --icon-font-size: 24px;
}

.title-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-icon-wrapper {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.title-icon {
  color: white;
  font-size: 22px;
}

.title-text-container {
  display: flex;
  flex-direction: column;
}

.title-main {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: var(--gray-900);
  line-height: 1.2;
}

.dark-theme .title-main {
  color: white;
}

.title-sub {
  margin: 0;
  font-size: 13px;
  color: var(--gray-500);
  font-weight: 500;
}

.modern-icon-button {
  --padding-start: 10px;
  --padding-end: 10px;
  --border-radius: 50%;
  width: 40px;
  height: 40px;
  --background: var(--gray-100);
  color: var(--gray-600);
  transition: var(--transition);
}

.modern-icon-button:hover {
  --background: var(--primary);
  color: white;
  transform: scale(1.05);
}

/* ============================================
   CONTENT STYLES
   ============================================ */
.modern-content {
  --background: var(--gray-50);
}

.dark-theme .modern-content {
  --background: var(--gray-900);
}

.content-padded {
  padding-top: 8px;
}

/* ============================================
   STATS CARDS
   ============================================ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  padding: 16px;
  margin-bottom: 8px;
}

.stat-card {
  background: white;
  border-radius: var(--radius-lg);
  padding: 16px 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  border: 1px solid var(--gray-100);
}

.dark-theme .stat-card {
  background: var(--gray-800);
  border-color: var(--gray-700);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stat-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  color: white;
  font-size: 24px;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: var(--gray-900);
  line-height: 1.2;
}

.dark-theme .stat-value {
  color: white;
}

.stat-label {
  font-size: 12px;
  color: var(--gray-500);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ============================================
   SKELETON LOADING
   ============================================ */
.loading-container {
  padding: 16px;
}

.skeleton-header {
  height: 40px;
  width: 200px;
  background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: var(--radius-md);
  margin-bottom: 20px;
}

.skeleton-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}

.dark-theme .skeleton-item {
  background: var(--gray-800);
}

.skeleton-avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-md);
  background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-line {
  height: 12px;
  background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ============================================
   ERROR & EMPTY STATES
   ============================================ */
.error-container,
.empty-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
  text-align: center;
}

.error-illustration,
.empty-illustration {
  color: var(--gray-400);
  margin-bottom: 24px;
}

.error-illustration svg,
.empty-illustration svg {
  width: 120px;
  height: 120px;
}

.error-title,
.empty-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--gray-900);
  margin: 0 0 8px;
}

.dark-theme .error-title,
.dark-theme .empty-title {
  color: white;
}

.error-message,
.empty-message {
  font-size: 15px;
  color: var(--gray-500);
  margin: 0 0 24px;
  max-width: 280px;
}

.retry-button,
.refresh-button {
  --border-radius: var(--radius-lg);
  --padding-start: 24px;
  --padding-end: 24px;
  height: 48px;
  font-weight: 600;
}

/* ============================================
   CARD LIST
   ============================================ */
.list-container {
  padding: 16px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding: 0 4px;
}

.list-header-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-700);
}

.dark-theme .list-header-title {
  color: var(--gray-300);
}

.card-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.record-card {
  background: white;
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: var(--transition);
  border: 1px solid var(--gray-100);
  box-shadow: var(--shadow-sm);
}

.dark-theme .record-card {
  background: var(--gray-800);
  border-color: var(--gray-700);
}

.record-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.card-expanded {
  box-shadow: var(--shadow-lg);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  cursor: pointer;
}

.card-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.card-avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: var(--shadow-sm);
}

.card-info {
  flex: 1;
}

.card-title {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-900);
}

.dark-theme .card-title {
  color: white;
}

.card-subtitle {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.card-number {
  font-size: 14px;
  color: var(--gray-600);
  font-family: 'SF Mono', monospace;
}

.dark-theme .card-number {
  color: var(--gray-400);
}

.card-badge {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  background: var(--gray-200);
  color: var(--gray-700);
  font-weight: 600;
  letter-spacing: 0.5px;
}

.card-badge.primary {
  background: #e0e7ff;
  color: #4f46e5;
}

.card-badge.secondary {
  background: #f3e8ff;
  color: #8b5cf6;
}

.card-badge.tertiary {
  background: #dbeafe;
  color: #2563eb;
}

.card-badge.success {
  background: #d1fae5;
  color: #059669;
}

.card-header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.card-amount {
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-900);
}

.dark-theme .card-amount {
  color: white;
}

.expand-icon {
  font-size: 18px;
  color: var(--gray-400);
  transition: var(--transition);
}

/* Expanded Content */
.card-expanded-content {
  padding: 0 16px 16px 76px;
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.expanded-section {
  margin-bottom: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid var(--gray-100);
}

.dark-theme .info-row {
  border-bottom-color: var(--gray-700);
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: var(--gray-600);
}

.dark-theme .info-label {
  color: var(--gray-400);
}

.info-icon {
  font-size: 16px;
  color: var(--gray-400);
}

.info-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--gray-900);
}

.dark-theme .info-value {
  color: white;
}

.sensitive-data {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--danger);
  cursor: pointer;
}

.copy-small-icon {
  font-size: 14px;
  color: var(--gray-400);
}

.card-actions {
  display: flex;
  gap: 12px;
  padding-top: 12px;
}

.card-action-button {
  flex: 1;
  --border-radius: var(--radius-md);
  --padding-start: 0;
  --padding-end: 0;
  height: 40px;
  font-size: 14px;
  font-weight: 500;
  background: var(--gray-100);
  color: var(--gray-700);
  transition: var(--transition);
}

.dark-theme .card-action-button {
  background: var(--gray-700);
  color: var(--gray-300);
}

.card-action-button:hover {
  background: var(--primary);
  color: white;
}

/* ============================================
   PAGINATION
   ============================================ */
.pagination-container {
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.pagination-info {
  font-size: 14px;
  color: var(--gray-500);
}

.pagination-controls-modern {
  display: flex;
  align-items: center;
  gap: 24px;
}

.pagination-arrow {
  --padding-start: 12px;
  --padding-end: 12px;
  --border-radius: 50%;
  width: 44px;
  height: 44px;
  --background: var(--gray-200);
  color: var(--gray-600);
  transition: var(--transition);
}

.pagination-arrow:hover:not(:disabled) {
  --background: var(--primary);
  color: white;
  transform: scale(1.05);
}

.pagination-arrow:disabled {
  opacity: 0.3;
}

.page-dots {
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--gray-300);
  cursor: pointer;
  transition: var(--transition);
}

.dark-theme .page-dot {
  background: var(--gray-600);
}

.page-dot.active {
  width: 32px;
  border-radius: 20px;
  background: var(--primary);
  transform: scale(1);
}

.page-dot.dot:hover {
  transform: scale(1.2);
  background: var(--primary);
}

/* ============================================
   BOTTOM SHEET MODAL
   ============================================ */
.bottom-sheet-modal {
  --height: auto;
  --border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}

.bottom-sheet-content {
  background: white;
  border-radius: var(--radius-xl) var(--radius-xl) 0 0;
  min-height: 50vh;
  max-height: 90vh;
  overflow-y: auto;
}

.dark-theme .bottom-sheet-content {
  background: var(--gray-800);
}

.sheet-header {
  padding: 20px 20px 12px;
  border-bottom: 1px solid var(--gray-100);
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

.dark-theme .sheet-header {
  background: var(--gray-800);
  border-bottom-color: var(--gray-700);
}

.sheet-handle {
  width: 40px;
  height: 4px;
  background: var(--gray-300);
  border-radius: 2px;
  margin: 0 auto 16px;
}

.sheet-title-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sheet-avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: var(--shadow-md);
}

.sheet-title-info {
  flex: 1;
}

.sheet-title {
  margin: 0 0 4px;
  font-size: 18px;
  font-weight: 600;
  color: var(--gray-900);
}

.dark-theme .sheet-title {
  color: white;
}

.sheet-subtitle {
  margin: 0;
  font-size: 13px;
  color: var(--gray-500);
}

.sheet-close {
  --padding-start: 8px;
  --padding-end: 8px;
  --border-radius: 50%;
  width: 36px;
  height: 36px;
  --background: var(--gray-100);
  color: var(--gray-600);
}

.sheet-body {
  padding: 20px;
}

/* Card Preview */
.card-preview {
  padding: 24px 20px;
  border-radius: var(--radius-lg);
  margin-bottom: 24px;
  color: white;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

.card-preview::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 50%);
  pointer-events: none;
}

.card-chip {
  width: 40px;
  height: 30px;
  background: linear-gradient(135deg, #ffd700, #ffed8a);
  border-radius: 6px;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}

.card-chip::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: rgba(0,0,0,0.1);
}

.card-number-preview {
  font-size: 20px;
  font-family: 'SF Mono', monospace;
  letter-spacing: 2px;
  margin-bottom: 24px;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.card-details-preview {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-holder-preview,
.card-expiry-preview {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.preview-label {
  font-size: 10px;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.preview-value {
  font-size: 14px;
  font-weight: 500;
}

/* Details Sections */
.details-section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--gray-700);
  margin: 0 0 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dark-theme .section-title {
  color: var(--gray-300);
}

.details-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: var(--gray-50);
  border-radius: var(--radius-lg);
  padding: 16px;
}

.dark-theme .details-grid {
  background: var(--gray-700);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  font-size: 14px;
  color: var(--gray-600);
}

.dark-theme .detail-label {
  color: var(--gray-400);
}

.detail-value-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--gray-900);
  font-family: 'SF Mono', monospace;
}

.dark-theme .detail-value {
  color: white;
}

.copy-button {
  --padding-start: 6px;
  --padding-end: 6px;
  --border-radius: 50%;
  width: 28px;
  height: 28px;
  --background: white;
  color: var(--gray-600);
  margin: 0;
}

.dark-theme .copy-button {
  --background: var(--gray-600);
  color: white;
}

.address-display {
  display: flex;
  gap: 12px;
  background: var(--gray-50);
  border-radius: var(--radius-lg);
  padding: 16px;
}

.dark-theme .address-display {
  background: var(--gray-700);
}

.address-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-50);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-size: 20px;
  flex-shrink: 0;
}

.address-text {
  flex: 1;
}

.address-text p {
  margin: 0 0 4px;
  font-size: 14px;
  color: var(--gray-900);
  line-height: 1.5;
}

.dark-theme .address-text p {
  color: white;
}

.address-text p:last-child {
  margin-bottom: 0;
  color: var(--gray-600);
}

.dark-theme .address-text p:last-child {
  color: var(--gray-400);
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: var(--gray-50);
  border-radius: var(--radius-lg);
  padding: 16px;
}

.dark-theme .meta-grid {
  background: var(--gray-700);
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-label {
  font-size: 12px;
  color: var(--gray-500);
}

.dark-theme .meta-label {
  color: var(--gray-400);
}

.meta-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--gray-900);
}

.dark-theme .meta-value {
  color: white;
}

/* Sheet Footer */
.sheet-footer {
  padding: 20px;
  border-top: 1px solid var(--gray-200);
  background: white;
  position: sticky;
  bottom: 0;
}

.dark-theme .sheet-footer {
  background: var(--gray-800);
  border-top-color: var(--gray-700);
}

.footer-button {
  --border-radius: var(--radius-lg);
  --padding-top: 16px;
  --padding-bottom: 16px;
  font-weight: 600;
  margin: 0;
}

/* ============================================
   TOAST NOTIFICATIONS
   ============================================ */
.modern-toast {
  --border-radius: var(--radius-lg);
  --box-shadow: var(--shadow-lg);
  font-size: 14px;
  font-weight: 500;
  padding: 12px 20px;
}

/* ============================================
   ANIMATIONS
   ============================================ */
.rotate-animation {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */
@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon-wrapper {
    width: 40px;
    height: 40px;
  }
  
  .stat-value {
    font-size: 20px;
  }
  
  .stat-label {
    font-size: 11px;
  }
  
  .card-header {
    padding: 12px;
  }
  
  .card-avatar {
    width: 40px;
    height: 40px;
    font-size: 20px;
  }
  
  .card-title {
    font-size: 15px;
  }
  
  .card-number {
    font-size: 12px;
  }
  
  .card-expanded-content {
    padding-left: 64px;
  }
  
  .pagination-controls-modern {
    gap: 16px;
  }
  
  .page-dots {
    gap: 8px;
  }
}

@media (max-width: 360px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .card-header-right {
    width: 100%;
    justify-content: space-between;
  }
}
</style>