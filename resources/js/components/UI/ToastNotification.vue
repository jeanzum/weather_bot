<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform opacity-0 scale-95 translate-y-2"
    enter-to-class="transform opacity-100 scale-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="transform opacity-100 scale-100 translate-y-0"
    leave-to-class="transform opacity-0 scale-95 translate-y-2"
  >
    <div 
      v-if="show" 
      :class="[
        'fixed top-6 right-6 z-50 p-4 rounded-2xl shadow-xl max-w-sm transform backdrop-blur-sm',
        type === 'error' 
          ? 'bg-red-500/90 dark:bg-red-600/90 text-white border border-red-400/20 dark:border-red-500/20' 
          : 'bg-green-500/90 dark:bg-green-600/90 text-white border border-green-400/20 dark:border-green-500/20'
      ]"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <span class="text-lg">
              {{ type === 'error' ? '⚠️' : '✅' }}
            </span>
          </div>
          <span class="text-sm font-medium leading-relaxed">{{ message }}</span>
        </div>
        <button 
          @click="$emit('close')" 
          class="ml-3 text-white/80 hover:text-white hover:bg-white/10 rounded-full p-1 transition-all duration-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error'].includes(value)
  }
})

defineEmits(['close'])
</script>