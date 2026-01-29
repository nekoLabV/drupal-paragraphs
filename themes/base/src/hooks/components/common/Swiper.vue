<script setup>
import { computed, onMounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'

const props = defineProps({
  items: {
    type: String,
    default: '' // html 字符串
  },
  cols: {
    type: Number,
    default: 3
  },
  minCols: {
    type: Number,
    default: 1
  },
  rows: {
    type: Number,
    default: 1,
  },
  swiperOptions: {
    type: Object,
    default: () => ({}),
  },
})

const slides = computed(() => {
  if (!props.items) return []

  const parser = new DOMParser()
  const doc = parser.parseFromString(props.items, 'text/html')

  return Array.from(
    doc.querySelectorAll('.base-theme-swiper__item')
  ).map((el, i) => ({
    id: i,
    html: el.outerHTML,
  }))
})
</script>

<template>
  <div class="base-theme-swiper swiper-overflow-wrap">
    <Swiper
      v-if="slides.length"
      class="swiper-container"
    >
      <SwiperSlide
        v-for="(slide, i) in slides"
        :key="i"
      >
        <!-- <div class="swiper-slide__content" v-html="slide.html"></div> -->
         <div>1111</div>
      </SwiperSlide>
    </Swiper>
  </div>
</template>
