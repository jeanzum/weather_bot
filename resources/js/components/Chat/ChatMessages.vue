<template>
  <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-800" ref="messagesContainer">
    <div class="p-6">
      <!-- Simple welcome message -->
      <div v-if="messages.length === 0" class="text-center py-16">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <span class="text-white text-2xl">🌤️</span>
        </div>
        
        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
          Asistente Meteorológico
        </h3>
        
        <p class="text-gray-600 dark:text-gray-300 max-w-md mx-auto">
          Pregúntame sobre el clima en cualquier ciudad del mundo
        </p>
      </div>

      <!-- Chat messages -->
      <div class="space-y-4">
        <MessageBubble
          v-for="message in messages"
          :key="message.id"
          :message="message"
        />

        <!-- Simple typing indicator -->
        <div v-if="isLoading" class="flex items-start space-x-3">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <span class="text-white text-sm">🤖</span>
          </div>
          <div class="bg-white dark:bg-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:0.1s]"></div>
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
            </div>
          </div>
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