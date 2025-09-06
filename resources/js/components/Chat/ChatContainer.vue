<template>
  <div class="flex-1 flex flex-col h-full overflow-hidden">
    <ChatHeader 
      :current-conversation="currentConversation"
      :is-loading="isLoading"
      @delete-conversation="$emit('delete-conversation')"
    />
    
    <ChatMessages 
      :messages="messages"
      :is-loading="isLoading"
    />
    
    <ChatInput 
      :is-loading="isLoading"
      @send-message="$emit('send-message', $event)"
    />
  </div>
</template>

<script setup>
import ChatHeader from './ChatHeader.vue'
import ChatMessages from './ChatMessages.vue'
import ChatInput from './ChatInput.vue'

defineProps({
  currentConversation: {
    type: Object,
    default: null
  },
  messages: {
    type: Array,
    default: () => []
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['delete-conversation', 'send-message'])
</script>