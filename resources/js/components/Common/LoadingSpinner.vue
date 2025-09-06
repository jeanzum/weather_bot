<template>
  <div
    :class="[
      'animate-spin inline-block border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]',
      sizeClasses
    ]"
    :style="{ width: `${size}px`, height: `${size}px` }"
    role="status"
    aria-label="Cargando"
  >
    <span class="sr-only">Cargando...</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: Number,
    default: 20
  }
})

const sizeClasses = computed(() => {
  if (props.size <= 16) return 'border-2'
  if (props.size <= 24) return 'border-2'
  if (props.size <= 32) return 'border-3'
  return 'border-4'
})
</script>
