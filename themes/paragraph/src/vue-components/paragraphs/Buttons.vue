<script setup>
import { computed } from 'vue'

const props = defineProps({
  direction: {
    type: String,
    default: 'row' // 'row' | 'column'
  },
  marginTop: {
    type: String,
    default: 'none'
  },
  marginBottom: {
    type: String,
    default: 'none'
  }
})

const className = computed(() => {
  const rootBlock = 'paragraph-theme-buttons'
  let resultClassArr = [
    `${rootBlock}--margin-top-${props.marginTop}`,
    `${rootBlock}--margin-bottom-${props.marginBottom}`
  ]

  return resultClassArr.join(' ')
})
</script>

<template>
  <div 
    :class="[
      'paragraph-theme-buttons', 
      `paragraph-theme-buttons--direction-${direction}`,
      className
    ]">
    <slot></slot>
  </div>
</template>