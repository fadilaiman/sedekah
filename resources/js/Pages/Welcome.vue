<template>
  <PublicLayout>
    <Head title="Sedekah.online — QR Sedekah Malaysia" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- ── Hero Section ───────────────────────────────────── -->
      <section class="hero-pattern rounded-3xl p-8 md:p-14 text-white relative overflow-hidden shadow-2xl shadow-primary/20 mb-12 ring-1 ring-white/10 group">
        <!-- Background blobs -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-secondary/30 rounded-full blur-3xl pointer-events-none group-hover:bg-secondary/40 transition-colors duration-700"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
          <!-- Hero Text -->
          <div class="max-w-xl">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 px-4 py-1.5 rounded-full text-xs font-semibold mb-6 shadow-sm">
              <span class="material-icons-round text-secondary text-sm">mosque</span>
              <span class="text-secondary-light">{{ stats.total?.toLocaleString() }} institusi berdaftar</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight tracking-tight">
              Sedekah di hujung <br />jari anda.
            </h1>
            <p class="text-gray-100 text-lg mb-8 leading-relaxed font-light opacity-90">
              Platform infaq QR berpusat yang mudah, telus dan selamat. Cari masjid, surau, dan institusi agama seluruh Malaysia.
            </p>
            <div class="flex flex-wrap gap-4">
              <Link :href="route('institutions.index')" class="btn-secondary flex items-center gap-2">
                <span class="material-icons-round text-lg">qr_code_scanner</span>
                Lihat Semua QR
              </Link>
              <Link :href="route('submit')" class="btn-ghost">
                Hantar Institusi
              </Link>
            </div>

            <!-- Mobile QR Preview — compact horizontal card -->
            <div
              v-if="(featuredWithQr.length > 0 || loading) && heroInstitution"
              class="lg:hidden mt-6 cursor-pointer"
              @click="onHeroCardClick"
            >
              <div class="flex items-center gap-4 bg-white/10 backdrop-blur-xl border border-white/20 p-3 rounded-2xl">
                <div class="bg-white p-2 rounded-xl shrink-0">
                  <img
                    v-if="heroQrSrc"
                    :src="heroQrSrc"
                    :alt="heroInstitution.name"
                    class="w-16 h-16 object-contain mix-blend-multiply"
                  />
                  <div v-else class="w-16 h-16 flex items-center justify-center text-gray-300">
                    <span class="material-icons-round text-3xl">qr_code_2</span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-white/90 truncate">{{ heroInstitution.name }}</p>
                  <p class="text-xs text-white/60">{{ heroInstitution.city }}, {{ heroInstitution.state }}</p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                  <button
                    @click.stop="heroPrev"
                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/70"
                  >
                    <span class="material-icons-round text-xs">undo</span>
                  </button>
                  <span class="text-[10px] text-white/40 tabular-nums">{{ heroIndex + 1 }}/{{ featuredWithQr.length }}</span>
                  <button
                    @click.stop="heroRandomize"
                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/70"
                  >
                    <span class="material-icons-round text-xs">shuffle</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating QR Preview — Tinder-style swipeable (desktop) -->
          <div class="hidden lg:block relative" v-if="featuredWithQr.length > 0 || loading">
            <div
              ref="heroCardEl"
              class="relative cursor-pointer select-none"
              :style="heroCardStyle"
              @mousedown="onHeroDragStart"
              @touchstart="onHeroDragStart"
              @click="onHeroCardClick"
            >
              <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-5 rounded-3xl shadow-2xl transition-shadow duration-300 hover:shadow-[0_20px_60px_rgba(0,0,0,0.3)]">
                <div class="bg-white p-3 rounded-2xl mb-3">
                  <Transition name="hero-qr" mode="out-in">
                    <img
                      v-if="heroQrSrc"
                      :key="heroIndex"
                      :src="heroQrSrc"
                      :alt="heroInstitution.name"
                      class="w-44 h-44 object-contain mix-blend-multiply"
                    />
                    <div v-else :key="'empty-' + heroIndex" class="w-44 h-44 flex items-center justify-center text-gray-300">
                      <span class="material-icons-round text-7xl">qr_code_2</span>
                    </div>
                  </Transition>
                </div>
                <div class="text-center">
                  <p class="text-sm font-semibold text-white/90 line-clamp-1">{{ heroInstitution.name }}</p>
                  <p class="text-xs text-white/60">{{ heroInstitution.city }}, {{ heroInstitution.state }}</p>
                </div>
              </div>

              <!-- Swipe direction hints -->
              <Transition name="fade">
                <div v-if="heroDragX > 30" class="absolute inset-0 rounded-3xl border-2 border-green-400/60 flex items-center justify-center pointer-events-none">
                  <span class="material-icons-round text-green-400 text-4xl drop-shadow-lg">undo</span>
                </div>
              </Transition>
              <Transition name="fade">
                <div v-if="heroDragX < -30" class="absolute inset-0 rounded-3xl border-2 border-secondary/60 flex items-center justify-center pointer-events-none">
                  <span class="material-icons-round text-secondary text-4xl drop-shadow-lg">shuffle</span>
                </div>
              </Transition>
            </div>

            <!-- Controls below card -->
            <div class="flex items-center justify-center gap-3 mt-3">
              <button
                @click="heroPrev"
                class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 flex items-center justify-center text-white/70 hover:text-white transition-all"
                title="Sebelumnya"
              >
                <span class="material-icons-round text-sm">undo</span>
              </button>
              <span class="text-[10px] text-white/40 font-medium tabular-nums">{{ heroIndex + 1 }}/{{ featuredWithQr.length }}</span>
              <button
                @click="heroRandomize"
                class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 flex items-center justify-center text-white/70 hover:text-white transition-all"
                title="Rawak"
              >
                <span class="material-icons-round text-sm">shuffle</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Filters & Search ───────────────────────────────── -->
      <section class="flex flex-col items-center gap-5 mb-10">
        <!-- Category Filter Pills -->
        <CategoryFilter v-model="filters.category" @update:modelValue="onCategoryChange" />

        <!-- Search Bar -->
        <SearchBar
          v-model="filters.search"
          v-model:state="filters.state"
          @search="onSearch"
          @update:state="onStateChange"
        />
      </section>

      <!-- ── Featured Institutions ─────────────────────────── -->
      <section v-if="featured.length > 0" class="mb-12">
        <div class="flex items-center gap-2 mb-6 px-1">
          <span class="material-icons-round text-secondary text-xl">star</span>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Institusi Pilihan</h2>
          <span class="text-xs text-gray-500 dark:text-gray-400 bg-secondary/10 text-secondary px-2 py-1 rounded-full font-medium">{{ featured.length }}/8</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <InstitutionCard
            v-for="institution in featured"
            :key="institution.id"
            :institution="institution"
            :featured="true"
            @share="openShare"
          />
        </div>
      </section>

      <!-- ── Institution Grid ────────────────────────────────── -->
      <section>
        <!-- Section Header -->
        <div class="flex justify-between items-end mb-6 px-1">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Senarai Institusi</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
              <template v-if="loading">Memuatkan...</template>
              <template v-else>{{ total.toLocaleString() }} institusi dijumpai</template>
            </p>
          </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
          <div v-for="n in 6" :key="n" class="glass-panel rounded-3xl p-5 animate-pulse">
            <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-2xl mb-3 mx-auto"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-full mb-2 w-3/4 mx-auto"></div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full mb-4 w-1/2 mx-auto"></div>
            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-2xl"></div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="isEmpty" class="text-center py-20">
          <span class="material-icons-round text-6xl text-gray-300 dark:text-gray-600 mb-4 block">search_off</span>
          <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Tiada institusi dijumpai</h3>
          <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Cuba cari dengan kata kunci yang lain</p>
          <button @click="resetFilters" class="btn-primary mt-4 text-sm">
            Paparkan Semua
          </button>
        </div>

        <!-- Institution Cards -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
          <InstitutionCard
            v-for="institution in institutions"
            :key="institution.id"
            :institution="institution"
            @share="openShare"
          />
        </div>

        <!-- Show More -->
        <div v-if="!loading && hasMore" class="flex flex-col items-center gap-2 mb-12">
          <button
            @click="loadMore"
            :disabled="loadingMore"
            class="btn-primary flex items-center gap-2 px-8 py-3 disabled:opacity-60"
          >
            <svg v-if="loadingMore" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span class="material-icons-round text-lg" v-else>expand_more</span>
            {{ loadingMore ? 'Memuatkan...' : 'Papar Lebih' }}
          </button>
          <p class="text-xs text-gray-400">Menunjukkan {{ institutions.length }} daripada {{ total }} institusi</p>
        </div>
        <div v-else-if="!loading && !isEmpty" class="mb-12"></div>
      </section>
    </div>

    <!-- Hero QR Modal — Tinder-style random browser -->
    <HeroQrModal
      v-model="heroModalOpen"
      :initial-institution="heroInstitution"
      @share="openShare"
    />

    <!-- Share Modal -->
    <ShareModal
      v-model="shareModalOpen"
      :institution="sharingInstitution"
      :qr-image-url="sharingQrUrl"
    />
  </PublicLayout>
</template>

<style scoped>
.hero-qr-enter-active,
.hero-qr-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.hero-qr-enter-from {
  opacity: 0;
  transform: scale(0.92);
}
.hero-qr-leave-to {
  opacity: 0;
  transform: scale(0.92);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import InstitutionCard from '@/Components/InstitutionCard.vue'
import HeroQrModal from '@/Components/HeroQrModal.vue'
import CategoryFilter from '@/Components/CategoryFilter.vue'
import SearchBar from '@/Components/SearchBar.vue'
import ShareModal from '@/Components/ShareModal.vue'
import { useInstitutions } from '@/Composables/useInstitutions.js'

const props = defineProps({
  featured: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
})

const {
  institutions, total, filters, loading, loadingMore, isEmpty, hasMore,
  fetch, loadMore,
} = useInstitutions()

// ── Hero Tinder-style card ──────────────────────────────
const heroCardEl = ref(null)
const heroIndex = ref(0)
const heroHistory = ref([])       // stack of previous indices
const heroModalOpen = ref(false)

// Featured institutions that have QR codes, or fallback to random institutions with QR codes
const featuredWithQr = computed(() => {
  // First try featured institutions with QR codes
  const featured = props.featured.filter(f => {
    const qrs = f.active_qr_codes || f.qr_codes || []
    return qrs.length > 0
  })

  if (featured.length > 0) {
    return featured
  }

  // Fallback: return institutions from the loaded list with QR codes
  return institutions.value.filter(inst => {
    const qrs = inst.active_qr_codes || inst.qr_codes || []
    return qrs.length > 0
  }).slice(0, 8)
})

const heroInstitution = computed(() => featuredWithQr.value[heroIndex.value] || null)
const heroQrSrc = computed(() => {
  if (!heroInstitution.value) return null
  const qrs = heroInstitution.value.active_qr_codes || heroInstitution.value.qr_codes || []
  return qrs[0]?.qr_image_url || null
})

// Drag state
const heroDragX = ref(0)
const heroDragging = ref(false)
let dragStartX = 0
let dragHasMoved = false

const heroCardStyle = computed(() => {
  if (!heroDragging.value && heroDragX.value === 0) {
    return { transform: 'rotate(3deg)', transition: 'transform 0.4s cubic-bezier(0.34,1.56,0.64,1)' }
  }
  const rotate = heroDragX.value * 0.08
  const translateX = heroDragX.value
  return {
    transform: `translateX(${translateX}px) rotate(${rotate}deg)`,
    transition: heroDragging.value ? 'none' : 'transform 0.4s cubic-bezier(0.34,1.56,0.64,1)',
  }
})

function onHeroDragStart(e) {
  if (featuredWithQr.value.length <= 1) return
  heroDragging.value = true
  dragHasMoved = false
  const clientX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX
  dragStartX = clientX
  window.addEventListener('mousemove', onHeroDragMove)
  window.addEventListener('mouseup', onHeroDragEnd)
  window.addEventListener('touchmove', onHeroDragMove, { passive: false })
  window.addEventListener('touchend', onHeroDragEnd)
}

function onHeroDragMove(e) {
  const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX
  heroDragX.value = clientX - dragStartX
  if (Math.abs(heroDragX.value) > 5) dragHasMoved = true
}

function onHeroDragEnd() {
  heroDragging.value = false
  window.removeEventListener('mousemove', onHeroDragMove)
  window.removeEventListener('mouseup', onHeroDragEnd)
  window.removeEventListener('touchmove', onHeroDragMove)
  window.removeEventListener('touchend', onHeroDragEnd)

  // Swipe left (drag negative) → randomize
  if (heroDragX.value < -60) {
    heroRandomize()
  }
  // Swipe right (drag positive) → go back
  else if (heroDragX.value > 60) {
    heroPrev()
  }

  heroDragX.value = 0
}

function onHeroCardClick() {
  if (dragHasMoved) return        // don't open modal if user was swiping
  if (!heroInstitution.value) return
  heroModalOpen.value = true
}

function heroRandomize() {
  if (featuredWithQr.value.length <= 1) return
  heroHistory.value.push(heroIndex.value)
  let next
  do {
    next = Math.floor(Math.random() * featuredWithQr.value.length)
  } while (next === heroIndex.value)
  heroIndex.value = next
}

function heroPrev() {
  if (heroHistory.value.length > 0) {
    heroIndex.value = heroHistory.value.pop()
  } else {
    // wrap around
    heroIndex.value = heroIndex.value === 0
      ? featuredWithQr.value.length - 1
      : heroIndex.value - 1
  }
}

// Share modal
const shareModalOpen = ref(false)
const sharingInstitution = ref(null)
const sharingQrUrl = ref(null)

function openShare(institution) {
  sharingInstitution.value = institution
  const qrs = institution.active_qr_codes || institution.qr_codes || []
  sharingQrUrl.value = qrs[0]?.qr_image_url || null
  shareModalOpen.value = true
}

// Filter handlers
function onCategoryChange(cat) { filters.category = cat; fetch() }
function onSearch(term) { filters.search = term; fetch() }
function onStateChange(state) { filters.state = state; fetch() }
function resetFilters() { filters.search = ''; filters.category = ''; filters.state = ''; fetch() }

onMounted(() => fetch())

onUnmounted(() => {
  window.removeEventListener('mousemove', onHeroDragMove)
  window.removeEventListener('mouseup', onHeroDragEnd)
  window.removeEventListener('touchmove', onHeroDragMove)
  window.removeEventListener('touchend', onHeroDragEnd)
})
</script>
