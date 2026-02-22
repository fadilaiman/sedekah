<template>
  <PublicLayout>
    <Head :title="`${institution.name} — Sedekah.online`" />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

      <!-- Back Button -->
      <Link :href="route('institutions.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-green-400 transition-colors mb-6 group">
        <span class="material-icons-round text-lg group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
        Kembali ke senarai
      </Link>

      <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <!-- ── Left Column: Info ─────────────────────────── -->
        <div class="lg:col-span-2 flex flex-col gap-5">

          <!-- Institution Profile Card -->
          <div class="glass-panel rounded-3xl p-6">
            <!-- Category Icon + Badge -->
            <div class="flex items-start justify-between mb-4">
              <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm', getCategoryBg(institution.category)]">
                <span :class="['material-icons-round text-3xl', getCategoryIconClass(institution.category)]">{{ getCategoryIcon(institution.category) }}</span>
              </div>
              <span class="text-xs px-3 py-1 rounded-full font-semibold bg-primary/10 text-primary dark:text-green-300">
                {{ getCategoryLabel(institution.category) }}
              </span>
            </div>

            <!-- Name -->
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 leading-tight">
              {{ institution.name }}
            </h1>

            <!-- Location -->
            <div class="flex items-center gap-1.5 text-secondary font-medium text-sm mb-4">
              <span class="material-icons-round text-base">location_on</span>
              {{ institution.city }}, {{ institution.state }}
            </div>

            <!-- Description -->
            <p v-if="institution.description" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
              {{ institution.description }}
            </p>

            <!-- Details list -->
            <div class="space-y-2.5 text-sm">
              <a
                v-if="institution.maps_url"
                :href="institution.maps_url"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-green-400 transition-colors group"
              >
                <span class="material-icons-round text-base text-primary">map</span>
                <span class="group-hover:underline">Google Maps</span>
                <span class="material-icons-round text-xs text-gray-400 ml-auto">open_in_new</span>
              </a>

              <a
                v-if="institution.website_url"
                :href="institution.website_url"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-green-400 transition-colors group"
              >
                <span class="material-icons-round text-base text-primary">language</span>
                <span class="group-hover:underline truncate">Laman Web</span>
                <span class="material-icons-round text-xs text-gray-400 ml-auto">open_in_new</span>
              </a>

              <a
                v-if="institution.contact_phone"
                :href="`tel:${institution.contact_phone}`"
                class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
              >
                <span class="material-icons-round text-base text-primary">phone</span>
                {{ institution.contact_phone }}
              </a>

              <a
                v-if="institution.contact_email"
                :href="`mailto:${institution.contact_email}`"
                class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
              >
                <span class="material-icons-round text-base text-primary">email</span>
                {{ institution.contact_email }}
              </a>
            </div>

            <!-- External Campaign Link -->
            <a
              v-if="institution.external_campaign_url"
              :href="institution.external_campaign_url"
              target="_blank"
              rel="noopener"
              class="mt-5 flex items-center justify-center gap-2 w-full bg-secondary hover:bg-secondary-light text-white px-5 py-2.5 rounded-full font-semibold text-sm shadow-lg shadow-secondary/20 transition-all hover:-translate-y-0.5"
            >
              <span class="material-icons-round text-base">volunteer_activism</span>
              Halaman Derma Rasmi
              <span class="material-icons-round text-sm">open_in_new</span>
            </a>

            <!-- Donation URL Link -->
            <a
              v-if="institution.url"
              :href="institution.url"
              target="_blank"
              rel="noopener"
              class="mt-3 flex items-center justify-center gap-2 w-full bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-full font-semibold text-sm shadow-lg shadow-red-500/20 transition-all hover:-translate-y-0.5"
            >
              <span class="material-icons-round text-base">favorite</span>
              Sumbang Sekarang
              <span class="material-icons-round text-sm">open_in_new</span>
            </a>
          </div>

          <!-- Share Actions -->
          <div class="glass-panel rounded-3xl p-5">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Kongsi Institusi</p>
            <div class="flex gap-3">
              <button
                @click="shareWhatsApp"
                class="flex-1 flex items-center justify-center gap-2 p-2.5 rounded-2xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 transition-colors text-sm font-semibold"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
              </button>
              <button
                @click="copyLink"
                :class="['flex-1 flex items-center justify-center gap-2 p-2.5 rounded-2xl text-sm font-semibold transition-colors', copied ? 'bg-primary/10 text-primary dark:text-green-300' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100']"
              >
                <span class="material-icons-round text-base">{{ copied ? 'check' : 'link' }}</span>
                {{ copied ? 'Disalin!' : 'Salin' }}
              </button>
            </div>
          </div>
        </div>

        <!-- ── Right Column: QR Codes ────────────────────── -->
        <div class="lg:col-span-3">

          <!-- No QR State -->
          <div v-if="!qrCodes.length" class="glass-panel rounded-3xl p-10 text-center">
            <span class="material-icons-round text-6xl text-gray-300 dark:text-gray-600 block mb-3">qr_code_2</span>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Tiada QR code aktif buat masa ini.</p>
          </div>

          <!-- QR Code Display -->
          <template v-else>
            <!-- Payment Method Tabs -->
            <div v-if="qrCodes.length > 1" class="flex gap-2 mb-4 flex-wrap">
              <button
                v-for="(qr, i) in qrCodes"
                :key="qr.id"
                @click="activeQrIndex = i"
                :class="[
                  'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-all',
                  activeQrIndex === i
                    ? 'bg-primary text-white shadow-md'
                    : 'glass-panel text-gray-600 dark:text-gray-300 hover:bg-white'
                ]"
              >
                {{ qr.payment_method?.name || 'QR ' + (i + 1) }}
              </button>
            </div>

            <!-- Active QR Card -->
            <div class="glass-panel rounded-3xl p-6 text-center">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h2 class="font-bold text-gray-900 dark:text-white text-lg">
                    {{ activeQr.payment_method?.name || 'QR Code' }}
                  </h2>
                  <span :class="[
                    'text-xs px-2.5 py-0.5 rounded-full font-semibold mt-1 inline-block',
                    activeQr.status === 'active'
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                      : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                  ]">
                    {{ activeQr.status === 'active' ? '● Aktif' : '○ ' + activeQr.status }}
                  </span>
                </div>

                <div class="flex gap-2">
                  <button
                    @click="downloadQr"
                    class="w-9 h-9 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-all"
                    title="Muat Turun"
                  >
                    <span class="material-icons-round text-base">download</span>
                  </button>
                </div>
              </div>

              <!-- QR Image -->
              <div class="bg-white dark:bg-gray-800 p-6 md:p-10 rounded-2xl border border-gray-100 dark:border-gray-700 inline-block w-full max-w-xs mx-auto mb-4 shadow-inner">
                <img
                  v-if="activeQr.qr_image_url"
                  :src="activeQr.qr_image_url"
                  :alt="`QR ${institution.name}`"
                  class="w-full h-auto object-contain mix-blend-multiply dark:mix-blend-normal"
                />
                <div v-else class="w-full aspect-square flex items-center justify-center text-gray-300">
                  <span class="material-icons-round text-8xl">qr_code_2</span>
                </div>
              </div>

              <!-- Expected Amount -->
              <p v-if="activeQr.expected_amount" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                Jumlah: <span class="font-semibold text-primary">RM {{ activeQr.expected_amount }}</span>
              </p>

              <!-- Download Button -->
              <button
                @click="downloadQr"
                class="btn-primary text-sm mx-auto flex items-center gap-2 w-full max-w-xs justify-center"
              >
                <span class="material-icons-round text-base">download</span>
                Muat Turun QR Code
              </button>

              <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">
                Imbas kod QR ini menggunakan aplikasi e-dompet anda untuk menyumbang
              </p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  institution: { type: Object, required: true },
})

const { getCategoryBg, getCategoryIconClass, getCategoryIcon, getCategoryLabel } = useCategories()

// QR codes — only active ones on the public page
const qrCodes = computed(() => {
  return (props.institution.qr_codes || []).filter(q => q.status === 'active')
})

const activeQrIndex = ref(0)
const activeQr = computed(() => qrCodes.value[activeQrIndex.value] || {})

// Share
const copied = ref(false)
const shareUrl = computed(() => `${window.location.origin}/${props.institution.slug}`)

function shareWhatsApp() {
  const text = encodeURIComponent(`Jom sedekah ke ${props.institution.name}! ${shareUrl.value}`)
  window.open(`https://wa.me/?text=${text}`, '_blank')
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
  } catch {
    const el = document.createElement('textarea')
    el.value = shareUrl.value
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
  }
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

function downloadQr() {
  if (!activeQr.value.qr_image_url) return
  const link = document.createElement('a')
  link.href = activeQr.value.qr_image_url
  link.download = `qr-${props.institution.slug}.png`
  link.click()
}
</script>
