<template>
  <div class="flex-shrink-0 p-6 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
    <form @submit.prevent="handleSubmit" class="flex items-end space-x-3">
      <!-- Message input -->
      <div class="flex-1 relative">
        <input
          ref="messageInput"
          v-model="message"
          type="text"
          placeholder="Escribe tu pregunta sobre el clima..."
          :disabled="isLoading"
          class="w-full bg-gray-50 dark:bg-gray-800 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          @keydown.enter.prevent="handleSubmit"
        />
      </div>

      <!-- Send button -->
      <button
        type="submit"
        :disabled="!message.trim() || isLoading"
        class="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white rounded-xl px-4 py-3 transition-colors duration-200 disabled:cursor-not-allowed"
        title="Enviar mensaje"
      >
        <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
        <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['send-message'])

const message = ref('')
const messageInput = ref(null)

const handleSubmit = () => {
  if (!message.value.trim()) return
  
  emit('send-message', message.value.trim())
  message.value = ''
}
</script>