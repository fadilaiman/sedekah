<template>
  <AdminLayout title="Institusi">
    <Head title="Institusi — Admin" />

    <!-- Status Tabs -->
    <div class="flex gap-3 mb-6 border-b border-gray-200 dark:border-gray-700">
      <button
        @click="setVerificationStatus('unverified')"
        :class="[
          'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
          verified === 'unverified'
            ? 'border-primary text-primary'
            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
        ]"
      >
        Belum Disahkan
      </button>
      <button
        @click="setVerificationStatus('verified')"
        :class="[
          'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
          verified === 'verified'
            ? 'border-primary text-primary'
            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
        ]"
      >
        Sudah Disahkan
      </button>
      <button
        @click="setVerificationStatus('all')"
        :class="[
          'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
          verified === 'all'
            ? 'border-primary text-primary'
            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
        ]"
      >
        Semua
      </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="flex gap-2 flex-1">
        <input
          v-model="search"
          @input="debouncedFetch"
          type="text"
          placeholder="Cari nama atau bandar..."
          class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
        />
        <select v-model="category" @change="applyFilters" class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm focus:ring-primary focus:border-primary">
          <option value="">Semua Kategori</option>
          <option v-for="cat in allCategories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
        </select>
      </div>
      <Link :href="route('admin.institutions.create')" class="btn-primary flex items-center gap-2 text-sm whitespace-nowrap">
        <span class="material-icons-round text-base">add</span>
        Tambah Institusi
      </Link>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
            <tr>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 hidden sm:table-cell">Kategori</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 hidden md:table-cell">Lokasi</th>
              <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">QR</th>
              <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 hidden lg:table-cell">Status</th>
              <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-if="institutions.data.length === 0">
              <td colspan="5" class="px-4 py-10 text-center text-gray-400">Tiada institusi dijumpai</td>
            </tr>
            <tr v-for="inst in institutions.data" :key="inst.id" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900 dark:text-white">{{ inst.name }}</p>
                <p class="text-xs text-gray-400 sm:hidden">{{ getCategoryLabel(inst.category) }}</p>
              </td>
              <td class="px-4 py-3 hidden sm:table-cell">
                <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-medium">
                  {{ getCategoryLabel(inst.category) }}
                </span>
              </td>
              <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-gray-400">{{ inst.city }}, {{ inst.state }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', inst.qr_codes_count > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                  {{ inst.qr_codes_count || 0 }}
                </span>
              </td>
              <td class="px-4 py-3 text-center hidden lg:table-cell">
                <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', inst.verified_at ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700']">
                  {{ inst.verified_at ? 'Disahkan' : 'Belum' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('institutions.show', inst.slug)" target="_blank"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
                    title="Lihat"
                  ><span class="material-icons-round text-base">open_in_new</span></Link>
                  <Link :href="route('admin.institutions.edit', inst.id)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
                    title="Edit"
                  ><span class="material-icons-round text-base">edit</span></Link>
                  <button @click="confirmDelete(inst)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Padam"
                  ><span class="material-icons-round text-base">delete</span></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="institutions.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
        <p class="text-sm text-gray-500">
          Menunjukkan {{ institutions.from }}–{{ institutions.to }} daripada {{ institutions.total }}
        </p>
        <div class="flex gap-1">
          <Link
            v-for="link in institutions.links"
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
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
          <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="material-icons-round text-red-500 text-2xl">warning</span>
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Padam Institusi?</h3>
          <p class="text-sm text-gray-500 text-center mb-5">
            <strong>{{ deleteTarget.name }}</strong> akan dipadamkan. Tindakan ini boleh dipulihkan melalui pangkalan data.
          </p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="flex-1 py-2 rounded-xl border border-gray-200 text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <form :action="route('admin.institutions.destroy', deleteTarget.id)" method="POST" class="flex-1">
              <input type="hidden" name="_method" value="DELETE" />
              <input type="hidden" name="_token" :value="csrfToken" />
              <button type="submit" class="w-full py-2 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-colors">Padam</button>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  institutions: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search || '')
const category = ref(props.filters.category || '')
const verified = ref(props.filters.verified || 'unverified')
const deleteTarget = ref(null)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

const { allCategories, getCategoryLabel } = useCategories()

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => applyFilters(), 400)
}

function applyFilters() {
  router.get(route('admin.institutions.index'), {
    search: search.value || undefined,
    category: category.value || undefined,
    verified: verified.value || 'unverified',
  }, { preserveState: true, replace: true })
}

function setVerificationStatus(status) {
  verified.value = status
  applyFilters()
}

function confirmDelete(inst) {
  deleteTarget.value = inst
}
</script>
