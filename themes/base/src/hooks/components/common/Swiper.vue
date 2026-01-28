<script setup>
import { onMounted, computed, shallowRef, h } from 'vue'
// import { Swiper, SwiperSlide } from 'swiper/vue'
// import { Navigation, Pagination, Scrollbar, Grid, Autoplay } from 'swiper'

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

  params.breakpoints = {
    320: {
      slidesPerView: responsiveCols.value.xs,
      slidesPerGroup: responsiveCols.value.xs,
      spaceBetween: parseInt(getRootCssVar('--cm-grid-gutter-width-phone') || 12),
    },
    768: {
      slidesPerView: responsiveCols.value.md,
      slidesPerGroup: responsiveCols.value.md,
      spaceBetween: parseInt(getRootCssVar('--cm-grid-gutter-width-tablet') || 16),
    },
    1024: {
      slidesPerView: responsiveCols.value.lg,
      slidesPerGroup: responsiveCols.value.lg,
      spaceBetween: parseInt(getRootCssVar('--cm-grid-gutter-width-tablet') || 16),
    },
    1200: {
      slidesPerView: props.cols,
      slidesPerGroup: props.cols,
      spaceBetween: parseInt(getRootCssVar('--cm-grid-gutter-width-desktop') || 24),
    },
  }
  if (props.rows === 1 && props.cols === 1) params.autoHeight = true
  if (props.rows > 1) {
    params.grid = { rows: props.rows, fill: 'row' }
    if (params.breakpoints) {
      for (let val of Object.values(params.breakpoints)) {
        val.grid = { rows: props.rows, fill: 'row' }
      }
    }
  }
  swiperParams.value = params

  swiperComponents.value = {
    Swiper,
    SwiperSlide,
    modules: [Navigation, Pagination, Scrollbar, Grid, Autoplay],
  }
})

function setChildHeight(swiper) {
  swiper.el.style.removeProperty('--childHeight')
  swiper.slides.forEach(el => {
    let childHeight = el.firstElementChild?.offsetHeight || 0
    let childWidth = el.firstElementChild?.offsetWidth || 0
    let cssVarChildHeight = swiper.el.style.getPropertyValue('--childHeight') || 0
    if (parseInt(cssVarChildHeight) < childHeight) {
      swiper.el.style.setProperty('--childHeight', childHeight + 'px')
    }
    swiper.el.style.setProperty('--childWidth', childWidth + 'px')
  })
}

function swiperGirdInit(swiper) {
  if (swiper.params?.grid?.rows) swiper.el.style.setProperty('--rows', swiper.params?.grid?.rows)
  setChildHeight(swiper)
}

const slides = computed(() => {
  if (!props.items) return []
  
  const parser = new DOMParser()
  const doc = parser.parseFromString(props.items, 'text/html')
  return Array.from(doc.querySelectorAll('.base-theme-swiper__item')).map((el, i) => ({
    id: i,
    html: el.outerHTML
  }))
})
</script>

<template>
  <div class="base-theme-swiper swiper-overflow-wrap">
    <component
      class="swiper-container"
      v-bind="swiperParams"
      :is="swiperComponents.Swiper"
      :modules="swiperComponents.modules"
      :class="{
        'swiper--grid': rows > 1,
        'swiper-pagination--visible': swiperParams.pagination?.visible,
      }"
      @init="swiperGirdInit"
      @resize="setChildHeight"
    >
      <component
        :is="swiperComponents.SwiperSlide"
        v-for="(slide, i) in slides"
        :key="i"
      >
        <div v-html="slide.html"></div>
      </component>
    </component>
  </div>
</template>
