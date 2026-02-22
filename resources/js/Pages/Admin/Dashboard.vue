<template>
  <AdminLayout title="Dashboard">
    <Head title="Dashboard — Admin" />

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="stat in statCards" :key="stat.label"
        class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm"
      >
        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center mb-3', stat.bg]">
          <span :class="['material-icons-round text-xl', stat.color]">{{ stat.icon }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value.toLocaleString() }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ stat.label }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- By Category -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <span class="material-icons-round text-primary text-base">pie_chart</span>
          Mengikut Kategori
        </h2>
        <div class="space-y-3">
          <div v-for="(count, cat) in byCategory" :key="cat" class="flex items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0"></div>
            <span class="text-sm text-gray-600 dark:text-gray-300 flex-1 capitalize">{{ getCategoryLabel(cat) }}</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ count }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Submissions -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="material-icons-round text-primary text-base">inbox</span>
            Penyerahan Terkini
          </h2>
          <Link :href="route('admin.submissions.index')" class="text-xs text-primary hover:underline">Lihat semua</Link>
        </div>
        <div v-if="recentSubmissions.length === 0" class="text-sm text-gray-400 text-center py-4">Tiada penyerahan</div>
        <div v-else class="space-y-3">
          <div v-for="sub in recentSubmissions" :key="sub.id" class="flex items-start gap-3">
            <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0 mt-0.5', statusClass(sub.status)]">
              {{ sub.status }}
            </span>
            <div class="min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ sub.institution_name }}</p>
              <p class="text-xs text-gray-400">{{ sub.institution_state }} · {{ formatDate(sub.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Institutions -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="material-icons-round text-primary text-base">add_circle</span>
            Institusi Terbaru
          </h2>
          <Link :href="route('admin.institutions.index')" class="text-xs text-primary hover:underline">Lihat semua</Link>
        </div>
        <div v-if="recentInstitutions.length === 0" class="text-sm text-gray-400 text-center py-4">Tiada institusi</div>
        <div v-else class="space-y-3">
          <div v-for="inst in recentInstitutions" :key="inst.id" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
              <span class="material-icons-round text-primary text-sm">account_balance</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ inst.name }}</p>
              <p class="text-xs text-gray-400">{{ inst.state }} · {{ formatDate(inst.created_at) }}</p>
            </div>
            <Link :href="route('admin.institutions.edit', inst.id)" class="text-xs text-primary hover:underline flex-shrink-0">Edit</Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Top States -->
    <div class="mt-6 bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
      <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <span class="material-icons-round text-primary text-base">bar_chart</span>
        Negeri Teratas
      </h2>
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div v-for="(count, state) in byState" :key="state"
          class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center"
        >
          <p class="text-lg font-bold text-primary">{{ count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ state }}</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  stats: { type: Object, required: true },
  byCategory: { type: Object, default: () => ({}) },
  byState: { type: Object, default: () => ({}) },
  recentSubmissions: { type: Array, default: () => [] },
  recentInstitutions: { type: Array, default: () => [] },
})

const statCards = computed(() => [
  { label: 'Jumlah Institusi', value: props.stats.total_institutions, icon: 'account_balance', bg: 'bg-primary/10', color: 'text-primary' },
  { label: 'QR Code Aktif', value: props.stats.active_qr_codes, icon: 'qr_code_2', bg: 'bg-blue-50 dark:bg-blue-900/20', color: 'text-blue-500' },
  { label: 'Penyerahan Tertangguh', value: props.stats.pending_submissions, icon: 'pending', bg: 'bg-amber-50 dark:bg-amber-900/20', color: 'text-amber-500' },
  { label: 'Diluluskan Bulan Ini', value: props.stats.approved_this_month, icon: 'check_circle', bg: 'bg-green-50 dark:bg-green-900/20', color: 'text-green-500' },
])

const { getCategoryLabel } = useCategories()

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }[status] || 'bg-gray-100 text-gray-600'
}

function formatDate(dt) {
  return new Date(dt).toLocaleDateString('ms-MY', { day: 'numeric', month: 'short' })
}
</script>
