<template>
  <div
    class="el-video-player"
    v-if="videoSrc || iframeSrc"
  >
    <video
      v-if="videoSrc"
      ref="playerRef"
      :src="videoSrc"
      :poster="poster"
    ></video>
    <iframe
      v-else-if="iframeSrc"
      scrolling="no"
      border="0"
      frameborder="no"
      framespacing="0"
      allowfullscreen="true"
      v-lazy-src="iframeSrc"
    ></iframe>
  </div>
</template>

<script setup>
  import { onMounted, onBeforeUnmount, useTemplateRef } from 'vue'

  const props = defineProps({
    poster: String,
    videoSrc: String,
    iframeSrc: String,
  })

  let player = null
  const playerRef = useTemplateRef('playerRef')

  onMounted(async () => {
    if (!playerRef.value || !props.videoSrc) return

    const PlyrModule = await import('plyr')
    const Plyr = PlyrModule.default
    const plyrSvg = await import('plyr/dist/plyr.svg').then(res => res.default)

    player = new Plyr(playerRef.value, {
      controls: ['play-large', 'play', 'progress', 'current-time', 'fullscreen'],
      iconUrl: plyrSvg,
    })
  })

  onBeforeUnmount(() => {
    if (player) {
      player.destroy()
    }
  })
</script>
