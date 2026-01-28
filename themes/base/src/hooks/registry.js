import { createApp, h } from 'vue'

const components = {
  'swiper': () => import('./components/common/Swiper.vue'),
  'countdown': () => import('./components/Countdown.vue'),
  'timeline': () => import('./components/Timeline.vue'),
  'textWithEmbed': () => import('./components/TextWithEmbed.vue'),
}

export const mountComponent = async (componentName, element, props = {}, slots = {}) => {
  if (!components[componentName]) {
    console.error(`组件 ${componentName} 未注册`)
    return
  }

  const component = await components[componentName]()

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
  app.mount(element)
  
  return app
}
