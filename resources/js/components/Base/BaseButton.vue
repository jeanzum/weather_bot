<template>
  <component
    :is="tag"
    :class="buttonClasses"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <div class="flex items-center justify-center space-x-2">
      <LoadingSpinner v-if="loading" :size="18" />
      <slot v-if="!loading" name="icon" />
      <span v-if="$slots.default">
        <slot />
      </span>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import LoadingSpinner from '../Common/LoadingSpinner.vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  rounded: {
    type: String,
    default: 'medium',
    validator: (value) => ['none', 'small', 'medium', 'large', 'full'].includes(value)
  },
  tag: {
    type: String,
    default: 'button'
  }
})

const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex items-center justify-center font-medium transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900',
    'disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none'
  ]

  // Size classes
  const sizeClasses = {
    small: 'px-3 py-1.5 text-sm',
    medium: 'px-4 py-2 text-sm',
    large: 'px-6 py-3 text-base'
  }

  // Variant classes
  const variantClasses = {
    primary: [
      'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
      'hover:from-blue-600 hover:to-indigo-700 focus:ring-blue-500',
      'shadow-md hover:shadow-lg transform hover:scale-105'
    ],
    secondary: [
      'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600',
      'hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-gray-500',
      'shadow-sm hover:shadow-md'
    ],
    ghost: [
      'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
      'focus:ring-gray-500'
    ],
    danger: [
      'bg-gradient-to-r from-red-500 to-red-600 text-white',
      'hover:from-red-600 hover:to-red-700 focus:ring-red-500',
      'shadow-md hover:shadow-lg'
    ]
  }

  // Rounded classes
  const roundedClasses = {
    none: 'rounded-none',
    small: 'rounded-sm',
    medium: 'rounded-lg',
    large: 'rounded-xl',
    full: 'rounded-full'
  }

  return [
    ...baseClasses,
    sizeClasses[props.size],
    ...variantClasses[props.variant],
    roundedClasses[props.rounded]
  ].join(' ')
})
</script>
