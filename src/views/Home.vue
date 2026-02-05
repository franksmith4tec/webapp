<template>
  <div v-if="isMobile">
    <HomeMobile />
  </div>
  <div v-else>
    <HomeWeb />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { isMobilePlatform } from '@/utils/platform'
import HomeWeb from './HomeWeb.vue'
import HomeMobile from './HomeMobile.vue'

const isMobile = ref(false)

onMounted(() => {
  isMobile.value = isMobilePlatform()
})
</script>