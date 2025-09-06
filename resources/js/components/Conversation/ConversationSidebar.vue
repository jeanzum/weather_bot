<template>
  <div class="w-full sm:w-80 md:w-1/3 bg-white border-r border-gray-200 flex flex-col">
    <!-- Header del Sidebar -->
    <div class="p-3 sm:p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 truncate">
          🌤️ Weather Chat
        </h1>
        <button @click="$emit('new-conversation')" class="bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors px-2 py-1 sm:px-3 sm:py-1 text-xs sm:text-sm flex-shrink-0">
          Nueva
        </button>
      </div>
    </div>

    <!-- Lista de Conversaciones -->
    <div class="flex-1 overflow-y-auto">
      <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500">
        No hay conversaciones aún.
        <br>¡Empieza una nueva!
      </div>
      
      <ConversationItem
        v-for="conversation in conversations" 
        :key="conversation.id"
        :conversation="conversation"
        :is-active="currentConversationId === conversation.id"
        @select="$emit('conversation-selected', conversation)"
      />
    </div>
  </div>
</template>

<script setup>
import ConversationItem from './ConversationItem.vue'

defineProps({
  conversations: {
    type: Array,
    default: () => []
  },
  currentConversationId: {
    type: Number,
    default: null
  }
})

defineEmits(['new-conversation', 'conversation-selected'])
</script>