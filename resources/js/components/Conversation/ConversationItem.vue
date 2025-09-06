<template>
  <div
    :class="containerClasses"
    class="relative"
  >
    <div 
      @click="$emit('select')"
      class="p-4 flex-1"
    >
      <div class="flex items-start justify-between mb-2">
        <h3 :class="titleClasses">
          {{ conversation.title || 'Nueva conversación' }}
        </h3>
        <div class="flex items-center space-x-2">
          <span :class="timeClasses">
            {{ formatDate(conversation.last_message_at) }}
          </span>
          
          <!-- Menu button -->
          <div class="relative">
            <button
              @click.stop="showMenu = !showMenu"
              class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 opacity-0 group-hover:opacity-100"
              :class="{ 'opacity-100': showMenu }"
            >
              <svg class="w-4 h-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path d="M10 4a2 2 0 100-4 2 2 0 000 4z"/>
                <path d="M10 20a2 2 0 100-4 2 2 0 000 4z"/>
              </svg>
            </button>
            
            <!-- Dropdown menu -->
            <div v-if="showMenu" 
                 class="absolute right-0 top-8 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10">
              <button
                @click.stop="deleteConversation"
                class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
              >
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  <span>Eliminar conversación</span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <p :class="messageClasses">
        {{ conversation.last_message || 'Sin mensajes aún...' }}
      </p>
    </div>

    <!-- Active indicator -->
    <div v-if="isActive" 
         class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-500 rounded-r-full" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  conversation: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['select', 'delete'])

const showMenu = ref(false)

// Close menu when clicking outside
const closeMenu = (event) => {
  if (!event.target.closest('.relative')) {
    showMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
})

const deleteConversation = () => {
  showMenu.value = false
  emit('delete', props.conversation.id)
}

const containerClasses = computed(() => [
  'group relative cursor-pointer mb-2 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg',
  props.isActive
    ? 'bg-blue-50 dark:bg-blue-900/20'
    : ''
].join(' '))

const titleClasses = computed(() => [
  'font-medium text-sm truncate transition-colors duration-200',
  props.isActive
    ? 'text-blue-700 dark:text-blue-300'
    : 'text-gray-900 dark:text-white'
].join(' '))

const timeClasses = computed(() => [
  'text-xs transition-colors duration-200 flex-shrink-0',
  props.isActive
    ? 'text-blue-500 dark:text-blue-400'
    : 'text-gray-400 dark:text-gray-500'
].join(' '))

const messageClasses = computed(() => [
  'text-sm leading-relaxed line-clamp-1 transition-colors duration-200',
  props.isActive
    ? 'text-blue-600 dark:text-blue-300'
    : 'text-gray-600 dark:text-gray-300'
].join(' '))

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
  } else if (diff < 7 * 24 * 60 * 60 * 1000) {
    return `${Math.floor(diff / (24 * 60 * 60 * 1000))} d`
  } else {
    return date.toLocaleDateString('es-ES', { 
      day: '2-digit', 
      month: 'short'
    })
  }
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>