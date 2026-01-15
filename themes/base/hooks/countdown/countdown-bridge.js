import { createApp } from 'vue'
import { Countdown } from './countdown.jsx'

// 导出供 Twig 使用的函数
export const createCountdownApp = (element, options) => {
  if (!element) {
    console.error('需要提供有效的 DOM 元素')
    return null
  }
  
  const app = createApp(Countdown, options)
  const vm = app.mount(element)
  
  return {
    unmount: () => app.unmount(),
    instance: vm
  }
}
