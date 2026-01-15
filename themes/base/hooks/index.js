import * as countdown from './countdown'
import * as timeline from './timeline'

const baseThemeHooks = {
  countdown,
  timeline
}

if (typeof window !== 'undefined') {
  window.baseThemeHooks = baseThemeHooks
}

window.dispatchEvent(new Event('baseThemeHooks:load'))
