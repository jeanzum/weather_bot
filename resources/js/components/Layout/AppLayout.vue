<template>
  <div class="flex flex-col sm:flex-row h-screen bg-gray-100">
    <ToastNotification 
      :show="toast.show"
      :message="toast.message"
      :type="toast.type"
      @close="hideToast"
    />

    <ConversationSidebar 
      :conversations="conversations"
      :current-conversation-id="currentConversation?.id"
      @new-conversation="startNewConversation"
      @conversation-selected="selectConversation"
    />

    <ChatContainer 
      :current-conversation="currentConversation"
      :messages="messages"
      :is-loading="isLoading"
      @delete-conversation="deleteCurrentConversation"
      @send-message="sendMessage"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import axios from 'axios'
import ToastNotification from '../UI/ToastNotification.vue'
import ConversationSidebar from '../Conversation/ConversationSidebar.vue'
import ChatContainer from '../Chat/ChatContainer.vue'

// State
const conversations = ref([])
const currentConversation = ref(null)
const messages = ref([])
const isLoading = ref(false)
const toast = reactive({
  show: false,
  message: '',
  type: 'success'
})

// Toast methods
const showToast = (message, type = 'error') => {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => hideToast(), 5000)
}

const hideToast = () => {
  toast.show = false
}

// API methods
const loadConversations = async () => {
  try {
    const { data } = await axios.get('/api/conversations')
    if (data.success) {
      conversations.value = data.data
    }
  } catch (err) {
    showToast('Error al cargar las conversaciones')
  }
}

const loadConversation = async (conversationId) => {
  try {
    const { data } = await axios.get(`/api/conversations/${conversationId}`)
    if (data.success) {
      messages.value = data.data.messages
      await nextTick()
      scrollToBottom()
    }
  } catch (err) {
    showToast('Error al cargar la conversación')
  }
}

const sendMessage = async (messageText) => {
  if (!messageText.trim() || isLoading.value) return

  isLoading.value = true

  // Add user message immediately to UI
  const userMessage = {
    id: Date.now(),
    content: messageText,
    role: 'user',
    created_at: new Date().toISOString()
  }
  messages.value.push(userMessage)

  try {
    const { data } = await axios.post('/api/chat', {
      message: messageText,
      conversation_id: currentConversation.value?.id
    })

    if (data.success) {
      // Update conversation if it's new
      if (!currentConversation.value) {
        currentConversation.value = { id: data.data.conversation_id }
        await loadConversations()
      }

      // Replace user message with server response and add assistant message
      messages.value = messages.value.filter(m => m.id !== userMessage.id)
      messages.value.push(data.data.user_message)
      messages.value.push(data.data.assistant_message)

      await loadConversations()
    } else {
      messages.value = messages.value.filter(m => m.id !== userMessage.id)
      
      if (data.error_type === 'security_violation') {
        showToast('⚠️ ' + data.message)
      } else {
        showToast('Error: ' + data.message)
      }
    }
  } catch (err) {
    messages.value = messages.value.filter(m => m.id !== userMessage.id)
    
    if (err.response?.data?.message) {
      showToast(err.response.data.message)
    } else {
      showToast('Error al enviar el mensaje')
    }
  } finally {
    isLoading.value = false
  }
}

const deleteCurrentConversation = async () => {
  if (!currentConversation.value) return

  try {
    const { data } = await axios.delete(`/api/conversations/${currentConversation.value.id}`)

    if (data.success) {
      currentConversation.value = null
      messages.value = []
      await loadConversations()
      showToast('Conversación eliminada', 'success')
    }
  } catch (err) {
    showToast('Error al eliminar la conversación')
  }
}

// Conversation methods
const startNewConversation = () => {
  currentConversation.value = null
  messages.value = []
}

const selectConversation = async (conversation) => {
  currentConversation.value = conversation
  await loadConversation(conversation.id)
}

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.querySelector('[ref="messagesContainer"]')
    if (container) {
      container.scrollTop = container.scrollHeight
    }
  })
}

// Initialize
onMounted(() => {
  loadConversations()
})
</script>

