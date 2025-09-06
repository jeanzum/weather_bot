<template>
  <div :class="messageClasses">
    <!-- Assistant Avatar -->
    <div v-if="message.role === 'assistant'" class="flex-shrink-0">
      <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
        <span class="text-white text-sm">🤖</span>
      </div>
    </div>
    
    <!-- Message Content -->
    <div :class="bubbleWrapperClasses">
      <div :class="bubbleClasses">
        <div class="whitespace-pre-wrap leading-relaxed" v-html="formatContent(message.content)" />
        
        <!-- Weather indicator if present -->
        <div v-if="message.weather_data_used" 
             class="inline-flex items-center space-x-1 mt-2 text-xs opacity-70">
          <span>🌤️</span>
        </div>
      </div>
    </div>

    <!-- User Avatar -->
    <div v-if="message.role === 'user'" class="flex-shrink-0">
      <div class="w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-700 dark:from-gray-600 dark:to-gray-800 rounded-lg flex items-center justify-center">
        <span class="text-white text-sm">👤</span>
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
  'flex items-end space-x-3 w-full mb-4',
  props.message.role === 'user' ? 'justify-end' : 'justify-start'
])

const bubbleWrapperClasses = computed(() => [
  'max-w-xs sm:max-w-md lg:max-w-lg xl:max-w-xl',
  props.message.role === 'user' ? 'ml-12' : 'mr-12'
])

const bubbleClasses = computed(() => [
  'rounded-2xl px-4 py-3 shadow-sm transition-all duration-200',
  props.message.role === 'user' 
    ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-br-sm' 
    : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-bl-sm'
])

const formatContent = (content) => {
  // Basic formatting for better readability
  return content
    .replace(/\n/g, '<br>')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-sm">$1</code>')
}
</script>