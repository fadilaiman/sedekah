<template>
  <div
    :class="[
      'glass-panel rounded-3xl p-5 hover:shadow-glass-hover transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden flex flex-col items-center text-center cursor-pointer',
      featured ? 'border-2 border-secondary/40 bg-secondary/5 dark:bg-secondary/10' : ''
    ]"
    @click="showModal = true"
  >
    <!-- Top accent bar on hover -->
    <div :class="[
      'absolute top-0 left-0 w-full h-1 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left rounded-t-3xl',
      featured ? 'bg-gradient-to-r from-secondary to-secondary/50' : 'bg-gradient-to-r from-primary to-secondary'
    ]"></div>

    <!-- Featured Badge -->
    <div v-if="featured" class="absolute top-3 right-3 flex items-center gap-1 bg-secondary text-white px-2.5 py-1 rounded-full text-xs font-semibold shadow-md">
      <span class="material-icons-round text-sm">star</span>
      <span>Pilihan</span>
    </div>

    <!-- Category Icon -->
    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm mb-3', getCategoryBg(institution.category)]">
      <span :class="['material-icons-round text-2xl', getCategoryIconClass(institution.category)]">{{ getCategoryIcon(institution.category) }}</span>
    </div>

    <!-- Institution Name & Location -->
    <div class="mb-4 w-full">
      <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight line-clamp-2 mb-1">
        {{ institution.name }}
      </h3>
      <div class="text-sm text-secondary font-medium">
        {{ institution.city }}, {{ institution.state }}
      </div>
    </div>

    <!-- QR Code Display (if available) -->
    <div class="w-full relative bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-4 flex justify-center items-center group-hover:border-secondary/30 transition-colors aspect-square">
      <template v-if="primaryQr">
        <img
          :src="primaryQr.qr_image_url"
          :alt="`QR ${institution.name}`"
          class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal"
          loading="lazy"
        />
      </template>
      <template v-else>
        <div class="flex flex-col items-center justify-center text-gray-300 dark:text-gray-600 gap-2">
          <span class="material-icons-round text-5xl">qr_code_2</span>
          <span class="text-xs">Tiada QR</span>
        </div>
      </template>
    </div>

    <!-- Payment Method Badges -->
    <div v-if="primaryQr" class="flex flex-wrap justify-center gap-1.5 mb-4 w-full">
      <span
        v-for="qr in (institution.active_qr_codes || institution.qr_codes || []).slice(0, 3)"
        :key="qr.id"
        class="text-[10px] px-2 py-0.5 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-green-300 font-semibold"
      >
        {{ qr.payment_method?.name || 'QR' }}
      </span>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-center gap-5 w-full mt-auto pt-2 border-t border-gray-100 dark:border-gray-700">
      <button
        v-if="primaryQr"
        @click.stop="downloadQr"
        class="text-gray-400 hover:text-primary dark:hover:text-green-400 transition-colors"
        title="Muat Turun QR"
        aria-label="Muat turun QR code"
      >
        <span class="material-icons-round text-xl">download</span>
      </button>
      <button
        @click.stop="shareInstitution"
        class="text-gray-400 hover:text-secondary dark:hover:text-yellow-400 transition-colors"
        title="Kongsi"
        aria-label="Kongsi institusi"
      >
        <span class="material-icons-round text-xl">share</span>
      </button>
      <button
        v-if="institution.url"
        @click.stop="openDonationUrl"
        class="text-gray-400 hover:text-primary dark:hover:text-green-400 transition-colors"
        title="Sumbang"
        aria-label="Buka pautan sumbangan"
      >
        <span class="material-icons-round text-xl">campaign</span>
      </button>
      <button
        @click.stop="goToDetail"
        class="text-gray-400 hover:text-primary dark:hover:text-green-400 transition-colors"
        title="Lihat Lanjut"
        aria-label="Lihat maklumat lanjut"
      >
        <span class="material-icons-round text-xl">arrow_forward</span>
      </button>
    </div>
  </div>

  <InstitutionQuickView
    v-model="showModal"
    :institution="institution"
    @share="shareInstitution"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import InstitutionQuickView from '@/Components/InstitutionQuickView.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  institution: { type: Object, required: true },
  featured: { type: Boolean, default: false },
})

const emit = defineEmits(['share'])

const showModal = ref(false)
const { getCategoryBg, getCategoryIconClass, getCategoryIcon } = useCategories()

// Pick first active QR code
const primaryQr = computed(() => {
  const qrs = props.institution.active_qr_codes || props.institution.qr_codes || []
  return qrs.find(q => q.status === 'active') || qrs[0] || null
})

function downloadQr() {
  if (!primaryQr.value?.qr_image_url) return
  const link = document.createElement('a')
  link.href = primaryQr.value.qr_image_url
  link.download = `qr-${props.institution.slug}.png`
  link.click()
}

function shareInstitution() {
  emit('share', props.institution)
}

function goToDetail() {
  router.visit(route('institutions.show', props.institution.slug))
}

function openDonationUrl() {
  if (props.institution.url) {
    window.open(props.institution.url, '_blank')
  }
}
</script>
