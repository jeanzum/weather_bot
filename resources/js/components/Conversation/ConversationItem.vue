<template>
  <div 
    @click="$emit('select')"
    :class="[
      'conversation-item cursor-pointer p-3 border-b border-gray-100 hover:bg-gray-50 transition-colors',
      { 'bg-blue-50 border-blue-200': isActive }
    ]"
  >
    <div class="font-medium text-sm text-gray-800 mb-1 truncate">
      {{ conversation.title || 'Nueva conversación' }}
    </div>
    <div class="text-xs text-gray-500 truncate">
      {{ conversation.last_message }}
    </div>
    <div class="text-xs text-gray-400 mt-1">
      {{ formatDate(conversation.last_message_at) }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  conversation: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
})

defineEmits(['select'])

const formatDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  
  if (diff < 60 * 1000) {
    return 'Ahora'
  } else if (diff < 60 * 60 * 1000) {
    return `${Math.floor(diff / (60 * 1000))} min`
  } else if (diff < 24 * 60 * 60 * 1000) {
    return `${Math.floor(diff / (60 * 60 * 1000))} h`
  } else {
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })
  }
}
</script>