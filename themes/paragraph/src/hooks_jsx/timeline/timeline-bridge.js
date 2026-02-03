import { createApp, ref } from 'vue'
import { Timeline } from './timeline.jsx'

const items = ref([])
export const getTimeline = (timeline) => {
  items.value = timeline
  // console.log('getTimeline', items.value)
}

// 导出供 Twig 使用的函数
export const createTimelineApp = (element, options) => {
  if (!element) {
    console.error('需要提供有效的 DOM 元素')
    return null
  }
  
  const app = createApp(Timeline, options)
  // const app = createApp(Timeline, { ...options, items: items.value })
  const vm = app.mount(element)
  
  return {
    unmount: () => app.unmount(),
    instance: vm
  }
}
