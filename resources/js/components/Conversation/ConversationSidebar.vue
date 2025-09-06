<template>
  <div class="flex flex-col h-full bg-white dark:bg-gray-900">
    <!-- Simplified Header -->
    <div class="flex-shrink-0 p-6 border-b border-gray-100 dark:border-gray-800">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
            <span class="text-white text-lg">🌤️</span>
          </div>
          <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
              Weather Bot
            </h1>
          </div>
        </div>
        
        <!-- Simple New Button -->
        <button
          @click="$emit('new-conversation')"
          class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center transition-colors duration-200"
          title="Nueva conversación"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Simple Search -->
    <div class="p-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar..."
          class="w-full bg-gray-50 dark:bg-gray-800 border-0 rounded-lg px-10 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 transition-all duration-200"
        />
        <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>
    </div>

    <!-- Conversations List -->
    <div class="flex-1 overflow-y-auto px-2">
      <!-- Empty State -->
      <div v-if="filteredConversations.length === 0" class="text-center py-12 px-4">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">
          {{ searchQuery ? 'Sin resultados' : 'Sin conversaciones' }}
        </p>
      </div>
      
      <!-- Conversations -->
      <div class="space-y-1" v-else>
        <ConversationItem
          v-for="conversation in filteredConversations" 
          :key="conversation.id"
          :conversation="conversation"
          :is-active="currentConversationId === conversation.id"
          @select="$emit('conversation-selected', conversation)"
          @delete="$emit('delete-conversation', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ConversationItem from './ConversationItem.vue'

const props = defineProps({
  conversations: {
    type: Array,
    default: () => []
  },
  currentConversationId: {
    type: [String, Number],
    default: null
  }
})

defineEmits(['new-conversation', 'conversation-selected', 'delete-conversation'])

const searchQuery = ref('')

const filteredConversations = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.conversations
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  return props.conversations.filter(conversation => {
    const title = (conversation.title || '').toLowerCase()
    const lastMessage = (conversation.last_message || '').toLowerCase()
    return title.includes(query) || lastMessage.includes(query)
  })
})
</script>
