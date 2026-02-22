<template>
  <div class="min-h-screen flex items-center justify-center bg-yellow-50">
    <div class="max-w-md w-full space-y-8 px-4">
      <div class="text-center">
        <div class="mb-4 text-3xl">⚙️</div>
        <h2 class="text-3xl font-bold text-gray-900">Development Login</h2>
        <p class="mt-2 text-sm text-gray-600">
          Quick admin login (development only)
        </p>
      </div>

      <div class="space-y-3">
        <form
          v-for="admin in admins"
          :key="admin.id"
          method="post"
          @submit.prevent="login(admin.id)"
          class="flex flex-col"
        >
          <button
            type="submit"
            class="px-4 py-3 border border-yellow-300 rounded-md shadow-sm text-sm font-medium text-gray-900 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
          >
            <span class="block font-semibold">{{ admin.name }}</span>
            <span class="block text-xs text-gray-500">{{ admin.email }}</span>
            <span class="block text-xs text-gray-400">{{ admin.role }}</span>
          </button>
        </form>
      </div>

      <div class="rounded-md bg-yellow-50 border border-yellow-200 p-4">
        <p class="text-xs text-yellow-800">
          ⚠️ This page is only visible when <code class="font-mono bg-yellow-100 px-1">APP_DEBUG=true</code> in your .env
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  admins: Array,
})

const form = useForm({ user_id: null })

function login(userId) {
  form.user_id = userId
  form.post(route('admin.dev-login.submit'))
}
</script>
