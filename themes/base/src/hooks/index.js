import { mountComponent } from './registry'

if (typeof window !== 'undefined') {
  window.vueMountComponent = mountComponent
}

setTimeout(() => {
  window.dispatchEvent(new Event('baseThemeHooks:load'))
}, 300)
