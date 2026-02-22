<template>
  <Teleport to="body">
    <Transition name="hqm">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close"></div>

        <!-- Card Container -->
        <div class="hqm-panel relative w-full max-w-sm z-10">
          <!-- Close button -->
          <button
            @click="close"
            class="absolute -top-2 -right-2 z-20 w-8 h-8 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            aria-label="Tutup"
          >
            <span class="material-icons-round text-base">close</span>
          </button>

          <!-- Swipeable Card -->
          <div
            ref="cardEl"
            class="relative select-none touch-none"
            :style="cardStyle"
            @mousedown="onDragStart"
            @touchstart="onDragStart"
          >
            <div class="glass-panel rounded-3xl p-6 shadow-2xl overflow-hidden">
              <!-- Loading state -->
              <div v-if="isLoading" class="flex flex-col items-center justify-center py-10">
                <svg class="animate-spin h-8 w-8 text-primary mb-3" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm text-gray-400">Memuatkan...</span>
              </div>

              <template v-else-if="currentInstitution">
                <!-- Payment Method Tabs -->
                <div v-if="currentQrCodes.length > 1" class="flex gap-2 mb-4 flex-wrap">
                  <button
                    v-for="(qr, i) in currentQrCodes"
                    :key="qr.id"
                    @click="activeQrIndex = i"
                    :class="[
                      'text-xs px-3 py-1 rounded-full font-semibold transition-colors',
                      activeQrIndex === i
                        ? 'bg-primary text-white'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                    ]"
                  >
                    {{ qr.payment_method?.name || 'QR' }}
                  </button>
                </div>

                <!-- QR Code -->
                <div class="w-full bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-4 flex justify-center items-center aspect-square">
                  <Transition name="qr-fade" mode="out-in">
                    <img
                      v-if="activeQr"
                      :key="currentInstitution.id + '-' + activeQrIndex"
                      :src="activeQr.qr_image_url"
                      :alt="`QR ${currentInstitution.name}`"
                      class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal"
                    />
                    <div v-else :key="'empty'" class="flex flex-col items-center justify-center text-gray-300 dark:text-gray-600 gap-2">
                      <span class="material-icons-round text-6xl">qr_code_2</span>
                      <span class="text-xs">Tiada QR</span>
                    </div>
                  </Transition>
                </div>

                <!-- Institution Info -->
                <h3 class="font-bold text-gray-900 dark:text-white text-lg leading-tight mb-1">
                  {{ currentInstitution.name }}
                </h3>
                <div class="flex items-center gap-2 mb-4 flex-wrap">
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ currentInstitution.city }}, {{ currentInstitution.state }}</span>
                  <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                  <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-green-300 font-semibold capitalize">
                    {{ currentInstitution.category }}
                  </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                  <button
                    v-if="activeQr"
                    @click="downloadQr"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    title="Muat Turun QR"
                  >
                    <span class="material-icons-round text-lg">download</span>
                  </button>
                  <button
                    @click="onShare"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    title="Kongsi"
                  >
                    <span class="material-icons-round text-lg">share</span>
                  </button>
                  <button
                    v-if="currentInstitution?.url"
                    @click="openDonationUrl"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                    title="Sumbang"
                  >
                    <span class="material-icons-round text-lg">favorite_border</span>
                  </button>
                  <button
                    @click="viewMore"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 transition-colors text-sm font-semibold ml-auto"
                  >
                    Lihat Lanjut
                    <span class="material-icons-round text-lg leading-none">arrow_forward</span>
                  </button>
                </div>
              </template>
            </div>

            <!-- Swipe direction overlays -->
            <Transition name="hint-fade">
              <div v-if="dragX > 40" class="absolute inset-0 rounded-3xl border-2 border-green-400/50 bg-green-400/5 flex items-center justify-center pointer-events-none">
                <div class="bg-green-500/20 backdrop-blur-sm rounded-full p-3">
                  <span class="material-icons-round text-green-400 text-3xl">undo</span>
                </div>
              </div>
            </Transition>
            <Transition name="hint-fade">
              <div v-if="dragX < -40" class="absolute inset-0 rounded-3xl border-2 border-secondary/50 bg-secondary/5 flex items-center justify-center pointer-events-none">
                <div class="bg-secondary/20 backdrop-blur-sm rounded-full p-3">
                  <span class="material-icons-round text-secondary text-3xl">shuffle</span>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Navigation controls -->
          <div class="flex items-center justify-center gap-4 mt-4">
            <button
              @click="goPrev"
              :disabled="history.length === 0"
              class="w-10 h-10 rounded-full bg-white/90 dark:bg-gray-800/90 shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
              title="Sebelumnya"
            >
              <span class="material-icons-round">undo</span>
            </button>
            <button
              @click="goRandom"
              class="w-12 h-12 rounded-full bg-secondary shadow-lg shadow-secondary/30 flex items-center justify-center text-white hover:bg-secondary/90 transition-all hover:scale-105"
              title="Rawak"
            >
              <span class="material-icons-round text-xl">shuffle</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  initialInstitution: { type: Object, default: null },
})

const emit = defineEmits(['update:modelValue', 'share'])

// State
const pool = ref([])              // pool of fetched random institutions
const currentIndex = ref(0)       // current position in pool
const history = ref([])            // stack for going back
const isLoading = ref(false)
const activeQrIndex = ref(0)
const cardEl = ref(null)

// Drag state
const dragX = ref(0)
const dragging = ref(false)
let dragStartX = 0

const currentInstitution = computed(() => pool.value[currentIndex.value] || null)

const currentQrCodes = computed(() => {
  if (!currentInstitution.value) return []
  const qrs = currentInstitution.value.active_qr_codes || currentInstitution.value.qr_codes || []
  const active = qrs.filter(q => q.status === 'active')
  return active.length ? active : qrs
})

const activeQr = computed(() => currentQrCodes.value[activeQrIndex.value] ?? null)

const cardStyle = computed(() => {
  if (!dragging.value && dragX.value === 0) {
    return { transition: 'transform 0.35s cubic-bezier(0.34,1.56,0.64,1)' }
  }
  const rotate = dragX.value * 0.06
  return {
    transform: `translateX(${dragX.value}px) rotate(${rotate}deg)`,
    transition: dragging.value ? 'none' : 'transform 0.35s cubic-bezier(0.34,1.56,0.64,1)',
  }
})

// When modal opens, seed the pool
watch(() => props.modelValue, async (open) => {
  if (!open) return
  history.value = []
  activeQrIndex.value = 0

  // Start with initial institution if provided
  if (props.initialInstitution) {
    pool.value = [props.initialInstitution]
    currentIndex.value = 0
  }

  // Pre-fetch random institutions into pool
  await fetchRandomBatch()
})

async function fetchRandomBatch() {
  try {
    isLoading.value = pool.value.length === 0
    // Fetch a random page (API now prioritizes institutions with URLs via weighted randomization)
    const randomPage = Math.floor(Math.random() * 20) + 1
    const { data } = await axios.get('/api/v1/institutions', {
      params: { page: randomPage, per_page: 9 }
    })

    const items = data.data || data
    if (items.length > 0) {
      // Filter out any already in pool (by id)
      const existingIds = new Set(pool.value.map(i => i.id))
      const newItems = items.filter(i => !existingIds.has(i.id))
      pool.value = [...pool.value, ...newItems]
    }
  } catch (e) {
    console.error('Failed to fetch random institutions:', e)
  } finally {
    isLoading.value = false
  }
}

// Drag handlers
function onDragStart(e) {
  dragging.value = true
  dragStartX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX
  window.addEventListener('mousemove', onDragMove)
  window.addEventListener('mouseup', onDragEnd)
  window.addEventListener('touchmove', onDragMove, { passive: false })
  window.addEventListener('touchend', onDragEnd)
}

function onDragMove(e) {
  const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX
  dragX.value = clientX - dragStartX
}

function onDragEnd() {
  dragging.value = false
  cleanupListeners()

  if (dragX.value < -80) {
    goRandom()
  } else if (dragX.value > 80) {
    goPrev()
  }

  dragX.value = 0
}

function cleanupListeners() {
  window.removeEventListener('mousemove', onDragMove)
  window.removeEventListener('mouseup', onDragEnd)
  window.removeEventListener('touchmove', onDragMove)
  window.removeEventListener('touchend', onDragEnd)
}

async function goRandom() {
  if (!currentInstitution.value) return
  history.value.push(currentIndex.value)
  activeQrIndex.value = 0

  // If we're near end of pool, fetch more
  if (currentIndex.value >= pool.value.length - 3) {
    await fetchRandomBatch()
  }

  // Move to next in pool, or wrap
  if (currentIndex.value < pool.value.length - 1) {
    currentIndex.value++
  } else {
    await fetchRandomBatch()
    if (currentIndex.value < pool.value.length - 1) {
      currentIndex.value++
    }
  }
}

function goPrev() {
  if (history.value.length === 0) return
  activeQrIndex.value = 0
  currentIndex.value = history.value.pop()
}

function close() {
  emit('update:modelValue', false)
}

function downloadQr() {
  if (!activeQr.value?.qr_image_url) return
  const link = document.createElement('a')
  link.href = activeQr.value.qr_image_url
  link.download = `qr-${currentInstitution.value.slug || 'code'}.png`
  link.click()
}

function onShare() {
  if (currentInstitution.value) {
    emit('share', currentInstitution.value)
  }
}

function viewMore() {
  if (!currentInstitution.value) return
  close()
  router.visit(route('institutions.show', currentInstitution.value.slug))
}

function openDonationUrl() {
  if (currentInstitution.value?.url) {
    window.open(currentInstitution.value.url, '_blank')
  }
}

onUnmounted(() => {
  cleanupListeners()
})
</script>

<style scoped>
/* Modal transition */
.hqm-enter-active,
.hqm-leave-active {
  transition: opacity 0.25s ease;
}
.hqm-enter-from,
.hqm-leave-to {
  opacity: 0;
}
.hqm-enter-active .hqm-panel {
  transition: opacity 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
              transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.hqm-leave-active .hqm-panel {
  transition: opacity 0.15s ease,
              transform 0.15s ease;
}
.hqm-enter-from .hqm-panel,
.hqm-leave-to .hqm-panel {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}

/* QR image crossfade */
.qr-fade-enter-active,
.qr-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.qr-fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}
.qr-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Swipe hint overlays */
.hint-fade-enter-active,
.hint-fade-leave-active {
  transition: opacity 0.15s ease;
}
.hint-fade-enter-from,
.hint-fade-leave-to {
  opacity: 0;
}
</style>
