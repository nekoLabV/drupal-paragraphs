<template>
  <figure class="paragraph-theme-media">
    <div class="paragraph-theme-media__resources">
      <img
        v-if="type === 'image'"
        :src="imgSrc"
        loading="lazy"
        decoding="async"
      />
      <VideoPlayer
        v-else
        :poster="imgSrc"
        :videoSrc
        :iframeSrc
      />
    </div>
    <figcaption
      v-if="caption"
      class="paragraph-theme-media__caption"
    >
      {{ caption }}
    </figcaption>
  </figure>
</template>

<script setup>
  import VideoPlayer from '../common/VideoPlayer.vue'

  defineProps({
    type: {
      type: String,
      default: 'image'
    },
    imgSrc: String,
    videoSrc: String,
    iframeSrc: String,
    caption: String
  })
</script>
