import { ref, computed, onMounted, onUnmounted, h, render } from 'vue'

export const useCountdown = (timestamp) => {
  const countdown = ref(0)
  const timeTexts = ref([])
  const timeTextPlurals = ref([])
  const hintText = 'Day|Hour|Min|Sec'

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

  const endTime = new Date(timestamp).getTime()
  const startTime = Date.now()
  countdown.value = Math.floor((endTime - startTime) / 1000)
  if (countdown.value <= 0) {
    console.warn('倒计时结束')
    return
  }

  timeTexts.value = hintText.split('|').map(str => str.trim())
  timeTextPlurals.value = timeTexts.value.map(getPluralStr)

  // 获取时间文本
  const getTimeText = (num, index) => {
    return formatPlural(Number(num), timeTexts.value[index], timeTextPlurals.value[index])
  }

  // 当前时间差
  const currentDiff = computed(() => getDiff(countdown.value))

  const CountdownComponent = {
    setup() {
      let timer
      onMounted(() => {
        timer = setInterval(() => {
          if (countdown.value <= 0) {
            clearInterval(timer)
            console.warn('倒计时结束')
          }
          countdown.value--
        }, 1000)
      })
      onUnmounted(() => {
        if (timer) {
          clearInterval(timer)
        }
      })

      return () => {
        const diff = currentDiff.value

        return h('div', { class: 'base-theme-countdown__container' },
          diff.map((time, index) => {
            const timeText = getTimeText(time, index)
            return h('div', { class: 'base-theme-countdown-item' }, [
              h('span', { class: 'base-theme-countdown-item__num' }, time),
              h('span', { class: 'base-theme-countdown-item__text' }, timeText)
            ])
          })
        )
      }
    }
  }

  const renderToContainer = (container) => {
    if (!container || !(container instanceof Element)) {
      console.error('render函数需要有效的DOM容器')
      return () => {}
    }
    
    render(h(CountdownComponent), container)
    
    return () => {
      // 返回清理函数
      render(null, container)
    }
  }

  return {
    render: renderToContainer
  }
}
