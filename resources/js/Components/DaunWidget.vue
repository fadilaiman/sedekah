<template>
  <Teleport to="body">
    <!-- Floating icon button -->
    <Transition name="pop">
      <button
        v-if="visible && hasPosts"
        @click="panelOpen = true"
        :class="[
          'fixed bottom-5 right-5 z-30 rounded-full bg-primary text-white shadow-lg shadow-primary/30',
          'flex items-center justify-center transition-all hover:scale-110 hover:shadow-xl',
          'w-11 h-11 md:w-12 md:h-12',
          !panelOpen && shouldShake ? 'daun-shake' : '',
        ]"
        :style="panelOpen ? { opacity: 0, pointerEvents: 'none' } : {}"
        aria-label="Lihat pos terbaru di Daun.me"
      >
        <span class="material-icons-round text-xl md:text-2xl">eco</span>
        <!-- Notification dot -->
        <span
          v-if="!panelOpen && hasPosts"
          class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-secondary rounded-full border-2 border-white dark:border-background-dark"
        ></span>
      </button>
    </Transition>

    <!-- Click-outside backdrop -->
    <div
      v-if="panelOpen"
      class="fixed inset-0 z-30"
      @click="panelOpen = false"
    ></div>

    <!-- Expandable panel -->
    <Transition name="slide-up">
      <div
        v-if="panelOpen"
        class="fixed bottom-5 right-5 z-30 w-80 max-w-[calc(100vw-3rem)] max-h-[400px] flex flex-col rounded-2xl glass-panel overflow-hidden"
      >
        <!-- Header -->
        <a
          href="https://daun.me/sedekahinfo"
          target="_blank"
          rel="noopener"
          class="flex items-center justify-between px-4 py-3 border-b border-gray-200/50 dark:border-white/10 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors group"
        >
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white shadow shadow-primary/20">
              <span class="material-icons-round text-base">eco</span>
            </div>
            <div class="leading-tight">
              <span class="font-bold text-sm text-gray-900 dark:text-white">Sedekah.info</span>
              <span class="block text-[11px] text-gray-400 dark:text-gray-500">di Daun.me</span>
            </div>
          </div>
          <button
            @click.prevent.stop="panelOpen = false"
            class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            aria-label="Tutup"
          >
            <span class="material-icons-round text-lg">close</span>
          </button>
        </a>

        <!-- Post list -->
        <div class="flex-1 overflow-y-auto overscroll-contain">
          <ul class="divide-y divide-gray-100 dark:divide-white/5">
            <li v-for="post in posts" :key="post.id">
              <a
                href="https://daun.me/sedekahinfo"
                target="_blank"
                rel="noopener"
                class="block px-4 py-3 hover:bg-primary/5 dark:hover:bg-white/5 transition-colors"
              >
                <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed line-clamp-3">
                  {{ truncate(post.content, 120) }}
                </p>
                <div class="flex items-center gap-3 mt-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                  <span>{{ relativeTime(post.createdAt) }}</span>
                  <span v-if="post.likes" class="flex items-center gap-0.5">
                    <span class="material-icons-round" style="font-size:12px">favorite</span>
                    {{ post.likes }}
                  </span>
                </div>
              </a>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <a
          href="https://daun.me/sedekahinfo"
          target="_blank"
          rel="noopener"
          class="flex items-center justify-center gap-1.5 px-4 py-2.5 border-t border-gray-200/50 dark:border-white/10 text-primary dark:text-secondary text-xs font-semibold hover:bg-primary/5 dark:hover:bg-white/5 transition-colors"
        >
          Lihat semua di Daun.me
          <span class="material-icons-round" style="font-size:14px">arrow_forward</span>
        </a>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useDaunPosts } from '@/Composables/useDaunPosts'

const { posts, hasPosts, fetchPosts } = useDaunPosts()

const visible = ref(false)
const panelOpen = ref(false)
const shouldShake = ref(false)

let shakeInterval = null
let appearTimeout = null

onMounted(async () => {
  await fetchPosts()

  if (!hasPosts.value) return

  // Check sessionStorage dismissal
  if (sessionStorage.getItem('daun_widget_dismissed') === '1') return

  // Appear after 2.5s delay
  appearTimeout = setTimeout(() => {
    visible.value = true
    startShakeCycle()
  }, 2500)
})

onUnmounted(() => {
  clearTimeout(appearTimeout)
  clearInterval(shakeInterval)
})

function startShakeCycle() {
  // Respect prefers-reduced-motion
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

  shakeInterval = setInterval(() => {
    if (panelOpen.value) return
    shouldShake.value = true
    setTimeout(() => { shouldShake.value = false }, 600)
  }, 18000)
}

function truncate(text, max) {
  if (!text) return ''
  if (text.length <= max) return text
  return text.slice(0, max).trimEnd() + '...'
}

function relativeTime(dateStr) {
  if (!dateStr) return ''
  const now = Date.now()
  const then = new Date(dateStr).getTime()
  const diff = Math.max(0, now - then)
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'baru'
  if (mins < 60) return `${mins} min lalu`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours} jam lalu`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days} hari lalu`
  const weeks = Math.floor(days / 7)
  if (weeks < 5) return `${weeks} minggu lalu`
  const months = Math.floor(days / 30)
  return `${months} bulan lalu`
}
</script>

<style scoped>
/* Pop-in for icon */
.pop-enter-active {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
}
.pop-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.pop-enter-from {
  opacity: 0;
  transform: scale(0.5);
}
.pop-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

/* Slide-up for panel */
.slide-up-enter-active {
  transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
}
.slide-up-leave-active {
  transition: transform 0.2s ease, opacity 0.15s ease;
}
.slide-up-enter-from {
  opacity: 0;
  transform: translateY(12px) scale(0.95);
}
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(8px) scale(0.97);
}

/* Shake animation */
@keyframes daun-shake {
  0%   { transform: rotate(0deg); }
  15%  { transform: rotate(-5deg); }
  30%  { transform: rotate(4deg); }
  45%  { transform: rotate(-3deg); }
  60%  { transform: rotate(2deg); }
  75%  { transform: rotate(-1deg); }
  100% { transform: rotate(0deg); }
}

.daun-shake {
  animation: daun-shake 0.6s ease-in-out;
}

/* 3-line clamp */
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (prefers-reduced-motion: reduce) {
  .daun-shake {
    animation: none;
  }
}
</style>
