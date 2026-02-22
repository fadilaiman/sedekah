<template>
  <AdminLayout title="Institusi Pilihan">
    <Head title="Institusi Pilihan — Admin" />

    <div class="max-w-4xl">
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        Pilih sehingga <strong>8</strong> institusi untuk dipaparkan di halaman utama. Susun semula dengan menyeret.
      </p>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Selected (Sortable) -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5">
          <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-icons-round text-primary text-base">star</span>
            Dipilih ({{ selected.length }}/8)
          </h2>

          <div v-if="selected.length === 0" class="text-sm text-gray-400 text-center py-8 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
            Tiada institusi dipilih
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="(inst, idx) in selected"
              :key="inst.id"
              draggable="true"
              @dragstart="dragStart(idx)"
              @dragover.prevent="dragOver(idx)"
              @dragend="dragEnd"
              :class="['flex items-center gap-3 p-3 rounded-xl border transition-colors cursor-grab active:cursor-grabbing', dragOverIndex === idx ? 'border-primary bg-primary/5' : 'border-gray-100 dark:border-gray-700 hover:border-gray-200']"
            >
              <span class="material-icons-round text-gray-300 text-base flex-shrink-0">drag_indicator</span>
              <div class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center flex-shrink-0">{{ idx + 1 }}</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ inst.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ inst.city }}, {{ inst.state }}</p>
              </div>
              <button @click="removeSelected(idx)" class="w-7 h-7 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center flex-shrink-0">
                <span class="material-icons-round text-base">close</span>
              </button>
            </div>
          </div>

          <!-- Save -->
          <button
            @click="save"
            :disabled="saving"
            class="mt-5 w-full btn-primary flex items-center justify-center gap-2 disabled:opacity-50"
          >
            <span v-if="saving" class="material-icons-round animate-spin text-base">sync</span>
            <span v-else class="material-icons-round text-base">save</span>
            {{ saving ? 'Menyimpan...' : 'Simpan Senarai' }}
          </button>
        </div>

        <!-- Institution Picker -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5">
          <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-icons-round text-primary text-base">search</span>
            Cari Institusi
          </h2>
          <input
            v-model="searchPicker"
            type="text"
            placeholder="Nama institusi..."
            class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary mb-3"
          />
          <div class="space-y-1.5 max-h-80 overflow-y-auto pr-1">
            <button
              v-for="inst in filteredInstitutions"
              :key="inst.id"
              @click="addSelected(inst)"
              :disabled="isSelected(inst.id) || selected.length >= 8"
              :class="[
                'w-full flex items-center gap-3 p-2.5 rounded-xl text-left transition-colors',
                isSelected(inst.id)
                  ? 'bg-primary/10 text-primary cursor-default'
                  : selected.length >= 8
                    ? 'opacity-40 cursor-not-allowed'
                    : 'hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200'
              ]"
            >
              <span class="material-icons-round text-sm flex-shrink-0">{{ isSelected(inst.id) ? 'check_circle' : 'radio_button_unchecked' }}</span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium truncate">{{ inst.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ inst.city }}, {{ inst.state }}</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  featured: { type: Array, default: () => [] },
  institutions: { type: Array, default: () => [] },
})

// Initialize selected from existing featured (preserving order)
const selected = ref(
  props.featured
    .filter(f => f.institution)
    .map(f => f.institution)
)

const searchPicker = ref('')
const saving = ref(false)
const dragIndex = ref(null)
const dragOverIndex = ref(null)

const filteredInstitutions = computed(() => {
  const q = searchPicker.value.toLowerCase()
  return props.institutions.filter(i =>
    !q || i.name.toLowerCase().includes(q) || i.city.toLowerCase().includes(q)
  )
})

function isSelected(id) { return selected.value.some(s => s.id === id) }

function addSelected(inst) {
  if (isSelected(inst.id) || selected.value.length >= 8) return
  selected.value.push(inst)
}

function removeSelected(idx) { selected.value.splice(idx, 1) }

// Drag-to-reorder
function dragStart(idx) { dragIndex.value = idx }
function dragOver(idx) { dragOverIndex.value = idx }
function dragEnd() {
  if (dragIndex.value !== null && dragOverIndex.value !== null && dragIndex.value !== dragOverIndex.value) {
    const arr = [...selected.value]
    const [item] = arr.splice(dragIndex.value, 1)
    arr.splice(dragOverIndex.value, 0, item)
    selected.value = arr
  }
  dragIndex.value = null
  dragOverIndex.value = null
}

function save() {
  saving.value = true
  router.post(route('admin.featured.update'), {
    institution_ids: selected.value.map(i => i.id),
  }, {
    onFinish: () => { saving.value = false },
  })
}
</script>
