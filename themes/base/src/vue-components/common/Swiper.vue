<script setup>
import { onMounted, computed, shallowRef, h } from 'vue'

const props = defineProps({
  items: {
    type: String,
    default: [] // html 字符串
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
  setSwiperParams()

  const { Swiper, SwiperSlide } = await import('swiper/vue')
  const { Navigation, Pagination, Scrollbar, Grid, Autoplay } = await import('swiper')
  swiperComponents.value = {
    Swiper,
    SwiperSlide,
    modules: [Navigation, Pagination, Scrollbar, Grid, Autoplay],
  }
})

function setSwiperParams() {
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
    simulateTouch: false
  }

  if (props.swiperOptions?.autoplay) {
    params.autoplay = Object.assign({}, props.swiperOptions.autoplay, {
      stopOnLastSlide: false, // 切换到最后一张slide后是否停止切换
      disableOnInteraction: false, // 用户操作swiper后（如滑动、翻页）是否禁用自动切换
      pauseOnMouseEnter: true, // 鼠标悬停在swiper上是否暂停自动切换
    })
  }

  if (props.swiperOptions?.pagination) {
    params.pagination = { ...params.pagination, ...props.swiperOptions.pagination }
  }

  if (props.swiperOptions?.hasOwnProperty('navigation')) {
    params.navigation = props.swiperOptions.navigation
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

function getRootCssVar(key) {
  // 添加SSR检查
  if (typeof window === 'undefined' || typeof document === 'undefined') return

  if (!key) return
  return getComputedStyle(document.documentElement).getPropertyValue(key)
}

const responsiveCols = computed(() => {
  const result = {}
  for (const device of ['xs', 'md', 'lg']) {
    result[device] = getSlidesPerView(props.cols, device)
  }
  return result
})

function setChildHeight(swiper) {
  swiper.el.style.removeProperty('--childHeight')
  swiper.slides.forEach(el => {
    let childHeight = el.firstElementChild?.offsetHeight || 0
    let cssVarChildHeight = swiper.el.style.getPropertyValue('--childHeight') || 0
    if (parseInt(cssVarChildHeight) < childHeight) {
      swiper.el.style.setProperty('--childHeight', childHeight + 'px')
    }
  })
}

function swiperGirdInit(swiper) {
  if (swiper.params?.grid?.rows) {
    swiper.el.style.setProperty('--rows', swiper.params.grid.rows)
  } else {
    swiper.el.style.setProperty('--rows', 1)
  }
  setChildHeight(swiper)
}
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
        v-for="(slide, i) in items"
        :key="i"
      >
        <div v-inline-html="slide"></div>
      </component>
    </component>
  </div>
</template>
