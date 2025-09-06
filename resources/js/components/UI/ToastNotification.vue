<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform opacity-0 scale-95 translate-y-2"
    enter-to-class="transform opacity-100 scale-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="transform opacity-100 scale-100 translate-y-0"
    leave-to-class="transform opacity-0 scale-95 translate-y-2"
  >
    <div 
      v-if="show" 
      :class="[
        'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform',
        type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
      ]"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <span class="mr-2">
            {{ type === 'error' ? '⚠️' : '✅' }}
          </span>
          <span class="text-sm font-medium">{{ message }}</span>
        </div>
        <button @click="$emit('close')" class="ml-2 text-white hover:text-gray-200">
          ×
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error'].includes(value)
  }
})

defineEmits(['close'])
</script>