import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const COLOR_MAP = {
  green: {
    icon: 'text-emerald-500',
    bg: 'bg-emerald-50 dark:bg-emerald-900/20',
    badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  },
  blue: {
    icon: 'text-blue-500',
    bg: 'bg-blue-50 dark:bg-blue-900/20',
    badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  },
  amber: {
    icon: 'text-amber-500',
    bg: 'bg-amber-50 dark:bg-amber-900/20',
    badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  },
  pink: {
    icon: 'text-pink-500',
    bg: 'bg-pink-50 dark:bg-pink-900/20',
    badge: 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
  },
  red: {
    icon: 'text-red-500',
    bg: 'bg-red-50 dark:bg-red-900/20',
    badge: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
  },
  purple: {
    icon: 'text-purple-500',
    bg: 'bg-purple-50 dark:bg-purple-900/20',
    badge: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
  },
  orange: {
    icon: 'text-orange-500',
    bg: 'bg-orange-50 dark:bg-orange-900/20',
    badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  },
  gray: {
    icon: 'text-gray-500',
    bg: 'bg-gray-50 dark:bg-gray-800',
    badge: 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-300',
  },
}

export function useCategories() {
  const page = usePage()

  // All active categories from Inertia props
  const allCategories = computed(() => page.props.categories || [])

  // Filter to only active categories
  const activeCategories = computed(() =>
    allCategories.value.filter(cat => cat.active)
  )

  // Get full config object for a category by value
  function getCategoryConfig(value) {
    const category = allCategories.value.find(cat => cat.value === value)
    if (!category) return null

    return {
      ...category,
      ...COLOR_MAP[category.color] || COLOR_MAP.gray,
    }
  }

  // Get label/display name for a category value
  function getCategoryLabel(value) {
    const category = allCategories.value.find(cat => cat.value === value)
    return category?.label || value
  }

  // Get icon class (e.g., 'text-emerald-500') for a category value
  function getCategoryIconClass(value) {
    const color = allCategories.value.find(cat => cat.value === value)?.color || 'gray'
    return COLOR_MAP[color]?.icon || COLOR_MAP.gray.icon
  }

  // Get background class for a category value
  function getCategoryBg(value) {
    const color = allCategories.value.find(cat => cat.value === value)?.color || 'gray'
    return COLOR_MAP[color]?.bg || COLOR_MAP.gray.bg
  }

  // Get badge classes for a category value
  function getCategoryBadge(value) {
    const color = allCategories.value.find(cat => cat.value === value)?.color || 'gray'
    return COLOR_MAP[color]?.badge || COLOR_MAP.gray.badge
  }

  // Get icon name for a category value
  function getCategoryIcon(value) {
    const category = allCategories.value.find(cat => cat.value === value)
    return category?.icon || 'help'
  }

  return {
    allCategories,
    activeCategories,
    getCategoryConfig,
    getCategoryLabel,
    getCategoryIconClass,
    getCategoryBg,
    getCategoryBadge,
    getCategoryIcon,
  }
}
