<template>
  <div
    :class="cardClasses"
    v-bind="$attrs"
  >
    <div v-if="$slots.header" class="border-b border-gray-200 dark:border-gray-700 p-6">
      <slot name="header" />
    </div>
    
    <div :class="bodyClasses">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="border-t border-gray-200 dark:border-gray-700 p-6">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'elevated', 'outlined', 'glass'].includes(value)
  },
  padding: {
    type: String,
    default: 'medium',
    validator: (value) => ['none', 'small', 'medium', 'large'].includes(value)
  },
  rounded: {
    type: String,
    default: 'medium',
    validator: (value) => ['none', 'small', 'medium', 'large', 'xl'].includes(value)
  }
})

const cardClasses = computed(() => {
  const baseClasses = [
    'bg-white dark:bg-gray-900 transition-all duration-200'
  ]

  const variantClasses = {
    default: 'shadow-sm',
    elevated: 'shadow-lg hover:shadow-xl',
    outlined: 'border border-gray-200 dark:border-gray-700',
    glass: 'backdrop-blur-md bg-white/80 dark:bg-gray-900/80 border border-white/20 dark:border-gray-700/50'
  }

  const roundedClasses = {
    none: 'rounded-none',
    small: 'rounded-md',
    medium: 'rounded-lg',
    large: 'rounded-xl',
    xl: 'rounded-2xl'
  }

  return [
    ...baseClasses,
    variantClasses[props.variant],
    roundedClasses[props.rounded]
  ].join(' ')
})

const bodyClasses = computed(() => {
  const paddingClasses = {
    none: '',
    small: 'p-4',
    medium: 'p-6',
    large: 'p-8'
  }

  return paddingClasses[props.padding]
})
</script>
