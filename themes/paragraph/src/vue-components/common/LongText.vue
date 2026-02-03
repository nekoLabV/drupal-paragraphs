<template>
  <div
    v-if="html"
    :class="['cm-richtext', className]"
    v-html="html"
  ></div>
</template>

<script setup>
  defineProps({
    html: String,
    className: {
      type: String,
      default: '',
    },
  })
</script>
