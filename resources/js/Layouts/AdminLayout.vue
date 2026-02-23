<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex">

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
          <span class="material-icons-round text-white text-sm">mosque</span>
        </div>
        <div>
          <p class="font-bold text-gray-900 dark:text-white text-sm leading-none">Sedekah.info</p>
          <p class="text-xs text-gray-400 mt-0.5">Admin Panel</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <Link
          v-for="item in navItems"
          :key="item.route"
          :href="route(item.route)"
          :class="[
            'flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors',
            isActive(item.route)
              ? 'bg-primary/10 text-primary dark:text-green-300'
              : 'text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/5'
          ]"
        >
          <span class="material-icons-round text-base">{{ item.icon }}</span>
          <span class="flex-1">{{ item.label }}</span>
          <span v-if="item.badge && item.badge > 0" class="bg-primary text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center">
            {{ item.badge }}
          </span>
        </Link>

        <div class="border-t border-gray-100 dark:border-gray-800 my-2 pt-2">
          <Link
            :href="route('home')"
            target="_blank"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-gray-500 dark:text-gray-400 hover:text-primary hover:bg-primary/5 transition-colors"
          >
            <span class="material-icons-round text-base">open_in_new</span>
            Lihat Laman Awam
          </Link>
        </div>
      </nav>

      <!-- User / Logout -->
      <div class="px-3 py-3 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-3 px-2 py-2 mb-1">
          <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
            <span class="material-icons-round text-primary text-sm">person</span>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $page.props.auth?.user?.name || 'Admin' }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $page.props.auth?.user?.email }}</p>
          </div>
        </div>
        <form @submit.prevent="logout">
          <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
            <span class="material-icons-round text-base">logout</span>
            Log Keluar
          </button>
        </form>
      </div>
    </aside>

    <!-- Backdrop (mobile) -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
    ></div>

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Top Bar -->
      <header class="sticky top-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-4 px-5 py-3 h-14">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-colors"
          >
            <span class="material-icons-round">menu</span>
          </button>
          <div class="flex-1">
            <h1 class="text-base font-bold text-gray-900 dark:text-white">{{ title }}</h1>
          </div>
          <Transition enter-from-class="opacity-0 translate-y-1" enter-active-class="transition duration-200" leave-active-class="transition duration-150" leave-to-class="opacity-0">
            <div v-if="flash.success" class="flex items-center gap-1.5 text-xs text-green-700 bg-green-50 px-3 py-1.5 rounded-full">
              <span class="material-icons-round text-sm">check_circle</span>{{ flash.success }}
            </div>
          </Transition>
          <Transition enter-from-class="opacity-0 translate-y-1" enter-active-class="transition duration-200" leave-active-class="transition duration-150" leave-to-class="opacity-0">
            <div v-if="flash.error" class="flex items-center gap-1.5 text-xs text-red-700 bg-red-50 px-3 py-1.5 rounded-full">
              <span class="material-icons-round text-sm">error</span>{{ flash.error }}
            </div>
          </Transition>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-5 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

defineProps({
  title: { type: String, default: 'Dashboard' },
})

const page = usePage()
const sidebarOpen = ref(false)
const flash = computed(() => page.props.flash || {})
const pendingCount = computed(() => page.props.pendingSubmissions || 0)

const navItems = computed(() => [
  { route: 'admin.dashboard', icon: 'dashboard', label: 'Dashboard' },
  { route: 'admin.institutions.index', icon: 'account_balance', label: 'Institusi' },
  { route: 'admin.submissions.index', icon: 'inbox', label: 'Penyerahan', badge: pendingCount.value },
  { route: 'admin.featured.edit', icon: 'star', label: 'Institusi Pilihan' },
  { route: 'admin.categories.index', icon: 'sell', label: 'Kategori' },
  { route: 'admin.payment-methods.index', icon: 'payment', label: 'Kaedah Bayaran' },
])

function isActive(routeName) {
  try {
    const target = route(routeName)
    const current = window.location.pathname
    const targetPath = new URL(target, window.location.origin).pathname
    return current.startsWith(targetPath)
  } catch {
    return false
  }
}

function logout() {
  router.post(route('admin.logout'))
}
</script>
