<template>
  <PublicLayout>
    <Head title="Senarai Institusi — Sedekah.online" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Senarai Institusi</h1>
        <p class="text-gray-500 dark:text-gray-400">
          Cari dan tapis institusi mengikut lokasi, kategori, atau nama.
        </p>
      </div>

      <!-- Filters -->
      <div class="flex flex-col items-center gap-5 mb-8">
        <CategoryFilter v-model="filters.category" @update:modelValue="onCategoryChange" />
        <SearchBar
          v-model="filters.search"
          v-model:state="filters.state"
          @search="onSearch"
          @update:state="onStateChange"
        />
      </div>

      <!-- Results Bar -->
      <div class="mb-5 px-1">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          <template v-if="loading">Memuatkan...</template>
          <template v-else>
            Menunjukkan <span class="font-semibold text-gray-700 dark:text-gray-200">{{ institutions.length }}</span>
            daripada <span class="font-semibold text-gray-700 dark:text-gray-200">{{ total }}</span> institusi
          </template>
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-10">
        <div v-for="n in 6" :key="n" class="glass-panel rounded-3xl p-5 animate-pulse h-80"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="isEmpty" class="text-center py-24">
        <span class="material-icons-round text-6xl text-gray-300 dark:text-gray-600 block mb-4">search_off</span>
        <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Tiada institusi dijumpai</h3>
        <p class="text-sm text-gray-400 mt-1 mb-5">Cuba carian yang berbeza atau buang penapis</p>
        <button @click="resetFilters" class="btn-primary text-sm">Paparkan Semua</button>
      </div>

      <!-- Institution Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-6">
        <InstitutionCard
          v-for="institution in institutions"
          :key="institution.id"
          :institution="institution"
          @share="openShare"
        />
      </div>

      <!-- Show More -->
      <div v-if="!loading && hasMore" class="flex flex-col items-center gap-2">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="btn-primary flex items-center gap-2 px-8 py-3 disabled:opacity-60"
        >
          <svg v-if="loadingMore" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          <span class="material-icons-round text-lg" v-else>expand_more</span>
          {{ loadingMore ? 'Memuatkan...' : 'Papar Lebih' }}
        </button>
        <p class="text-xs text-gray-400">Menunjukkan {{ institutions.length }} daripada {{ total }} institusi</p>
      </div>
    </div>

    <ShareModal v-model="shareModalOpen" :institution="sharingInstitution" :qr-image-url="sharingQrUrl" />
  </PublicLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import InstitutionCard from '@/Components/InstitutionCard.vue'
import CategoryFilter from '@/Components/CategoryFilter.vue'
import SearchBar from '@/Components/SearchBar.vue'
import ShareModal from '@/Components/ShareModal.vue'
import { useInstitutions } from '@/Composables/useInstitutions.js'

const {
  institutions, total, filters, loading, loadingMore, isEmpty, hasMore,
  fetch, loadMore,
} = useInstitutions()

const shareModalOpen = ref(false)
const sharingInstitution = ref(null)
const sharingQrUrl = ref(null)

function openShare(institution) {
  sharingInstitution.value = institution
  const qrs = institution.active_qr_codes || institution.qr_codes || []
  sharingQrUrl.value = qrs[0]?.qr_image_url || null
  shareModalOpen.value = true
}

function onCategoryChange(cat) { filters.category = cat; fetch() }
function onSearch(term) { filters.search = term; fetch() }
function onStateChange(state) { filters.state = state; fetch() }
function resetFilters() { filters.search = ''; filters.category = ''; filters.state = ''; fetch() }

// Initialize from URL query params if present
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('category')) filters.category = params.get('category')
  if (params.get('state')) filters.state = params.get('state')
  if (params.get('search')) filters.search = params.get('search')
  fetch()
})
</script>
