<template>
  <AdminLayout title="Kategori">
    <Head title="Kategori — Admin" />

    <!-- Toolbar -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kategori Institusi</h1>
      <button
        @click="showModal = true"
        class="btn-primary flex items-center gap-2"
      >
        <span class="material-icons-round text-base">add</span>
        Tambah Kategori
      </button>
    </div>

    <!-- Categories Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
            <tr>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 w-10">Susunan</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Icon</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Label</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Slug</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Warna</th>
              <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
              <!-- Order Buttons -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <button
                    v-if="categories.indexOf(cat) > 0"
                    @click="moveUp(cat)"
                    class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 transition-colors"
                    title="Ke atas"
                  >
                    <span class="material-icons-round text-sm">arrow_upward</span>
                  </button>
                  <button
                    v-if="categories.indexOf(cat) < categories.length - 1"
                    @click="moveDown(cat)"
                    class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 transition-colors"
                    title="Ke bawah"
                  >
                    <span class="material-icons-round text-sm">arrow_downward</span>
                  </button>
                </div>
              </td>

              <!-- Icon Preview -->
              <td class="px-4 py-3">
                <div :class="['w-8 h-8 rounded flex items-center justify-center', getCategoryBg(cat.value)]">
                  <span :class="['material-icons-round text-lg', getCategoryIconClass(cat.value)]">{{ cat.icon }}</span>
                </div>
              </td>

              <!-- Label -->
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ cat.label }}</td>

              <!-- Slug -->
              <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ cat.value }}</td>

              <!-- Color Swatch -->
              <td class="px-4 py-3">
                <div
                  :style="{
                    backgroundColor: getColorHex(cat.color),
                  }"
                  class="w-6 h-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
                  :title="cat.color"
                ></div>
              </td>

              <!-- Status Badge -->
              <td class="px-4 py-3 text-center">
                <span v-if="cat.active" class="text-xs px-2.5 py-1 rounded-full font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                  Aktif
                </span>
                <span v-else class="text-xs px-2.5 py-1 rounded-full font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                  Tidak Aktif
                </span>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="editCategory(cat)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
                    title="Edit"
                  >
                    <span class="material-icons-round text-base">edit</span>
                  </button>
                  <button
                    @click="confirmDelete(cat)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Padam"
                  >
                    <span class="material-icons-round text-base">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ editingId ? 'Edit Kategori' : 'Tambah Kategori' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-icons-round">close</span>
              </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Value (slug) - only on create -->
              <div v-if="!editingId">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                  Slug <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.value"
                  type="text"
                  required
                  placeholder="e.g., mosque"
                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                />
                <p class="text-xs text-gray-400 mt-1">Tidak boleh ditukar setelah dibuat</p>
              </div>

              <!-- Label -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                  Nama Label <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.label"
                  type="text"
                  required
                  placeholder="e.g., Masjid"
                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                />
              </div>

              <!-- Color Picker -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                  Warna <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-4 gap-2">
                  <button
                    v-for="col in COLORS"
                    :key="col"
                    type="button"
                    @click="formData.color = col"
                    :class="[
                      'w-full aspect-square rounded-xl transition-all border-2',
                      formData.color === col
                        ? 'border-gray-900 dark:border-white ring-2 ring-primary'
                        : 'border-gray-200 dark:border-gray-700'
                    ]"
                    :style="{ backgroundColor: getColorHex(col) }"
                    :title="col"
                  >
                    <span v-if="formData.color === col" class="material-icons-round text-white text-lg" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5)">check</span>
                  </button>
                </div>
              </div>

              <!-- Icon Picker -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                  Icon <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="iconSearch"
                  type="text"
                  placeholder="Cari icon..."
                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary mb-2"
                />
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 max-h-48 overflow-y-auto grid grid-cols-4 gap-1.5">
                  <button
                    v-for="icon in filteredIcons"
                    :key="icon"
                    type="button"
                    @click="formData.icon = icon"
                    :class="[
                      'aspect-square flex flex-col items-center justify-center rounded-lg text-xs gap-1 transition-all border-2',
                      formData.icon === icon
                        ? 'bg-primary/20 border-primary dark:border-primary'
                        : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 hover:border-primary'
                    ]"
                    :title="icon"
                  >
                    <span class="material-icons-round text-sm">{{ icon }}</span>
                    <span class="text-[10px] text-gray-600 dark:text-gray-400">{{ icon.substring(0, 5) }}</span>
                  </button>
                </div>
              </div>

              <!-- Live Preview -->
              <div v-if="formData.icon && formData.color && formData.label" class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Pratonton</p>
                <div
                  :class="[
                    'w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-2',
                    getCategoryBg(formData.value)
                  ]"
                >
                  <span :class="['material-icons-round text-2xl', getCategoryIconClass(formData.value)]">{{ formData.icon }}</span>
                </div>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold inline-block" :class="getCategoryBadge(formData.value)">
                  {{ formData.label }}
                </span>
              </div>

              <!-- Active Toggle -->
              <label class="flex items-center gap-3 cursor-pointer">
                <input v-model="formData.active" type="checkbox" class="w-4 h-4 rounded text-primary focus:ring-primary" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Aktif</span>
              </label>

              <!-- Submit Buttons -->
              <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  class="flex-1 py-2 rounded-xl bg-primary text-white text-sm font-medium hover:bg-primary-light transition-colors disabled:opacity-50"
                  :disabled="isSubmitting"
                >
                  {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <!-- Delete Confirmation -->
      <Transition name="fade">
        <div v-if="deleteTarget" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <span class="material-icons-round text-red-500 text-2xl">warning</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Padam Kategori?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">
              <strong>{{ deleteTarget.label }}</strong> akan dipadamkan. Tindakan ini tidak boleh dipulihkan.
            </p>
            <div v-if="deleteTarget.isInUse" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 mb-5 text-sm text-amber-700 dark:text-amber-300 flex gap-2">
              <span class="material-icons-round text-base flex-shrink-0">warning</span>
              <span>Kategori ini sedang digunakan oleh {{ deleteTarget.usageCount }} institusi.</span>
            </div>
            <div class="flex gap-3">
              <button
                @click="deleteTarget = null"
                class="flex-1 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
              >
                Batal
              </button>
              <button
                v-if="!deleteTarget.isInUse"
                @click="doDelete"
                class="flex-1 py-2 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-colors"
              >
                Padam
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useCategories } from '@/Composables/useCategories'

const props = defineProps({
  categories: { type: Array, required: true },
})

const { getCategoryBg, getCategoryIconClass, getCategoryBadge } = useCategories()

const showModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)
const isSubmitting = ref(false)
const iconSearch = ref('')

const COLORS = ['green', 'blue', 'amber', 'pink', 'red', 'purple', 'orange', 'gray']

const ICONS = [
  'mosque', 'home', 'church', 'groups', 'diversity_3', 'people',
  'child_care', 'elderly', 'family_restroom', 'school', 'local_library',
  'menu_book', 'auto_stories', 'local_hospital', 'medical_services',
  'healing', 'health_and_safety', 'volunteer_activism', 'favorite',
  'handshake', 'card_giftcard', 'savings', 'account_balance', 'apartment',
  'business', 'cottage', 'domain', 'eco', 'water', 'restaurant',
  'food_bank', 'local_dining', 'place', 'star', 'work', 'public',
  'category', 'label', 'flag', 'info', 'support', 'more_horiz',
  'sports_kabaddi', 'accessibility', 'nature', 'psychology', 'lightbulb',
  'campaign', 'hub',
]

const formData = ref({
  value: '',
  label: '',
  icon: '',
  color: 'green',
  order: 0,
  active: true,
})

const filteredIcons = computed(() => {
  const search = iconSearch.value.toLowerCase()
  return search
    ? ICONS.filter(icon => icon.includes(search))
    : ICONS
})

const categories = ref(props.categories)

function getColorHex(color) {
  const colors = {
    green: '#10b981',
    blue: '#3b82f6',
    amber: '#f59e0b',
    pink: '#ec4899',
    red: '#ef4444',
    purple: '#a855f7',
    orange: '#f97316',
    gray: '#6b7280',
  }
  return colors[color] || '#6b7280'
}

function editCategory(cat) {
  editingId.value = cat.id
  formData.value = {
    value: cat.value,
    label: cat.label,
    icon: cat.icon,
    color: cat.color,
    order: cat.order,
    active: cat.active,
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  iconSearch.value = ''
  resetForm()
}

function resetForm() {
  formData.value = {
    value: '',
    label: '',
    icon: '',
    color: 'green',
    order: 0,
    active: true,
  }
}

function confirmDelete(cat) {
  // Simple check - in real app, backend would do proper check
  deleteTarget.value = {
    ...cat,
    isInUse: false,
  }
}

function doDelete() {
  if (!deleteTarget.value) return
  router.delete(route('admin.categories.destroy', deleteTarget.value.id), {
    onSuccess: () => {
      deleteTarget.value = null
      window.location.reload()
    },
  })
}

function moveUp(cat) {
  const index = categories.value.indexOf(cat)
  if (index > 0) {
    const order = categories.value[index - 1].order
    categories.value[index - 1].order = cat.order
    cat.order = order
    updateOrder()
  }
}

function moveDown(cat) {
  const index = categories.value.indexOf(cat)
  if (index < categories.value.length - 1) {
    const order = categories.value[index + 1].order
    categories.value[index + 1].order = cat.order
    cat.order = order
    updateOrder()
  }
}

function updateOrder() {
  const orderMap = {}
  categories.value.forEach(cat => {
    orderMap[cat.id] = cat.order
  })
  router.post(route('admin.categories.reorder'), { order: orderMap }, {
    preserveScroll: true,
    onSuccess: () => {
      // Reload the page to get fresh data from server
      window.location.reload()
    },
  })
}

async function submitForm() {
  isSubmitting.value = true

  try {
    if (editingId.value) {
      // Update
      await router.put(route('admin.categories.update', editingId.value), formData.value, {
        onSuccess: () => {
          closeModal()
          window.location.reload()
        },
      })
    } else {
      // Create
      await router.post(route('admin.categories.store'), formData.value, {
        onSuccess: () => {
          closeModal()
          window.location.reload()
        },
      })
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
