<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full space-y-8 px-4">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">Admin Login</h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your email to receive a secure login link.
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="submit">
        <p v-if="errors.token" class="rounded-md bg-red-50 p-4 text-sm text-red-800">
          {{ errors.token }}
        </p>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
            placeholder="admin@example.com"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
            {{ form.errors.email }}
          </p>
        </div>

        <div v-if="status" class="rounded-md bg-green-50 p-4">
          <p class="text-sm text-green-800">{{ status }}</p>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Sending...</span>
          <span v-else>Send Login Link</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const status = computed(() => page.props.flash?.status)
const errors = computed(() => page.props.errors ?? {})

const form = useForm({
  email: '',
})

function submit() {
  form.post(route('admin.login.send'), {
    onSuccess: () => form.reset(),
  })
}
</script>
