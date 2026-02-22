<template>
  <AdminLayout title="Tambah Institusi">
    <Head title="Tambah Institusi — Admin" />

    <div class="max-w-2xl">
      <div class="mb-6">
        <Link :href="route('admin.institutions.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition-colors">
          <span class="material-icons-round text-base">arrow_back</span>
          Kembali ke senarai
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
        <InstitutionForm :form="form" :payment-methods="paymentMethods" :errors="form.errors" @submit="submit" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import InstitutionForm from '@/Components/Admin/InstitutionForm.vue'

const props = defineProps({
  paymentMethods: { type: Array, default: () => [] },
})

const form = useForm({
  name: '', category: '', state: '', city: '',
  address: '', description: '', website_url: '', contact_email: '',
  contact_phone: '', external_campaign_url: '', url: '', maps_url: '', is_verified: false,
})

function submit() {
  form.post(route('admin.institutions.store'))
}
</script>
