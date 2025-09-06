<template>
  <div :class="messageClasses">
    <div v-if="message.role === 'assistant'" class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs sm:text-sm">
      🤖
    </div>
    
    <div :class="bubbleClasses">
      <div class="whitespace-pre-wrap text-sm sm:text-base">{{ message.content }}</div>
      <div class="text-xs mt-1 opacity-70 flex items-center justify-between">
        <span>{{ formatTime(message.created_at) }}</span>
        <span v-if="message.weather_data_used" class="ml-1" title="Datos meteorológicos utilizados">
          🌤️
        </span>
      </div>
    </div>

    <div v-if="message.role === 'user'" class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-gray-500 rounded-full flex items-center justify-center text-white text-xs sm:text-sm">
      👤
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: Object,
    required: true
  }
})

const messageClasses = computed(() => [
  'flex items-start space-x-2 w-full max-w-full',
  props.message.role === 'user' ? 'justify-end' : 'justify-start'
])

const bubbleClasses = computed(() => [
  'rounded-lg px-3 py-2 sm:px-4 sm:py-2 max-w-[85%] sm:max-w-md md:max-w-lg lg:max-w-xl break-words',
  props.message.role === 'user' 
    ? 'bg-blue-500 text-white rounded-br-sm' 
    : 'bg-gray-100 text-gray-800 rounded-bl-sm'
])

const formatTime = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  return date.toLocaleTimeString('es-ES', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}
</script>