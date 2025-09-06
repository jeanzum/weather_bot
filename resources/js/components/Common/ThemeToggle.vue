<template>
  <button
    @click="toggle"
    :class="buttonClasses"
    :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  >
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="scale-0 rotate-90 opacity-0"
      enter-to-class="scale-100 rotate-0 opacity-100"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="scale-100 rotate-0 opacity-100"
      leave-to-class="scale-0 -rotate-90 opacity-0"
      mode="out-in"
    >
      <!-- Sun Icon for dark mode (shows when dark mode is active) -->
      <svg v-if="isDark" :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      
      <!-- Moon Icon for light mode (shows when light mode is active) -->
      <svg v-else :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
      </svg>
    </transition>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { useDarkMode } from '../../composables/useDarkMode.js'

const props = defineProps({
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  variant: {
    type: String,
    default: 'floating',
    validator: (value) => ['floating', 'inline', 'ghost'].includes(value)
  }
})

const { isDark, toggleDarkMode } = useDarkMode()

const toggle = () => {
  toggleDarkMode()
}

const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex items-center justify-center rounded-full transition-all duration-300',
    'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400',
    'focus:ring-offset-white dark:focus:ring-offset-gray-900'
  ]

  const sizeClasses = {
    small: 'w-8 h-8 p-1.5',
    medium: 'w-10 h-10 p-2',
    large: 'w-12 h-12 p-2.5'
  }

  const variantClasses = {
    floating: [
      'fixed top-4 right-4 z-50 shadow-lg backdrop-blur-md',
      'bg-white/90 dark:bg-gray-800/90 border border-gray-200/50 dark:border-gray-700/50',
      'hover:bg-white dark:hover:bg-gray-800 hover:shadow-xl transform hover:scale-110'
    ],
    inline: [
      'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
      'hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-blue-500 dark:hover:text-blue-400'
    ],
    ghost: [
      'text-gray-600 dark:text-gray-300',
      'hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-500 dark:hover:text-blue-400'
    ]
  }

  return [
    ...baseClasses,
    sizeClasses[props.size],
    ...variantClasses[props.variant]
  ].join(' ')
})

const iconClasses = computed(() => {
  const sizeClasses = {
    small: 'w-4 h-4',
    medium: 'w-5 h-5',
    large: 'w-6 h-6'
  }

  return [
    'text-gray-600 dark:text-gray-300 transition-colors duration-300',
    sizeClasses[props.size]
  ].join(' ')
})
</script>
