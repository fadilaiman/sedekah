<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full space-y-8 px-4">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">Confirm Login</h2>
        <p class="mt-2 text-sm text-gray-600">
          Click the button below to complete your login.
        </p>
      </div>

      <form class="mt-8" @submit.prevent="submit">
        <input type="hidden" name="token" :value="token" />

        <p v-if="form.errors.token" class="mb-4 text-sm text-red-600 text-center">
          {{ form.errors.token }}
        </p>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Logging in...</span>
          <span v-else>Log In to Admin Panel</span>
        </button>
      </form>

      <p class="text-center text-sm text-gray-500">
        <a :href="route('admin.login')" class="text-green-600 hover:underline">
          Request a new link
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  token: String,
})

const form = useForm({
  token: props.token,
})

function submit() {
  form.post(route('admin.login.confirm'))
}
</script>
