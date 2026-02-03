<template>
  <div class="paragraph-theme-image">
    <div
      v-if="image?.url"
      class="el-ratio"
      :class="`el-ratio--${imageRatio} object-fit--${imageFit}`"
    >
      <img
        :src="image?.url"
        loading="lazy"
        decoding="async"
      />
    </div>
    <div
      v-if="caption"
      class="paragraph-theme-image__caption"
    >
      {{ caption }}
    </div>
  </div>
</template>

<script setup>
  defineProps({
    image: {
      type: Object,
      default: () => ({})
    },
    imageRatio: {
      type: String,
      default: '16x9'
    },
    imageFit: {
      type: String,
      default: 'contain'
    },
    caption: {
      type: String,
      default: ''
    }
  })
</script>
