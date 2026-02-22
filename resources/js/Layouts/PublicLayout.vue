<template>
  <div class="min-h-screen flex flex-col bg-background-light dark:bg-background-dark bg-islamic-geo transition-colors duration-300">

    <!-- Floating Glass Navbar -->
    <div class="fixed top-0 left-0 right-0 z-50 flex justify-center pt-5 px-4 pointer-events-none">
      <nav
        ref="navEl"
        class="glass-pill rounded-full px-5 py-2.5 w-full max-w-5xl flex items-center justify-between pointer-events-auto shadow-xl ring-1 ring-white/20 dark:ring-white/5 transition-all duration-300"
      >
        <!-- Logo -->
        <Link :href="route('home')" class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center shadow-lg shadow-primary/30">
            <span class="material-icons-round text-base">mosque</span>
          </div>
          <span class="font-bold text-base tracking-tight text-primary dark:text-white">
            Sedekah<span class="text-secondary">.online</span>
          </span>
        </Link>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center bg-gray-100/50 dark:bg-white/5 rounded-full px-1 p-1 border border-white/50 dark:border-white/10">
          <Link
            :href="route('home')"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
            :class="isRoute('home') ? 'bg-white dark:bg-primary shadow-sm text-primary dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-white'"
          >Utama</Link>
          <Link
            :href="route('institutions.index')"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
            :class="isRoute('institutions.*') ? 'bg-white dark:bg-primary shadow-sm text-primary dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-white'"
          >Senarai</Link>
          <Link
            :href="route('submit')"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
            :class="isRoute('submit*') ? 'bg-white dark:bg-primary shadow-sm text-primary dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-white'"
          >Hantar</Link>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center gap-2">
          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDark"
            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-secondary/20 hover:text-secondary transition-colors"
            aria-label="Toggle dark mode"
          >
            <span class="material-icons-round text-base">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
          </button>

          <!-- Mobile Menu Toggle -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden w-8 h-8 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300"
            aria-label="Menu"
          >
            <span class="material-icons-round text-xl">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
          </button>
        </div>
      </nav>

      <!-- Mobile Menu Dropdown -->
      <Transition name="slide-down">
        <div
          v-if="mobileMenuOpen"
          class="absolute top-[70px] left-4 right-4 glass-pill rounded-2xl p-4 pointer-events-auto shadow-xl"
        >
          <div class="flex flex-col gap-1">
            <Link :href="route('home')" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-primary/10 hover:text-primary transition-colors">Utama</Link>
            <Link :href="route('institutions.index')" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-primary/10 hover:text-primary transition-colors">Senarai Institusi</Link>
            <Link :href="route('submit')" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-primary/10 hover:text-primary transition-colors">Hantar Institusi</Link>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Page Content -->
    <main class="flex-grow pt-24">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-surface-light dark:bg-surface-dark border-t border-gray-100 dark:border-gray-800 pt-12 pb-6 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
          <!-- Brand -->
          <div class="md:col-span-1">
            <div class="flex items-center gap-2.5 mb-4">
              <div class="w-9 h-9 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <span class="material-icons-round text-xl">mosque</span>
              </div>
              <span class="font-bold text-lg text-gray-900 dark:text-white">Sedekah.online</span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
              Platform infaq QR berpusat untuk memudahkan urusan sedekah ke masjid, surau, dan institusi agama di Malaysia.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Pautan</h4>
            <ul class="space-y-2.5 text-sm text-gray-500 dark:text-gray-400">
              <li><Link :href="route('home')" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-secondary inline-block"></span>Utama</Link></li>
              <li><Link :href="route('institutions.index')" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-300 inline-block"></span>Senarai Institusi</Link></li>
              <li><Link :href="route('submit')" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-300 inline-block"></span>Hantar Institusi</Link></li>
            </ul>
          </div>

          <!-- Categories -->
          <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Kategori</h4>
            <ul class="space-y-2.5 text-sm text-gray-500 dark:text-gray-400">
              <li v-for="cat in activeCategories" :key="cat.value">
                <Link :href="route('institutions.index') + '?category=' + cat.value" class="hover:text-primary transition-colors">{{ cat.label }}</Link>
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Sokongan</h4>
            <ul class="space-y-2.5 text-sm text-gray-500 dark:text-gray-400">
              <li><a href="#" class="hover:text-primary transition-colors">Tentang Kami</a></li>
              <li><a href="#" class="hover:text-primary transition-colors">Hubungi Kami</a></li>
              <li><a href="#" class="hover:text-primary transition-colors">Dasar Privasi</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-800 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
          <p>© {{ new Date().getFullYear() }} Sedekah.online. Hak Cipta Terpelihara.</p>
          <div class="flex items-center gap-1">
            Made with <span class="material-icons-round text-red-400 text-sm mx-0.5" style="font-size:14px">favorite</span> for Ummah
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useCategories } from '@/Composables/useCategories'

const mobileMenuOpen = ref(false)
const navEl = ref(null)
const isDark = ref(false)

const { activeCategories } = useCategories()

function isRoute(pattern) {
  const page = usePage()
  const currentRoute = page.url
  // Simple pattern check
  return false // Will be enhanced if needed
}

function toggleDark() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

function handleScroll() {
  if (!navEl.value) return
  const container = navEl.value.parentElement
  if (window.scrollY > 50) {
    container.classList.remove('pt-5')
    container.classList.add('pt-1.5')
    navEl.value.classList.add('py-1.5', 'px-3', 'max-w-3xl', 'bg-white/70', 'dark:bg-black/60', 'backdrop-blur-2xl', 'border', 'border-white/30', 'dark:border-white/5', 'shadow-lg')
    navEl.value.classList.remove('glass-pill', 'px-5', 'py-2.5', 'max-w-5xl')
  } else {
    container.classList.add('pt-5')
    container.classList.remove('pt-1.5')
    navEl.value.classList.remove('py-1.5', 'px-3', 'max-w-3xl', 'bg-white/70', 'dark:bg-black/60', 'backdrop-blur-2xl', 'border', 'border-white/30', 'dark:border-white/5', 'shadow-lg')
    navEl.value.classList.add('glass-pill', 'px-5', 'py-2.5', 'max-w-5xl')
  }
}

onMounted(() => {
  // Restore dark mode preference
  const saved = localStorage.getItem('theme')
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.2s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
