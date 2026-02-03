import { createApp, h } from 'vue'
import { inlineHtml } from '@/directives'

const components = {
  'swiper': () => import('./common/Swiper.vue'),
  'countdown': () => import('./Countdown.vue'),
  'image': () => import('./Image.vue'),
  'timeline': () => import('./Timeline.vue'),
  'textWithEmbed': () => import('./TextWithEmbed.vue'),
}

export const mountComponent = async (componentName, element, props = {}, slots = {}) => {
  if (!components[componentName]) {
    console.error(`组件 ${componentName} 未注册`)
    return
  }

  const component = await components[componentName]()

  // 插槽
  const wrapper = {
    render() {
      const slotContent = {
        default: () => h('div', {
          innerHTML: slots
        })
      }
        
      return h(component.default || component, props, slotContent)
    }
  }

  const app = createApp(wrapper)

  // 自定义指令
  app.directive('inline-html', inlineHtml)

  app.mount(element)
  
  return app
}
