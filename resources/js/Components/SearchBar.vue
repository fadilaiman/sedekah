<template>
  <div class="w-full max-w-4xl glass-panel rounded-2xl md:rounded-full flex flex-col md:flex-row gap-2 p-2 shadow-xl ring-1 ring-white/50 dark:ring-white/10">
    <!-- State Selector -->
    <div class="relative min-w-[160px]">
      <select
        v-model="selectedState"
        @change="emit('update:state', selectedState)"
        class="w-full appearance-none bg-transparent border-none text-gray-700 dark:text-gray-200 py-2.5 pl-5 pr-8 rounded-full focus:ring-0 cursor-pointer font-medium text-sm hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors"
      >
        <option value="">Semua Negeri</option>
        <option v-for="state in STATES" :key="state" :value="state">{{ state }}</option>
      </select>
      <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
        <span class="material-icons-round text-gray-400 text-lg">expand_more</span>
      </div>
      <div class="absolute inset-y-2 right-0 w-px bg-gray-200 dark:bg-gray-700 hidden md:block"></div>
    </div>

    <!-- Mobile divider -->
    <div class="h-px bg-gray-200 dark:bg-gray-700 mx-3 md:hidden"></div>

    <!-- Search Input -->
    <div class="relative flex-grow group">
      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <span class="material-icons-round text-gray-400 group-focus-within:text-primary transition-colors text-lg">search</span>
      </div>
      <input
        v-model="query"
        @input="onInput"
        @keyup.enter="emit('search', query)"
        type="text"
        placeholder="Cari masjid, surau, atau institusi..."
        class="w-full bg-transparent border-none text-gray-900 dark:text-white py-2.5 pl-11 pr-4 rounded-full focus:ring-0 placeholder-gray-400 text-sm"
        aria-label="Cari institusi"
      />
      <button
        v-if="query"
        @click="clearSearch"
        class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
      >
        <span class="material-icons-round text-base">close</span>
      </button>
    </div>

    <!-- Search Button -->
    <button
      @click="emit('search', query)"
      class="bg-primary hover:bg-primary-light text-white px-7 py-2.5 rounded-full font-semibold shadow-lg shadow-primary/20 transition-all text-sm whitespace-nowrap"
    >
      Cari
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  state: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'update:state', 'search'])

const query = ref(props.modelValue)
const selectedState = ref(props.state)

let debounceTimer = null

function onInput() {
  emit('update:modelValue', query.value)
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', query.value)
  }, 400)
}

function clearSearch() {
  query.value = ''
  emit('update:modelValue', '')
  emit('search', '')
}

watch(() => props.modelValue, (v) => { query.value = v })
watch(() => props.state, (v) => { selectedState.value = v })

const STATES = [
  'W.P. Kuala Lumpur', 'W.P. Putrajaya', 'W.P. Labuan',
  'Selangor', 'Johor', 'Kedah', 'Kelantan', 'Melaka',
  'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis',
  'Pulau Pinang', 'Sabah', 'Sarawak', 'Terengganu',
]
</script>
