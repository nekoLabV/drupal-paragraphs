import { mountComponent } from './registry'

if (typeof window !== 'undefined') {
  window.vueMountComponent = mountComponent
}

const customEvent = (name, data) => {
  if (!name) {
    throw new Error('customEvent: name is required.')
  }
  return new CustomEvent(name, {
    detail: data
  })
}

(function(Drupal, _) {
  'use strict'

  Drupal.behaviors.countdown = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('countdown:load', settings.countdown))
    }
  }

  Drupal.behaviors.timeline = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('timeline:load', settings.timeline))
    }
  }

  Drupal.behaviors.textWithEmbed = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('textWithEmbed:load', settings.textWithEmbed))
    }
  }

  Drupal.behaviors.swiper = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('swiper:load', settings.swiper))
    }
  }

  Drupal.behaviors.image = {
    attach: function(context, settings) {
      // window.dispatchEvent(customEvent('image:load', settings.image));

      const elements = context.querySelectorAll('[data-vue-compontent="image"]')
    
      elements.forEach(el => {
        const id = el.getAttribute('data-id')
        const data = settings.image?.[id]
        if (data && typeof mountComponent === 'function') {
          mountComponent('image', el, data)
          // el.setAttribute('data-once', 'true')
        }
      })
    }
  }

  Drupal.behaviors.images = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('images:load', settings.images))
    }
  }

})(Drupal, drupalSettings)
