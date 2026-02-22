<template>
  <form @submit.prevent="$emit('submit')" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Name -->
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama Institusi <span class="text-red-500">*</span></label>
        <input v-model="form.name" type="text" required class="field" placeholder="Masjid Al-Hidayah" />
        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
      </div>

      <!-- Category -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kategori <span class="text-red-500">*</span></label>
        <select v-model="form.category" required class="field">
          <option value="">-- Pilih --</option>
          <option v-for="cat in allCategories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
        </select>
        <p v-if="errors.category" class="text-xs text-red-500 mt-1">{{ errors.category }}</p>
      </div>

      <!-- State -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Negeri <span class="text-red-500">*</span></label>
        <select v-model="form.state" required class="field">
          <option value="">-- Pilih --</option>
          <option v-for="state in STATES" :key="state" :value="state">{{ state }}</option>
        </select>
        <p v-if="errors.state" class="text-xs text-red-500 mt-1">{{ errors.state }}</p>
      </div>

      <!-- City -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Bandar <span class="text-red-500">*</span></label>
        <input v-model="form.city" type="text" required class="field" placeholder="Petaling Jaya" />
        <p v-if="errors.city" class="text-xs text-red-500 mt-1">{{ errors.city }}</p>
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Alamat</label>
        <input v-model="form.address" type="text" class="field" placeholder="No 12, Jalan Utama..." />
      </div>

      <!-- Description -->
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Penerangan</label>
        <textarea v-model="form.description" rows="3" class="field" placeholder="Penerangan ringkas..."></textarea>
      </div>

      <!-- Website -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Laman Web</label>
        <input v-model="form.website_url" type="url" class="field" placeholder="https://example.com" />
        <p v-if="errors.website_url" class="text-xs text-red-500 mt-1">{{ errors.website_url }}</p>
      </div>

      <!-- Contact Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email Hubungan</label>
        <input v-model="form.contact_email" type="email" class="field" placeholder="info@example.com" />
        <p v-if="errors.contact_email" class="text-xs text-red-500 mt-1">{{ errors.contact_email }}</p>
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">No. Telefon</label>
        <input v-model="form.contact_phone" type="text" class="field" placeholder="+60123456789" />
      </div>

      <!-- Campaign URL -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Pautan Kempen Rasmi</label>
        <input v-model="form.external_campaign_url" type="url" class="field" placeholder="https://..." />
        <p v-if="errors.external_campaign_url" class="text-xs text-red-500 mt-1">{{ errors.external_campaign_url }}</p>
      </div>

      <!-- Donation URL -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Pautan Sumbangan (ToyyibPay, iPay88, etc.)</label>
        <input v-model="form.url" type="url" class="field" placeholder="https://toyyibpay.com/..." />
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pautan lengkap untuk platform sumbangan (ToyyibPay, iPay88, atau platform lain)</p>
        <p v-if="errors.url" class="text-xs text-red-500 mt-1">{{ errors.url }}</p>
      </div>

      <!-- Maps URL -->
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Pautan Google Maps</label>
        <input v-model="form.maps_url" type="url" class="field" placeholder="https://maps.google.com/..." />
        <p v-if="errors.maps_url" class="text-xs text-red-500 mt-1">{{ errors.maps_url }}</p>
      </div>

      <!-- Is Verified -->
      <div class="sm:col-span-2">
        <label class="flex items-center gap-3 cursor-pointer">
          <input v-model="form.is_verified" type="checkbox" class="w-4 h-4 rounded text-primary focus:ring-primary" />
          <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Institusi Disahkan</span>
        </label>
      </div>
    </div>

    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex gap-3">
      <button type="submit" :disabled="form.processing" class="btn-primary flex items-center gap-2 disabled:opacity-50">
        <span v-if="form.processing" class="material-icons-round animate-spin text-base">sync</span>
        <span v-else class="material-icons-round text-base">save</span>
        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { useCategories } from '@/Composables/useCategories'

defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  paymentMethods: { type: Array, default: () => [] },
})

defineEmits(['submit'])

const { allCategories } = useCategories()

const STATES = [
  'W.P. Kuala Lumpur', 'W.P. Putrajaya', 'W.P. Labuan',
  'Selangor', 'Johor', 'Kedah', 'Kelantan', 'Melaka',
  'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis',
  'Pulau Pinang', 'Sabah', 'Sarawak', 'Terengganu',
]
</script>

<style scoped>
.field {
  @apply w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary;
}
</style>
