<script setup>
import { onMounted, computed, shallowRef, h } from 'vue'

const props = defineProps({
  cols: {
    type: Number,
    default: 3,
    required: true,
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

function getRootCssVar(key) {
  // 添加SSR检查
  if (typeof window === 'undefined' || typeof document === 'undefined') return

  if (!key) return
  return getComputedStyle(document.documentElement).getPropertyValue(key)
}

function getSlidesPerView(cols, deviceSize) {
  if (cols && ['xs', 'md', 'lg', 'xl'].includes(deviceSize)) {
    if (cols == 2) return deviceSize === 'xs' ? 1 : 2
    let result
    const { max } = Math
    if (cols < 5) {
      result = {
        xs: max(1, cols - 2, props.minCols),
        md: max(2, cols - 2),
        lg: max(2, cols - 1),
      }
    } else {
      result = {
        xs: max(3, cols - 5, props.minCols),
        md: max(3, cols - 4),
        lg: max(4, cols - 2),
      }
    }
    return result[deviceSize]
  }
  return cols
}

const responsiveCols = computed(() => {
  const result = {}
  for (const device of ['xs', 'md', 'lg']) {
    result[device] = getSlidesPerView(props.cols, device)
  }
  return result
})

// 仅在客户端导入Swiper
const swiperComponents = shallowRef({
  Swiper: {
    setup(_, { slots }) {
      return () =>
        h('div', {}, h('div', { class: 'swiper-wrapper uninitialized' }, slots.default?.()))
    },
  },
  SwiperSlide: {
    setup(_, { slots }) {
      return () =>
        h(
          'div',
          { class: 'swiper-slide', style: { width: 100 / props.cols + '%' } },
          slots.default?.()
        )
    },
  },
  modules: [],
})
const swiperParams = shallowRef({})

onMounted(async () => {
  const { Swiper, SwiperSlide } = await import('swiper/vue')
  const { Navigation, Pagination, Scrollbar, Grid, Autoplay } = await import('swiper')

  const params = {
    spaceBetween: 12,
    slidesPerView: responsiveCols.value.xs,
    slidesPerGroup: responsiveCols.value.xs,
    spaceBetween: parseInt(getRootCssVar('--cm-grid-gutter-width-phone') || 12),
    pagination: {
      clickable: true,
    },
    navigation: true,
    scrollbar: true,
    createElements: true,
    simulateTouch: false,
  }

  swiperParams.value = params

  swiperComponents.value = {
    Swiper,
    SwiperSlide,
    modules: [Navigation, Pagination, Scrollbar, Grid, Autoplay],
  }
})
</script>

<template>
  <div>
    <component
      class="swiper-container"
      v-bind="swiperParams"
      :is="swiperComponents.Swiper"
      :modules="swiperComponents.modules"
      :class="{ 
        'swiper--grid': rows > 1
      }"
    >
      <template
        v-for="(vnode, i) in $slots.default?.()"
        :key="i"
      >
        <component
          :is="swiperComponents.SwiperSlide"
          v-for="(ctx, i2) in vnode.children"
          :key="i2"
        >
          <component :is="ctx" />
        </component>
      </template>
    </component>
  </div>
</template>
