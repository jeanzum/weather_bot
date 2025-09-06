import { ref, watch } from 'vue'

const isDark = ref(false)

// Initialize dark mode from localStorage or system preference
const initializeDarkMode = () => {
  if (typeof window !== 'undefined') {
    const stored = localStorage.getItem('darkMode')
    if (stored) {
      isDark.value = JSON.parse(stored)
    } else {
      // Check system preference
      isDark.value = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    
    // Apply the theme
    updateTheme()
  }
}

// Update theme in DOM
const updateTheme = () => {
  if (typeof window !== 'undefined') {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
}

// Toggle dark mode
const toggleDarkMode = () => {
  isDark.value = !isDark.value
}

// Watch for changes and persist to localStorage
watch(isDark, (newValue) => {
  if (typeof window !== 'undefined') {
    localStorage.setItem('darkMode', JSON.stringify(newValue))
    updateTheme()
  }
}, { immediate: false })

export function useDarkMode() {
  return {
    isDark,
    toggleDarkMode,
    initializeDarkMode
  }
}
