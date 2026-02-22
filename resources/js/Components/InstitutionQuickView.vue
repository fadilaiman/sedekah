<template>
  <Teleport to="body">
    <Transition name="qv">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close"></div>

        <!-- Panel -->
        <div class="qv-panel relative glass-panel rounded-3xl p-6 w-full max-w-sm shadow-2xl">
          <!-- Close button -->
          <button
            @click="close"
            class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
            aria-label="Tutup"
          >
            <span class="material-icons-round text-base">close</span>
          </button>

          <!-- Payment Method Tabs -->
          <div v-if="qrCodes.length > 1" class="flex gap-2 mb-4 flex-wrap pr-10">
            <button
              v-for="(qr, i) in qrCodes"
              :key="qr.id"
              @click="activeIndex = i"
              :class="[
                'text-xs px-3 py-1 rounded-full font-semibold transition-colors',
                activeIndex === i
                  ? 'bg-primary text-white'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
              ]"
            >
              {{ qr.payment_method?.name || 'QR' }}
            </button>
          </div>

          <!-- QR Code (large, prominent) -->
          <div class="w-full bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-4 flex justify-center items-center aspect-square">
            <template v-if="activeQr">
              <img
                :src="activeQr.qr_image_url"
                :alt="`QR ${institution.name}`"
                class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal"
              />
            </template>
            <template v-else>
              <div class="flex flex-col items-center justify-center text-gray-300 dark:text-gray-600 gap-2">
                <span class="material-icons-round text-6xl">qr_code_2</span>
                <span class="text-xs">Tiada QR</span>
              </div>
            </template>
          </div>

          <!-- Institution Info -->
          <h3 class="font-bold text-gray-900 dark:text-white text-lg leading-tight mb-1">
            {{ institution.name }}
          </h3>
          <div class="flex items-center gap-2 mb-4 flex-wrap">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ institution.city }}, {{ institution.state }}</span>
            <span class="text-gray-300 dark:text-gray-600">•</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-green-300 font-semibold capitalize">
              {{ institution.category }}
            </span>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button
              v-if="activeQr"
              @click="downloadQr"
              class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
              title="Muat Turun QR"
              aria-label="Muat turun QR code"
            >
              <span class="material-icons-round text-lg">download</span>
            </button>
            <button
              @click="onShare"
              class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
              title="Kongsi"
              aria-label="Kongsi institusi"
            >
              <span class="material-icons-round text-lg">share</span>
            </button>
            <button
              v-if="institution.url"
              @click="openDonationUrl"
              class="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-green-400 transition-colors"
              title="Sumbang"
              aria-label="Buka pautan sumbangan"
            >
              <span class="material-icons-round text-lg">campaign</span>
            </button>
            <button
              @click="viewMore"
              class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 transition-colors text-sm font-semibold ml-auto"
            >
              Lihat Lanjut
              <span class="material-icons-round text-lg leading-none">arrow_forward</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  institution: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue', 'share'])

const activeIndex = ref(0)

// Reset tab when modal opens
watch(() => props.modelValue, (val) => {
  if (val) activeIndex.value = 0
})

const qrCodes = computed(() => {
  const qrs = props.institution.active_qr_codes || props.institution.qr_codes || []
  const active = qrs.filter(q => q.status === 'active')
  return active.length ? active : qrs
})

const activeQr = computed(() => qrCodes.value[activeIndex.value] ?? null)

function close() {
  emit('update:modelValue', false)
}

function downloadQr() {
  if (!activeQr.value?.qr_image_url) return
  const link = document.createElement('a')
  link.href = activeQr.value.qr_image_url
  link.download = `qr-${props.institution.slug}.png`
  link.click()
}

function onShare() {
  emit('share', props.institution)
}

function viewMore() {
  close()
  router.visit(route('institutions.show', props.institution.slug))
}

function openDonationUrl() {
  if (props.institution.url) {
    window.open(props.institution.url, '_blank')
  }
}
</script>

<style scoped>
/* Backdrop fade */
.qv-enter-active,
.qv-leave-active {
  transition: opacity 0.2s ease;
}
.qv-enter-from,
.qv-leave-to {
  opacity: 0;
}

/* Panel spring animation */
.qv-enter-active .qv-panel {
  transition: opacity 0.25s cubic-bezier(0.34, 1.56, 0.64, 1),
              transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.qv-leave-active .qv-panel {
  transition: opacity 0.15s ease,
              transform 0.15s ease;
}
.qv-enter-from .qv-panel,
.qv-leave-to .qv-panel {
  opacity: 0;
  transform: scale(0.92) translateY(16px);
}
</style>
