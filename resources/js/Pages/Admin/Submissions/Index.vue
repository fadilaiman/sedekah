<template>
  <AdminLayout title="Penyerahan">
    <Head title="Penyerahan — Admin" />

    <!-- Status Tabs -->
    <div class="flex gap-2 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        @click="setStatus(tab.value)"
        :class="[
          'px-4 py-2 rounded-full text-sm font-semibold transition-colors',
          filters.status === tab.value
            ? 'bg-primary text-white shadow-md'
            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-primary/50'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="submissions.data.length === 0" class="text-center py-20 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800">
      <span class="material-icons-round text-6xl text-gray-300 dark:text-gray-600 block mb-3">inbox</span>
      <p class="text-gray-500 font-medium">Tiada penyerahan dalam status ini</p>
    </div>

    <!-- Submissions List -->
    <div v-else class="space-y-4">
      <div
        v-for="sub in submissions.data"
        :key="sub.id"
        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5"
      >
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- QR Image -->
          <div class="flex-shrink-0">
            <div v-if="sub.qr_image_url" class="w-24 h-24 bg-white border border-gray-200 rounded-xl p-2 flex items-center justify-center">
              <img :src="sub.qr_image_url" :alt="sub.institution_name" class="w-full h-full object-contain" />
            </div>
            <div v-else class="w-24 h-24 bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center">
              <span class="material-icons-round text-4xl text-gray-300">qr_code_2</span>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3 mb-2">
              <div>
                <h3 class="font-bold text-gray-900 dark:text-white">{{ sub.institution_name }}</h3>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-medium">{{ getCategoryLabel(sub.institution_category) }}</span>
                  <span class="text-xs text-gray-500">{{ sub.institution_city }}, {{ sub.institution_state }}</span>
                </div>
              </div>
              <span :class="['text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0', statusClass(sub.status)]">
                {{ sub.status }}
              </span>
            </div>

            <div class="text-xs text-gray-400 space-y-0.5 mb-3">
              <p v-if="sub.submitter_email"><span class="font-medium">Penghantar:</span> {{ sub.submitter_email }}</p>
              <p v-if="sub.submitter_name"><span class="font-medium">Nama:</span> {{ sub.submitter_name }}</p>
              <p><span class="font-medium">Dihantar:</span> {{ formatDate(sub.created_at) }}</p>
              <p v-if="sub.institution_address">{{ sub.institution_address }}</p>
              <p v-if="sub.institution_maps_url">
                <a :href="sub.institution_maps_url" target="_blank" class="text-primary hover:underline">Lihat Google Maps</a>
              </p>
            </div>

            <!-- Duplicate Warning -->
            <div v-if="sub.duplicates?.length > 0" class="mb-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
              <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 flex items-center gap-1 mb-1">
                <span class="material-icons-round text-sm">warning</span>
                Mungkin duplikasi dengan institusi sedia ada:
              </p>
              <div v-for="dup in sub.duplicates" :key="dup.id" class="text-xs text-amber-700 dark:text-amber-300 flex justify-between">
                <span>{{ dup.name }} — {{ dup.city }}, {{ dup.state }}</span>
                <a :href="route('institutions.show', dup.slug)" target="_blank" class="underline">Lihat</a>
              </div>
            </div>

            <!-- Actions (pending only) -->
            <div v-if="sub.status === 'pending'" class="flex flex-wrap gap-2">
              <form @submit.prevent="approve(sub)" class="inline">
                <button type="submit" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-green-500 text-white text-sm font-semibold hover:bg-green-600 transition-colors">
                  <span class="material-icons-round text-base">check_circle</span>
                  Luluskan
                </button>
              </form>
              <button @click="openRejectModal(sub)" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition-colors">
                <span class="material-icons-round text-base">cancel</span>
                Tolak
              </button>
            </div>

            <!-- Reviewed info -->
            <div v-else-if="sub.reviewed_at" class="text-xs text-gray-400">
              Disemak: {{ formatDate(sub.reviewed_at) }}
              <span v-if="sub.rejection_reason"> · <em>{{ sub.rejection_reason }}</em></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="submissions.last_page > 1" class="flex justify-center mt-6 gap-1">
      <Link
        v-for="link in submissions.links"
        :key="link.label"
        :href="link.url || '#'"
        v-html="link.label"
        :class="[
          'px-3 py-1.5 rounded-lg text-sm transition-colors',
          link.active ? 'bg-primary text-white font-semibold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800',
          !link.url && 'opacity-40 pointer-events-none'
        ]"
      />
    </div>

    <!-- Reject Modal -->
    <Teleport to="body">
      <div v-if="rejectTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Tolak Penyerahan</h3>
          <p class="text-sm text-gray-500 mb-4">Penyerahan untuk <strong>{{ rejectTarget.institution_name }}</strong></p>
          <textarea v-model="rejectReason" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary mb-4" placeholder="Sebab penolakan (pilihan)..."></textarea>
          <div class="flex gap-3">
            <button @click="rejectTarget = null" class="flex-1 py-2 rounded-xl border border-gray-200 text-sm font-medium hover:bg-gray-50">Batal</button>
            <button @click="doReject" class="flex-1 py-2 rounded-xl bg-red-500 text-white text-sm font-semibold hover:bg-red-600">Tolak</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  submissions: { type: Object, required: true },
  filters: { type: Object, default: () => ({ status: 'pending' }) },
})

const rejectTarget = ref(null)
const rejectReason = ref('')

const tabs = [
  { label: 'Menunggu', value: 'pending' },
  { label: 'Diluluskan', value: 'approved' },
  { label: 'Ditolak', value: 'rejected' },
]

const { getCategoryLabel } = useCategories()

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }[status] || 'bg-gray-100 text-gray-600'
}

function formatDate(dt) {
  return new Date(dt).toLocaleDateString('ms-MY', { day: 'numeric', month: 'short', year: 'numeric' })
}

function setStatus(status) {
  router.get(route('admin.submissions.index'), { status }, { preserveState: true, replace: true })
}

function approve(sub) {
  router.post(route('admin.submissions.approve', sub.id))
}

function openRejectModal(sub) {
  rejectTarget.value = sub
  rejectReason.value = ''
}

function doReject() {
  router.post(route('admin.submissions.reject', rejectTarget.value.id), {
    reason: rejectReason.value,
  }, {
    onSuccess: () => { rejectTarget.value = null },
  })
}
</script>
