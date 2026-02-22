import { ref, reactive, computed } from 'vue'
import axios from 'axios'

export function useInstitutions(initialData = null) {
  const buffer = ref(initialData?.data || [])   // all fetched items accumulated
  const visibleCount = ref(6)                    // how many to display
  const total = ref(initialData?.total || 0)     // total records from API
  const currentPage = ref(1)
  const lastPage = ref(initialData?.last_page || 1)
  const loading = ref(false)
  const loadingMore = ref(false)
  const error = ref(null)

  const filters = reactive({
    search: '',
    category: '',
    state: '',
    city: '',
  })

  // Only show up to visibleCount items from the buffer
  const institutions = computed(() => buffer.value.slice(0, visibleCount.value))
  const hasMore = computed(() => visibleCount.value < total.value)
  const isEmpty = computed(() => !loading.value && buffer.value.length === 0)

  async function fetchPage(page) {
    const query = { ...filters, page, per_page: 9 }
    Object.keys(query).forEach(k => !query[k] && query[k] !== 0 && delete query[k])
    const response = await axios.get('/api/v1/institutions', { params: query })
    return response.data
  }

  // Full reset + fetch (used when filters change)
  async function fetch(params = {}) {
    loading.value = true
    error.value = null
    buffer.value = []
    visibleCount.value = 6
    currentPage.value = 1

    try {
      if (Object.keys(params).length) Object.assign(filters, params)
      const data = await fetchPage(1)
      buffer.value = data.data || data
      total.value = data.total ?? buffer.value.length
      lastPage.value = data.last_page || 1
    } catch (e) {
      error.value = 'Gagal memuatkan institusi. Sila cuba semula.'
      console.error('useInstitutions fetch error:', e)
    } finally {
      loading.value = false
    }
  }

  // Progressive load: show 9 more, fetching next page if needed
  async function loadMore() {
    if (!hasMore.value || loadingMore.value) return

    const nextVisible = visibleCount.value + 9

    // Need more data from API?
    if (nextVisible > buffer.value.length && currentPage.value < lastPage.value) {
      loadingMore.value = true
      try {
        const data = await fetchPage(currentPage.value + 1)
        buffer.value = [...buffer.value, ...(data.data || [])]
        currentPage.value++
        lastPage.value = data.last_page || lastPage.value
      } catch (e) {
        error.value = 'Gagal memuatkan lebih institusi. Sila cuba semula.'
        console.error('loadMore error:', e)
        loadingMore.value = false
        return
      } finally {
        loadingMore.value = false
      }
    }

    visibleCount.value = Math.min(nextVisible, total.value)
  }

  function search(term) { filters.search = term; fetch() }
  function filterByCategory(cat) { filters.category = cat; fetch() }
  function filterByState(state) { filters.state = state; fetch() }
  function setFilter(key, value) { filters[key] = value }

  return {
    institutions,
    total,
    filters,
    loading,
    loadingMore,
    error,
    isEmpty,
    hasMore,
    fetch,
    loadMore,
    search,
    filterByCategory,
    filterByState,
    setFilter,
  }
}
