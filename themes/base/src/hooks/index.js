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
    attach: function(context, settings) {
      if (context === document) {
        window.dispatchEvent(customEvent('textWithEmbed:load', settings.textWithEmbed))
      }
    }
  }

  Drupal.behaviors.swiper = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('swiper:load'))
    }
  }

})(Drupal, drupalSettings)
