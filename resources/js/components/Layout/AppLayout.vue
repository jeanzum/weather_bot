<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <ToastNotification 
      :show="toast.show"
      :message="toast.message"
      :type="toast.type"
      @close="hideToast"
    />

    <!-- Theme toggle in top right -->
    <ThemeToggle variant="floating" size="medium" />

    <!-- Mobile menu button -->
    <button 
      @click="toggleSidebar"
      class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white dark:bg-gray-800 shadow-lg"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- Sidebar -->
    <div 
      :class="[
        'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden',
        'fixed lg:relative inset-y-0 left-0 z-40',
        'w-80 lg:w-80',
        'transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <ConversationSidebar 
        :conversations="conversations"
        :current-conversation-id="currentConversation?.id"
        @new-conversation="startNewConversation"
        @conversation-selected="selectConversation"
        @delete-conversation="deleteConversation"
      />
    </div>

    <!-- Overlay for mobile -->
    <div 
      v-if="sidebarOpen"
      @click="closeSidebar"
      class="lg:hidden fixed inset-0 z-30 bg-black bg-opacity-50"
    ></div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-900 overflow-hidden lg:ml-0">
      <ChatContainer 
        :current-conversation="currentConversation"
        :messages="messages"
        :is-loading="isLoading"
        @delete-conversation="deleteCurrentConversation"
        @send-message="sendMessage"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useDarkMode } from '../../composables/useDarkMode.js'
import ToastNotification from '../UI/ToastNotification.vue'
import ThemeToggle from '../Common/ThemeToggle.vue'
import ConversationSidebar from '../Conversation/ConversationSidebar.vue'
import ChatContainer from '../Chat/ChatContainer.vue'

// Dark mode
const { initializeDarkMode } = useDarkMode()

// State
const conversations = ref([])
const currentConversation = ref(null)
const messages = ref([])
const isLoading = ref(false)
const sidebarOpen = ref(false)
const toast = reactive({
  show: false,
  message: '',
  type: 'success'
})

// User location state
const userLocation = ref({
  city: null,
  latitude: null,
  longitude: null,
  isLoading: false,
  hasPermission: false
})

// Sidebar methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

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

// Location methods
const getUserLocation = async () => {
  if (!navigator.geolocation) {
    return
  }

  userLocation.value.isLoading = true

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000 // 5 minutes
      })
    })

    userLocation.value.latitude = position.coords.latitude
    userLocation.value.longitude = position.coords.longitude
    userLocation.value.hasPermission = true

    // Try to get city name from coordinates
    await getCityFromCoordinates(position.coords.latitude, position.coords.longitude)
    
  } catch (error) {
    userLocation.value.hasPermission = false
  } finally {
    userLocation.value.isLoading = false
  }
}

const getCityFromCoordinates = async (lat, lon) => {
  try {
    // Using a simple reverse geocoding service
    const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=es`)
    const data = await response.json()
    
    userLocation.value.city = data.city || data.locality || data.principalSubdivision || null
  } catch (error) {
    // Silently handle geocoding errors
  }
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
    const payload = {
      message: messageText,
      conversation_id: currentConversation.value?.id
    }

    // Include location data if available
    if (userLocation.value.hasPermission) {
      if (userLocation.value.city) payload.user_city = userLocation.value.city
      if (userLocation.value.latitude) payload.user_latitude = userLocation.value.latitude
      if (userLocation.value.longitude) payload.user_longitude = userLocation.value.longitude
    }

    const { data } = await axios.post('/api/chat', payload)

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

const deleteConversation = async (conversationId) => {
  try {
    const { data } = await axios.delete(`/api/conversations/${conversationId}`)

    if (data.success) {
      // If it's the current conversation, clear it
      if (currentConversation.value?.id === conversationId) {
        currentConversation.value = null
        messages.value = []
      }
      await loadConversations()
      showToast('Conversación eliminada', 'success')
    }
  } catch (err) {
    showToast('Error al eliminar la conversación')
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
  initializeDarkMode()
  loadConversations()
  // Request user location for enhanced experience
  getUserLocation()
})
</script>

