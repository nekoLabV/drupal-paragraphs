import { createApp, h } from 'vue'
import { inlineHtml } from '@/directives'

const components = {
  'button': () => import('./paragraphs/Button.vue'),
  'countdown': () => import('./paragraphs/Countdown.vue'),
  'image': () => import('./paragraphs/Image.vue'),
  'timeline': () => import('./paragraphs/Timeline.vue'),
  'textWithEmbed': () => import('./paragraphs/TextWithEmbed.vue')
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
