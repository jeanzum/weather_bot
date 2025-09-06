<template>
  <div :class="messageClasses">
    <div v-if="message.role === 'assistant'" class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm mr-2">
      🤖
    </div>
    <div v-if="message.role === 'user'" class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-white text-sm ml-2 order-2">
      👤
    </div>
    
    <div :class="bubbleClasses">
      <div class="whitespace-pre-wrap">{{ message.content }}</div>
      <div class="text-xs mt-1 opacity-70">
        {{ formatTime(message.created_at) }}
        <span v-if="message.weather_data_used" class="ml-1" title="Datos meteorológicos utilizados">
          🌤️
        </span>
      </div>
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
  'flex items-start space-x-2 max-w-4xl',
  props.message.role === 'user' ? 'ml-auto flex-row-reverse space-x-reverse' : ''
])

const bubbleClasses = computed(() => [
  'rounded-lg px-4 py-2 max-w-xs sm:max-w-md lg:max-w-lg',
  props.message.role === 'user' 
    ? 'bg-blue-500 text-white' 
    : 'bg-gray-100 text-gray-800'
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