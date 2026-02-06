<script setup>
import { onMounted, computed, shallowRef, ref, watch } from 'vue'
import Swiper from 'swiper'
import { Navigation, Pagination, Scrollbar, Grid, Autoplay } from 'swiper'

const props = defineProps({
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

const swiperParams = shallowRef({})

onMounted(async () => {
  setSwiperParams()
  initSwiper()
})

const swiperRef = ref(null)
let swiperInstance = null
const initSwiper = () => {
  if (swiperInstance) {
    swiperInstance.destroy() // 如果存在旧实例，先销毁
  }
  swiperInstance = new Swiper(swiperRef.value, {
    modules: [Navigation, Pagination, Scrollbar, Grid, Autoplay],
    observer: true,
    observeParents: true,
    ...swiperParams.value, 
    
    on: {
      init: (swiper) => {
        swiperGirdInit(swiper)
      },
      resize: (swiper) => {
        setChildHeight(swiper)
      }
    }
  })
}

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

watch(() => [props.cols, props.rows], () => {
  setSwiperParams()
  initSwiper()
}, { deep: true })
</script>

<template>
  <div class="paragraph-theme-swiper swiper-overflow-wrap">
    <div ref="swiperRef" class="swiper">
      <div class="swiper-wrapper">
        <slot></slot>
      </div>
    </div>
  </div>
</template>
