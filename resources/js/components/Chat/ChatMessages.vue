<template>
  <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
    <!-- Mensaje de bienvenida -->
    <div v-if="messages.length === 0" class="text-center py-12">
      <div class="text-6xl mb-4">🌦️</div>
      <h3 class="text-lg font-medium text-gray-800 mb-2">
        ¡Hola! Soy tu asistente meteorológico
      </h3>
      <p class="text-gray-600 max-w-md mx-auto">
        Puedo ayudarte con información del clima, pronósticos del tiempo, 
        y responder preguntas sobre meteorología. ¿Sobre qué ciudad te gustaría saber?
      </p>
    </div>

    <!-- Mensajes del chat -->
    <MessageBubble
      v-for="message in messages"
      :key="message.id"
      :message="message"
    />

    <!-- Indicador de escritura -->
    <div v-if="isLoading" class="flex items-start space-x-2">
      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm">
        🤖
      </div>
      <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-xs">
        <div class="flex space-x-1">
          <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
          <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
          <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import MessageBubble from './MessageBubble.vue'

const props = defineProps({
  messages: {
    type: Array,
    default: () => []
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const messagesContainer = ref(null)

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

watch(() => props.messages, () => {
  scrollToBottom()
}, { deep: true })

watch(() => props.isLoading, () => {
  if (props.isLoading) {
    scrollToBottom()
  }
})
</script>