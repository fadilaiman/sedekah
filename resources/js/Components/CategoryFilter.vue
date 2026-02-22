<template>
  <div class="flex flex-wrap justify-center gap-2.5">
    <button
      @click="emit('update:modelValue', '')"
      :class="[
        'glass-panel px-5 py-2 rounded-full flex items-center gap-2 transition-all transform hover:scale-105 group text-sm font-semibold',
        modelValue === ''
          ? 'ring-2 ring-primary bg-primary/10 dark:bg-primary/20 text-primary dark:text-green-300'
          : 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10'
      ]"
    >
      <span class="material-icons-round text-base group-hover:scale-110 transition-transform text-primary">apps</span>
      Semua
      <span v-if="counts['all'] || counts.all" class="text-[10px] px-1.5 py-0.5 rounded-full font-bold bg-primary/20 text-primary">{{ counts.all }}</span>
    </button>
    <button
      v-for="cat in activeCategories"
      :key="cat.value"
      @click="emit('update:modelValue', cat.value === modelValue ? '' : cat.value)"
      :class="[
        'glass-panel px-5 py-2 rounded-full flex items-center gap-2 transition-all transform hover:scale-105 group text-sm font-semibold',
        modelValue === cat.value
          ? 'ring-2 ring-primary bg-primary/10 dark:bg-primary/20 text-primary dark:text-green-300'
          : 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10'
      ]"
    >
      <span :class="['material-icons-round text-base group-hover:scale-110 transition-transform', getCategoryIconClass(cat.value)]">{{ cat.icon }}</span>
      {{ cat.label }}
      <span v-if="counts[cat.value]" :class="['text-[10px] px-1.5 py-0.5 rounded-full font-bold', cat.badge || getCategoryBadge(cat.value)]">{{ counts[cat.value] }}</span>
    </button>
  </div>
</template>

<script setup>
import { useCategories } from '@/Composables/useCategories'

defineProps({
  modelValue: { type: String, default: '' },
  counts: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

const { activeCategories, getCategoryIconClass, getCategoryBadge } = useCategories()
</script>
