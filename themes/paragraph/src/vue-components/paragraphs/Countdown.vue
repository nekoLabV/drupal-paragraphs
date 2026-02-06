<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  start: String,
  end: {
    type: String,
    required: true,
  },
  text: {
    type: String,
    default: 'Day|Hour|Min|Sec',
  },
  countdownStyle: {
    type: String,
    default: 'block',
  },
})

const emit = defineEmits(['countdownEnd'])

const showCountdown = ref(false)

// 工具函数
const formatPlural = (count, singular, plural) => {
    return Number(count) != 1 ? plural : singular
  }
  const isChinese = str => /^[\u4e00-\u9fa5]+$/.test(str)
  const isUpperCase = str => /^[A-Z]$/.test(str)
  const getPluralStr = str => {
    if (isChinese(str)) return str
    return str + (isUpperCase(str.substr(1, 1)) ? 'S' : 's')
  }

  const getDiff = countdown => {
    let [d, h, m, s] = [0, 0, 0, 0]
    if (countdown > 0) {
      d = parseInt(countdown / 60 / 60 / 24)
      h = parseInt((countdown / 60 / 60) % 24)
      m = parseInt((countdown / 60) % 60)
      s = parseInt(countdown % 60)
    }
    // 补零处理
    const padZero = num => String(num).padStart(2, '0')
    return [d, h, m, s].map(padZero)
  }

  // 响应式状态
  const countdown = ref(0)
  const timeTexts = ref([])
  const timeTextPlurals = ref([])

  // 计算属性
  const currentDiff = computed(() => getDiff(countdown.value))

  // 获取时间文本
  const getTimeText = (num, index) => {
    return formatPlural(Number(num), timeTexts.value[index], timeTextPlurals.value[index])
  }

  // 生命周期钩子
  onMounted(() => {
    showCountdown.value = true
    if (!props.end) {
      console.error('Countdown end time is required.')
      emit('countdownEnd')
      return
    }

    const endTime = new Date(props.end).getTime()
    const startTime = props.start ? new Date(props.start).getTime() : Date.now()
    countdown.value = Math.floor((endTime - startTime) / 1000)

    if (countdown.value <= 0) {
      emit('countdownEnd')
      return
    }

    // 初始化文本
    timeTexts.value = props.text.split('|').map(str => str.trim())
    timeTextPlurals.value = timeTexts.value.map(getPluralStr)

    // 启动定时器
    const timer = setInterval(() => {
      if (countdown.value <= 0) {
        clearInterval(timer)
        emit('countdownEnd')
      }
      countdown.value--
    }, 1000)

    // 组件卸载时清理定时器
    onUnmounted(() => clearInterval(timer))
  })
</script>

<template>
  <div 
    v-if="showCountdown"
    class="paragraph-theme-countdown"
    :class="`paragraph-theme-countdown--style-${countdownStyle}`">
    <div class="paragraph-theme-countdown__container">
      <div 
        v-for="(num, index) in currentDiff" 
        :key="index"
        class="paragraph-theme-countdown-item">
        <span class="paragraph-theme-countdown-item__num">{{ num }}</span>
        <span class="paragraph-theme-countdown-item__text">
          {{ getTimeText(num, index) }}
        </span>
      </div>
    </div>
  </div>
</template>
