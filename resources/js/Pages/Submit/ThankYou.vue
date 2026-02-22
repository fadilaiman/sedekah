<template>
  <PublicLayout>
    <Head title="Terima Kasih — Sedekah.online" />

    <div class="max-w-md mx-auto px-4 sm:px-6 py-16 text-center">
      <div class="glass-panel rounded-3xl p-10">
        <!-- Success Icon -->
        <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
          <span class="material-icons-round text-4xl text-primary">check_circle</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
          Terima Kasih!
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mb-2">
          Penyerahan anda telah diterima dengan jayanya.
        </p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mb-8">
          Admin kami akan menyemak maklumat yang diberikan sebelum memaparkannya dalam senarai. Proses ini biasanya mengambil masa 1-3 hari bekerja.
        </p>

        <div class="flex flex-col gap-3">
          <Link :href="route('home')" class="btn-primary justify-center flex items-center gap-2">
            <span class="material-icons-round text-base">home</span>
            Kembali ke Utama
          </Link>
          <Link :href="route('submit')" class="btn-ghost bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 justify-center flex items-center gap-2">
            <span class="material-icons-round text-base">add_circle_outline</span>
            Hantar Lagi
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>
