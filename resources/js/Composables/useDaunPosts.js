import { ref, computed } from 'vue'
import axios from 'axios'

export function useDaunPosts() {
  const posts = ref([])
  const loading = ref(false)

  const hasPosts = computed(() => posts.value.length > 0)

  async function fetchPosts() {
    loading.value = true
    try {
      const response = await axios.get('/api/v1/daun/posts')
      posts.value = response.data?.data || []
    } catch (e) {
      console.error('useDaunPosts fetch error:', e)
      posts.value = []
    } finally {
      loading.value = false
    }
  }

  return {
    posts,
    loading,
    hasPosts,
    fetchPosts,
  }
}
