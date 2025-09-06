<template>
  <div class="p-4 bg-white border-b border-gray-200">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-medium text-gray-800">
        {{ currentConversation?.title || 'Asistente Meteorológico' }}
      </h2>
      <div class="flex items-center space-x-2">
        <span v-if="isLoading" class="text-sm text-blue-500 flex items-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Escribiendo...
        </span>
        <button 
          v-if="currentConversation"
          @click="$emit('delete-conversation')"
          class="text-red-500 hover:text-red-700 text-sm px-2 py-1 rounded hover:bg-red-50 transition-colors"
        >
          Eliminar
        </button>
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