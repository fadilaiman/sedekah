<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
        @click.self="emit('update:modelValue', false)"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Modal -->
        <div class="relative glass-panel rounded-3xl p-6 w-full max-w-sm shadow-2xl">
          <!-- Close -->
          <button
            @click="emit('update:modelValue', false)"
            class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors"
          >
            <span class="material-icons-round text-base">close</span>
          </button>

          <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1">Kongsi</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 line-clamp-1">{{ institution?.name }}</p>

          <!-- Share Options -->
          <div class="grid grid-cols-2 gap-3 mb-4">
            <button
              @click="shareWhatsApp"
              class="flex items-center gap-3 p-3 rounded-2xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors"
            >
              <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
              <span class="text-sm font-semibold">WhatsApp</span>
            </button>

            <button
              @click="copyLink"
              :class="['flex items-center gap-3 p-3 rounded-2xl transition-colors', copied ? 'bg-primary/10 text-primary dark:text-green-300' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100']"
            >
              <span class="material-icons-round text-xl flex-shrink-0">{{ copied ? 'check' : 'link' }}</span>
              <span class="text-sm font-semibold">{{ copied ? 'Disalin!' : 'Salin Pautan' }}</span>
            </button>

            <button
              v-if="qrImageUrl"
              @click="downloadQr"
              class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <span class="material-icons-round text-xl flex-shrink-0">download</span>
              <span class="text-sm font-semibold">Muat Turun QR</span>
            </button>

            <button
              @click="shareFacebook"
              class="flex items-center gap-3 p-3 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-colors"
            >
              <span class="material-icons-round text-xl flex-shrink-0">facebook</span>
              <span class="text-sm font-semibold">Facebook</span>
            </button>
          </div>

          <!-- URL Display -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 truncate font-mono">
            {{ shareUrl }}
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  institution: { type: Object, default: null },
  qrImageUrl: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue'])

const copied = ref(false)

const shareUrl = computed(() => {
  if (!props.institution) return ''
  return `${window.location.origin}/${props.institution.slug}`
})

function shareWhatsApp() {
  const text = encodeURIComponent(`Jom sedekah ke ${props.institution.name}! ${shareUrl.value}`)
  window.open(`https://wa.me/?text=${text}`, '_blank')
}

function shareFacebook() {
  const url = encodeURIComponent(shareUrl.value)
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank')
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback
    const el = document.createElement('textarea')
    el.value = shareUrl.value
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}

function downloadQr() {
  if (!props.qrImageUrl) return
  const link = document.createElement('a')
  link.href = props.qrImageUrl
  link.download = `qr-${props.institution.slug}.png`
  link.click()
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
