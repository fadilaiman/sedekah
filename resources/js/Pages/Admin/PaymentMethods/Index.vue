<template>
  <AdminLayout title="Kaedah Bayaran">
    <Head title="Kaedah Bayaran — Admin" />

    <!-- Toolbar -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kaedah Bayaran</h1>
      <button
        @click="openCreateModal"
        class="btn-primary flex items-center gap-2"
      >
        <span class="material-icons-round text-base">add</span>
        Tambah Kaedah Bayaran
      </button>
    </div>

    <!-- Payment Methods Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
            <tr>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Kod</th>
              <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">QR Codes</th>
              <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="pm in paymentMethods" :key="pm.id" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
              <!-- Code -->
              <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ pm.code }}</td>

              <!-- Name -->
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ pm.name }}</td>

              <!-- QR Codes Count -->
              <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ pm.qr_codes_count }}</td>

              <!-- Status Badge -->
              <td class="px-4 py-3 text-center">
                <span v-if="pm.active" class="text-xs px-2.5 py-1 rounded-full font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
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
                    @click="openEditModal(pm)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
                    title="Edit"
                  >
                    <span class="material-icons-round text-base">edit</span>
                  </button>
                  <button
                    @click="confirmDelete(pm)"
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
                {{ editingId ? 'Edit Kaedah Bayaran' : 'Tambah Kaedah Bayaran' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-icons-round">close</span>
              </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Code - only on create -->
              <div v-if="!editingId">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                  Kod <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.code"
                  type="text"
                  required
                  placeholder="e.g., tng"
                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                />
                <p class="text-xs text-gray-400 mt-1">Tidak boleh ditukar setelah dibuat</p>
              </div>

              <!-- Code display (read-only on edit) -->
              <div v-else>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kod</label>
                <div class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-mono text-gray-500 dark:text-gray-400">
                  {{ formData.code }}
                </div>
              </div>

              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                  Nama <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.name"
                  type="text"
                  required
                  placeholder="e.g., Touch 'n Go eWallet"
                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:ring-primary focus:border-primary"
                />
              </div>

              <!-- Active Toggle -->
              <label class="flex items-center gap-3 cursor-pointer">
                <input v-model="formData.active" type="checkbox" class="w-4 h-4 rounded text-primary focus:ring-primary" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Aktif</span>
              </label>

              <!-- Validation Errors -->
              <div v-if="Object.keys(errors).length" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 text-sm text-red-700 dark:text-red-300">
                <p v-for="(msg, field) in errors" :key="field">{{ msg }}</p>
              </div>

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
            <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Padam Kaedah Bayaran?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">
              <strong>{{ deleteTarget.name }}</strong> ({{ deleteTarget.code }}) akan dipadamkan.
            </p>
            <div v-if="deleteTarget.qr_codes_count > 0" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 mb-5 text-sm text-amber-700 dark:text-amber-300 flex gap-2">
              <span class="material-icons-round text-base flex-shrink-0">warning</span>
              <span>Kaedah bayaran ini digunakan oleh {{ deleteTarget.qr_codes_count }} QR code. Tidak boleh dipadamkan.</span>
            </div>
            <div class="flex gap-3">
              <button
                @click="deleteTarget = null"
                class="flex-1 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
              >
                Batal
              </button>
              <button
                v-if="deleteTarget.qr_codes_count === 0"
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
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  paymentMethods: { type: Array, required: true },
})

const page = usePage()
const errors = ref({})

const showModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)
const isSubmitting = ref(false)

const formData = ref({
  code: '',
  name: '',
  active: true,
})

function openCreateModal() {
  editingId.value = null
  formData.value = { code: '', name: '', active: true }
  errors.value = {}
  showModal.value = true
}

function openEditModal(pm) {
  editingId.value = pm.id
  formData.value = {
    code: pm.code,
    name: pm.name,
    active: pm.active,
  }
  errors.value = {}
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  errors.value = {}
}

function confirmDelete(pm) {
  deleteTarget.value = pm
}

function doDelete() {
  if (!deleteTarget.value) return
  router.delete(route('admin.payment-methods.destroy', deleteTarget.value.id), {
    onSuccess: () => {
      deleteTarget.value = null
    },
  })
}

function submitForm() {
  isSubmitting.value = true
  errors.value = {}

  const options = {
    onSuccess: () => {
      closeModal()
    },
    onError: (errs) => {
      errors.value = errs
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  }

  if (editingId.value) {
    router.put(route('admin.payment-methods.update', editingId.value), formData.value, options)
  } else {
    router.post(route('admin.payment-methods.store'), formData.value, options)
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
