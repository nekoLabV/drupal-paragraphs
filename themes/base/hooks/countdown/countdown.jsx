import { defineComponent, ref, computed, onMounted, onUnmounted } from 'vue'

export const Countdown = defineComponent({
  name: 'Countdown',
  
  props: {
    timestamp: {
      type: [Number, String, Date],
      required: true
    },
    hintText: {
      type: String,
      default: 'Day|Hour|Min|Sec'
    }
  },
  
  setup(props) {
    const rootClassName = 'base-theme-countdown'
    const countdown = ref(0)
    const timeTexts = ref([])

    const getDiff = (countdown) => {
      let [d, h, m, s] = [0, 0, 0, 0]
      if (countdown > 0) {
        d = Math.floor(countdown / 60 / 60 / 24)
        h = Math.floor((countdown / 60 / 60) % 24)
        m = Math.floor((countdown / 60) % 60)
        s = Math.floor(countdown % 60)
      }
      const padZero = num => String(num).padStart(2, '0')
      return [d, h, m, s].map(padZero)
    }

    const init = () => {
      const endTime = new Date(props.timestamp).getTime()
      const startTime = Date.now()
      countdown.value = Math.max(0, Math.floor((endTime - startTime) / 1000))
      
      timeTexts.value = props.hintText.split('|').map(str => str.trim())
    }

    const currentDiff = computed(() => getDiff(countdown.value))

    init()

    onMounted(() => {
      const timer = setInterval(() => {
        if (countdown.value <= 0) {
          clearInterval(timer)
        } else {
          countdown.value--
        }
      }, 1000)
      
      onUnmounted(() => {
        clearInterval(timer)
      })
    })

    // JSX 渲染函数
    return () => {
      if (countdown.value <= 0) {
        return null
      }

      const diff = currentDiff.value
      const timeUnits = timeTexts.value

      return (
        <div class={`${rootClassName}`}>
          <div class={`${rootClassName}__container`}>
            {diff.map((time, index) => (
              <div key={index} class={`${rootClassName}-item`}>
                <span class={`${rootClassName}-item__num`}>{time}</span>
                <span class={`${rootClassName}-item__text`}>{timeUnits[index]}</span>
              </div>
            ))}
          </div>
        </div>
      )
    }
  }
})
