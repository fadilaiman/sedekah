<template>
  <AdminLayout :title="`Edit: ${institution.name}`">
    <Head :title="`Edit ${institution.name} — Admin`" />

    <div class="max-w-3xl">
      <div class="mb-6 flex items-center justify-between">
        <Link :href="route('admin.institutions.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition-colors">
          <span class="material-icons-round text-base">arrow_back</span>
          Kembali ke senarai
        </Link>
        <Link :href="route('institutions.show', institution.slug)" target="_blank" class="text-sm text-primary hover:underline flex items-center gap-1">
          <span class="material-icons-round text-base">open_in_new</span>
          Lihat awam
        </Link>
      </div>

      <div class="space-y-6">
        <!-- Institution Form -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
            <span class="material-icons-round text-primary text-lg">edit</span>
            Maklumat Institusi
          </h2>
          <InstitutionForm :form="form" :payment-methods="paymentMethods" :errors="form.errors" @submit="submit" />
        </div>

        <!-- QR Code Management -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
            <span class="material-icons-round text-primary text-lg">qr_code</span>
            Pengurusan QR Code
          </h2>

          <!-- Current QRs -->
          <div v-if="institution.qr_codes?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div v-for="qr in institution.qr_codes" :key="qr.id"
              class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 flex gap-4 items-start"
            >
              <img v-if="qr.qr_image_url" :src="qr.qr_image_url" :alt="`QR ${institution.name}`"
                class="w-20 h-20 object-contain rounded-lg bg-white border border-gray-100 p-1 flex-shrink-0"
              />
              <div class="flex-shrink-0 w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center" v-else>
                <span class="material-icons-round text-4xl text-gray-300">qr_code_2</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ qr.payment_method?.name }}</p>
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium inline-block mt-1', qr.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                  {{ qr.status === 'active' ? '● Aktif' : '○ Tidak Aktif' }}
                </span>
                <div class="flex gap-2 mt-3">
                  <form @submit.prevent="toggleQr(qr)">
                    <button type="submit" class="text-xs px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-primary/10 hover:text-primary text-gray-600 transition-colors">
                      {{ qr.status === 'active' ? 'Nyahaktif' : 'Aktifkan' }}
                    </button>
                  </form>
                  <button @click="confirmDeleteQr(qr)" class="text-xs px-2 py-1 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                    Padam
                  </button>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 mb-5">Tiada QR code dimuat naik.</p>

          <!-- Upload New QR -->
          <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Muat Naik QR Code Baru</h3>
            <form @submit.prevent="uploadQr" class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Kaedah Pembayaran</label>
                <select v-model="qrForm.payment_method_id" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary">
                  <option value="">-- Pilih --</option>
                  <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Imej QR (JPEG/PNG, maks 100KB)</label>
                <div
                  @click="$refs.qrFileInput.click()"
                  @dragover.prevent
                  @drop.prevent="onQrDrop"
                  :class="['border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-colors', qrPreview ? 'border-primary/50' : 'border-gray-200 dark:border-gray-700 hover:border-primary/40']"
                >
                  <img v-if="qrPreview" :src="qrPreview" class="w-24 h-24 object-contain mx-auto mb-2" />
                  <template v-else>
                    <span class="material-icons-round text-3xl text-gray-300 block mb-1">upload_file</span>
                    <p class="text-xs text-gray-400">Klik atau seret fail</p>
                  </template>
                </div>
                <input ref="qrFileInput" type="file" accept="image/jpeg,image/png" class="hidden" @change="onQrFileChange" />
                <p v-if="qrError" class="text-xs text-red-500 mt-1">{{ qrError }}</p>
              </div>
              <button type="submit" :disabled="!qrForm.payment_method_id || !qrForm.qr_image || uploading"
                class="btn-primary text-sm flex items-center gap-2 disabled:opacity-50"
              >
                <span v-if="uploading" class="material-icons-round animate-spin text-base">sync</span>
                <span v-else class="material-icons-round text-base">upload</span>
                {{ uploading ? 'Memuat naik...' : 'Muat Naik' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete QR Modal -->
    <Teleport to="body">
      <div v-if="deleteQrTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Padam QR Code?</h3>
          <p class="text-sm text-gray-500 text-center mb-5">QR code untuk <strong>{{ deleteQrTarget.payment_method?.name }}</strong> akan dipadam.</p>
          <div class="flex gap-3">
            <button @click="deleteQrTarget = null" class="flex-1 py-2 rounded-xl border border-gray-200 text-sm font-medium hover:bg-gray-50">Batal</button>
            <button @click="doDeleteQr" class="flex-1 py-2 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600">Padam</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import InstitutionForm from '@/Components/Admin/InstitutionForm.vue'

const props = defineProps({
  institution: { type: Object, required: true },
  paymentMethods: { type: Array, default: () => [] },
})

const form = useForm({
  name: props.institution.name,
  category: props.institution.category,
  state: props.institution.state,
  city: props.institution.city,
  address: props.institution.address || '',
  description: props.institution.description || '',
  website_url: props.institution.website_url || '',
  contact_email: props.institution.contact_email || '',
  contact_phone: props.institution.contact_phone || '',
  external_campaign_url: props.institution.external_campaign_url || '',
  url: props.institution.url || '',
  maps_url: props.institution.maps_url || '',
  is_verified: props.institution.is_verified || false,
})

function submit() {
  form.put(route('admin.institutions.update', props.institution.id))
}

// QR Management
const qrForm = reactive({ payment_method_id: '', qr_image: null })
const qrPreview = ref(null)
const qrError = ref('')
const uploading = ref(false)
const deleteQrTarget = ref(null)

function onQrFileChange(e) { setQrFile(e.target.files[0]) }
function onQrDrop(e) { setQrFile(e.dataTransfer.files[0]) }

function setQrFile(file) {
  if (!file) return
  if (!['image/jpeg', 'image/png'].includes(file.type)) { qrError.value = 'Hanya JPEG atau PNG'; return }
  if (file.size > 102400) { qrError.value = 'Fail terlalu besar (maks 100KB)'; return }
  qrError.value = ''
  qrForm.qr_image = file
  const reader = new FileReader()
  reader.onload = e => { qrPreview.value = e.target.result }
  reader.readAsDataURL(file)
}

async function uploadQr() {
  if (!qrForm.qr_image || !qrForm.payment_method_id) return
  uploading.value = true
  const data = new FormData()
  data.append('payment_method_id', qrForm.payment_method_id)
  data.append('qr_image', qrForm.qr_image)

  try {
    const response = await axios.post(route('admin.institutions.qr.store', props.institution.id), data)
    if (response.status === 302 || response.status === 200) {
      router.visit(route('admin.institutions.edit', props.institution.id))
    }
  } catch (e) {
    const errorMsg = e.response?.data?.message || e.response?.data?.errors?.qr_image?.[0] || 'Gagal memuat naik QR code'
    qrError.value = errorMsg
    console.error('QR upload failed:', e.response?.data || e.message)
  } finally {
    uploading.value = false
  }
}

function toggleQr(qr) {
  router.patch(route('admin.institutions.qr.toggle', [props.institution.id, qr.id]))
}

function confirmDeleteQr(qr) { deleteQrTarget.value = qr }
function doDeleteQr() {
  router.delete(route('admin.institutions.qr.destroy', [props.institution.id, deleteQrTarget.value.id]), {
    onFinish: () => { deleteQrTarget.value = null },
  })
}
</script>
