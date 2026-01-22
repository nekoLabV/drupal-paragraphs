import * as countdown from './countdown'
import * as timeline from './timeline'
import * as textWithEmbed from './textWithEmbed'

const baseThemeHooks = {
  countdown,
  timeline,
  textWithEmbed
}

if (typeof window !== 'undefined') {
  window.baseThemeHooks = baseThemeHooks
}

setTimeout(() => {
  window.dispatchEvent(new Event('baseThemeHooks:load'))
}, 300)
