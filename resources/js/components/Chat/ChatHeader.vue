<template>
  <div class="flex-shrink-0 p-3 sm:p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 sm:space-x-3">
        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
          <span class="text-white text-sm sm:text-lg">🤖</span>
        </div>
        
        <div>
          <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate max-w-48 sm:max-w-none">
            {{ currentConversation?.title || 'Asistente Meteorológico' }}
          </h2>
          
          <p v-if="isLoading" class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">
            Escribiendo...
          </p>
          <p v-else class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            En línea
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentConversation: {
    type: Object,
    default: null
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['delete-conversation'])
</script>