<template>
  <PublicLayout>
    <Head title="Hantar Institusi — Sedekah.online" />

    <div class="max-w-2xl mx-auto px-4 sm:px-6 pb-16">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Hantar Institusi</h1>
        <p class="text-gray-500 dark:text-gray-400">
          Bantu kami menambah masjid, surau atau institusi agama yang belum ada dalam senarai.
        </p>
      </div>

      <form @submit.prevent="submit" class="glass-panel rounded-3xl p-6 md:p-8 space-y-6">

        <!-- Submitter Info -->
        <div class="space-y-4">
          <h2 class="font-bold text-gray-800 dark:text-gray-100 text-base flex items-center gap-2">
            <span class="material-icons-round text-primary text-lg">person</span>
            Maklumat Penghantar
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email <span class="text-red-500">*</span></label>
              <input v-model="form.submitter_email" type="email" required
                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                placeholder="email@contoh.com" />
              <p v-if="errors.submitter_email" class="text-xs text-red-500 mt-1">{{ errors.submitter_email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama (pilihan)</label>
              <input v-model="form.submitter_name" type="text"
                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                placeholder="Nama anda" />
            </div>
          </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-700"></div>

        <!-- Institution Info -->
        <div class="space-y-4">
          <h2 class="font-bold text-gray-800 dark:text-gray-100 text-base flex items-center gap-2">
            <span class="material-icons-round text-primary text-lg">mosque</span>
            Maklumat Institusi
          </h2>

          <!-- Name + Duplicate Check -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama Institusi <span class="text-red-500">*</span></label>
            <input v-model="form.institution_name" @input="checkDuplicate" type="text" required
              class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
              placeholder="Masjid Al-Hidayah" />
            <!-- Duplicate Warning -->
            <div v-if="duplicates.length > 0" class="mt-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
              <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 flex items-center gap-1 mb-2">
                <span class="material-icons-round text-sm">warning</span>
                Institusi yang serupa mungkin sudah ada:
              </p>
              <div v-for="dup in duplicates" :key="dup.id" class="flex items-center justify-between text-xs text-amber-700 dark:text-amber-300 mb-1">
                <span>{{ dup.name }} — {{ dup.city }}, {{ dup.state }}</span>
                <Link :href="route('institutions.show', dup.slug)" target="_blank" class="underline hover:no-underline">Lihat</Link>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kategori <span class="text-red-500">*</span></label>
              <select v-model="form.institution_category" required
                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary">
                <option value="">-- Pilih --</option>
                <option v-for="cat in activeCategories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Negeri <span class="text-red-500">*</span></label>
              <select v-model="form.institution_state" required
                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary">
                <option value="">-- Pilih --</option>
                <option v-for="state in STATES" :key="state" :value="state">{{ state }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Bandar <span class="text-red-500">*</span></label>
              <input v-model="form.institution_city" type="text" required
                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                placeholder="Petaling Jaya" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Alamat (pilihan)</label>
            <input v-model="form.institution_address" type="text"
              class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
              placeholder="No 12, Jalan Utama..." />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Pautan Google Maps (pilihan)</label>
            <input v-model="form.institution_maps_url" type="url"
              class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
              placeholder="https://maps.google.com/..." />
          </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-700"></div>

        <!-- QR Upload -->
        <div class="space-y-4">
          <h2 class="font-bold text-gray-800 dark:text-gray-100 text-base flex items-center gap-2">
            <span class="material-icons-round text-primary text-lg">qr_code</span>
            QR Code <span class="text-red-500">*</span>
          </h2>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kaedah Pembayaran <span class="text-red-500">*</span></label>
            <select v-model="form.payment_method_id" required
              class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary">
              <option value="">-- Pilih kaedah --</option>
              <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
            </select>
          </div>

          <!-- File Upload Area -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Imej QR Code (JPEG/PNG, maks 100KB) <span class="text-red-500">*</span></label>
            <div
              @dragover.prevent
              @drop.prevent="onFileDrop"
              @click="$refs.fileInput.click()"
              :class="[
                'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all',
                isDragging ? 'border-primary bg-primary/5' : 'border-gray-200 dark:border-gray-700 hover:border-primary/50'
              ]"
            >
              <template v-if="qrPreview">
                <img :src="qrPreview" class="w-32 h-32 object-contain mx-auto mb-3 mix-blend-multiply dark:mix-blend-normal" />
                <p class="text-xs text-gray-500">{{ form.qr_image?.name }} ({{ qrFileSizeKb }}KB)</p>
                <p class="text-xs text-primary mt-1">Klik untuk tukar</p>
              </template>
              <template v-else>
                <span class="material-icons-round text-4xl text-gray-300 dark:text-gray-600 block mb-2">upload_file</span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Seret fail ke sini atau <span class="text-primary font-semibold">pilih fail</span></p>
                <p class="text-xs text-gray-400 mt-1">JPEG atau PNG, maksimum 100KB</p>
              </template>
            </div>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png" class="hidden" @change="onFileChange" />
            <p v-if="errors.qr_image" class="text-xs text-red-500 mt-1">{{ errors.qr_image }}</p>
          </div>
        </div>

        <!-- Submit -->
        <div v-if="successMessage" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm font-medium flex items-center gap-2">
          <span class="material-icons-round">check_circle</span>
          {{ successMessage }}
        </div>

        <button
          type="submit"
          :disabled="submitting || duplicates.length > 0"
          class="w-full btn-primary flex items-center justify-center gap-2 py-3 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="submitting" class="material-icons-round animate-spin text-base">sync</span>
          <span v-else class="material-icons-round text-base">send</span>
          {{ submitting ? 'Menghantar...' : 'Hantar untuk Semakan' }}
        </button>

        <p class="text-xs text-center text-gray-400">
          Penyerahan anda akan disemak oleh admin sebelum dipaparkan.
        </p>
      </form>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const STATES = [
  'W.P. Kuala Lumpur', 'W.P. Putrajaya', 'W.P. Labuan',
  'Selangor', 'Johor', 'Kedah', 'Kelantan', 'Melaka',
  'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis',
  'Pulau Pinang', 'Sabah', 'Sarawak', 'Terengganu',
]

const { activeCategories } = useCategories()

const paymentMethods = ref([])
const duplicates = ref([])
const submitting = ref(false)
const isDragging = ref(false)
const qrPreview = ref(null)
const qrFileSizeKb = ref(0)
const successMessage = ref('')
const errors = reactive({})

const form = reactive({
  submitter_email: '',
  submitter_name: '',
  institution_name: '',
  institution_category: '',
  institution_state: '',
  institution_city: '',
  institution_address: '',
  institution_maps_url: '',
  payment_method_id: '',
  qr_image: null,
})

let duplicateTimer = null

async function checkDuplicate() {
  if (form.institution_name.length < 3) { duplicates.value = []; return }
  clearTimeout(duplicateTimer)
  duplicateTimer = setTimeout(async () => {
    const res = await axios.get('/api/v1/institutions', { params: { search: form.institution_name, per_page: 3 } })
    duplicates.value = res.data.data || []
  }, 500)
}

function onFileChange(e) {
  const file = e.target.files[0]
  setFile(file)
}

function onFileDrop(e) {
  const file = e.dataTransfer.files[0]
  setFile(file)
}

function setFile(file) {
  if (!file) return
  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    errors.qr_image = 'Hanya JPEG atau PNG dibenarkan.'
    return
  }
  if (file.size > 102400) {
    errors.qr_image = 'Fail terlalu besar. Maksimum 100KB.'
    return
  }
  errors.qr_image = ''
  form.qr_image = file
  qrFileSizeKb.value = Math.round(file.size / 1024)
  const reader = new FileReader()
  reader.onload = e => { qrPreview.value = e.target.result }
  reader.readAsDataURL(file)
}

async function submit() {
  if (!form.qr_image) { errors.qr_image = 'Sila muat naik imej QR code.'; return }
  submitting.value = true
  Object.keys(errors).forEach(k => { errors[k] = '' })

  const data = new FormData()
  Object.entries(form).forEach(([k, v]) => { if (v) data.append(k, v) })

  try {
    await axios.post('/api/v1/submissions', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    router.visit(route('submit.thank-you'))
  } catch (e) {
    const errs = e.response?.data?.errors || {}
    Object.assign(errors, errs)
    if (e.response?.status === 429) {
      errors.general = 'Terlalu banyak percubaan. Sila cuba sebentar lagi.'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  const res = await axios.get('/api/v1/payment-methods')
  paymentMethods.value = res.data
})
</script>
