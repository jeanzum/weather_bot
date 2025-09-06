<template>
  <div class="relative">
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="inputClasses"
      v-bind="$attrs"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('focus', $event)"
      @blur="$emit('blur', $event)"
    />
    <label
      v-if="label"
      :for="id"
      :class="labelClasses"
    >
      {{ label }}
    </label>
    <div v-if="$slots.icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <slot name="icon" />
    </div>
    <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3 flex items-center">
      <slot name="suffix" />
    </div>
  </div>
  <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
    {{ error }}
  </p>
  <p v-else-if="hint" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
    {{ hint }}
  </p>
</template>

<script setup>
import { computed, useId } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  }
})

defineEmits(['update:modelValue', 'focus', 'blur'])

const id = useId()

const inputClasses = computed(() => {
  const baseClasses = [
    'block w-full border border-gray-300 dark:border-gray-600 rounded-lg',
    'bg-white dark:bg-gray-800 text-gray-900 dark:text-white',
    'placeholder-gray-400 dark:placeholder-gray-500',
    'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400',
    'transition-all duration-200',
    'disabled:opacity-50 disabled:cursor-not-allowed'
  ]

  const sizeClasses = {
    small: 'px-3 py-2 text-sm',
    medium: 'px-4 py-3 text-sm',
    large: 'px-4 py-4 text-base'
  }

  const errorClasses = props.error
    ? 'border-red-300 dark:border-red-600 focus:ring-red-500 focus:border-red-500'
    : ''

  const iconClasses = props.$slots?.icon ? 'pl-10' : ''
  const suffixClasses = props.$slots?.suffix ? 'pr-10' : ''

  return [
    ...baseClasses,
    sizeClasses[props.size],
    errorClasses,
    iconClasses,
    suffixClasses
  ].join(' ')
})

const labelClasses = computed(() => {
  const baseClasses = [
    'absolute left-3 transition-all duration-200 pointer-events-none',
    'text-gray-500 dark:text-gray-400'
  ]

  const sizeClasses = {
    small: 'text-xs -top-2 bg-white dark:bg-gray-800 px-1',
    medium: 'text-sm -top-2.5 bg-white dark:bg-gray-800 px-1',
    large: 'text-sm -top-2.5 bg-white dark:bg-gray-800 px-1'
  }

  return [...baseClasses, sizeClasses[props.size]].join(' ')
})
</script>
