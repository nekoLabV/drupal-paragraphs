import { defineCustomElement } from 'vue'
import { mountComponent } from './registry'
import { modulePropsMap } from '@/jsUtils/paragraphPropsProcessor'
import Swiper from './common/Swiper.vue'

const SwiperElement = defineCustomElement({ ...Swiper, shadowRoot: false })
customElements.define('paragraph-swiper', SwiperElement)

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

const createComponent = (type, context, data) => {
  const elements = context.querySelectorAll(`[data-vue-component="${type}"]`)
  
  elements.forEach(el => {
    const id = el.getAttribute('data-id')
    const handle = modulePropsMap?.[type] ?? modulePropsMap.default
    const props = handle(data?.[id])
    mountComponent(type, el, props)
    el.setAttribute('data-once', 'true')
  })
}

(function(Drupal, _) {
  'use strict'

  Drupal.behaviors.countdown = {
    attach: function(context, settings) {
      createComponent('countdown', context, settings.countdown)
    }
  }

  Drupal.behaviors.timeline = {
    attach: function(context, settings) {
      createComponent('timeline', context, settings.timeline)
    }
  }

  Drupal.behaviors.textWithEmbed = {
    attach: function(context, settings) {
      createComponent('textWithEmbed', context, settings.textWithEmbed)
    }
  }

  Drupal.behaviors.swiper = {
    attach: function(context, settings) {
      const swipers = context.querySelectorAll('paragraph-swiper')
      swipers.forEach(el => {
        const id = el.getAttribute('data-id')
        if (settings.swiper && settings.swiper[id]) {
          const data = settings.swiper[id]
          el.cols = Number(data?.cols)
          el.rows = Number(data?.rows)
        }
      })
    }
  }

  Drupal.behaviors.image = {
    attach: function(context, settings) {
      createComponent('image', context, settings.image)
    }
  }

  Drupal.behaviors.images = {
    attach: function(_, settings) {
      window.dispatchEvent(customEvent('images:load', settings.images))
    }
  }

})(Drupal, drupalSettings)
