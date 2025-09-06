<template>
  <div class="p-4 bg-white border-t border-gray-200">
    <form @submit.prevent="handleSubmit" class="flex space-x-2">
      <input
        v-model="message"
        type="text"
        placeholder="Escribe tu pregunta sobre el clima..."
        :disabled="isLoading"
        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
        @keydown.enter.prevent="handleSubmit"
      />
      <button
        type="submit"
        :disabled="!message.trim() || isLoading"
        class="btn-primary px-6 py-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="!isLoading">Enviar</span>
        <span v-else class="flex items-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Enviando...
        </span>
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

const handleSubmit = () => {
  if (!message.value.trim()) return
  
  emit('send-message', message.value.trim())
  message.value = ''
}
</script>