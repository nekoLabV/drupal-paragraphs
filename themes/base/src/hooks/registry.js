import { createApp } from 'vue'

const components = {
  'countdown': () => import('./components/countdown.vue'),
  'timeline': () => import('./components/timeline.vue'),
  'textWithEmbed': () => import('./components/TextWithEmbed.vue'),
}

export const mountComponent = async (componentName, element, props) => {
  if (!components[componentName]) {
    console.error(`组件 ${componentName} 未注册`)
    return
  }
  
  const component = await components[componentName]()
  const app = createApp(component.default || component, props)
  app.mount(element)
  
  return app
}
