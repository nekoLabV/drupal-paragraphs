import './customElements'
import { mountComponent } from './registry'
import { modulePropsMap } from '@/jsUtils/paragraphPropsProcessor'
import { kebabCase } from '@/jsUtils/string'
import { setElementAttr } from '@/jsUtils/ele'

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
  const eles = context.querySelectorAll(`[data-vue-component="${type}"]`)
  
  eles.forEach(el => {
    const id = el.getAttribute('data-id')
    const handle = modulePropsMap?.[type] ?? modulePropsMap.default
    const props = handle(data?.[id])
    mountComponent(type, el, props)
    el.setAttribute('data-once', 'true')
  })
}

const customElementData = (type, context, data) => {
  const eles = context.querySelectorAll(`paragraph-${kebabCase(type)}`)
  eles.forEach(el => {
    const id = el.getAttribute('data-id')
    const handle = modulePropsMap?.[type] ?? modulePropsMap.default
    if (data) {
      const props = handle(data[id])
      for (const key in props) {
        setElementAttr(el, key, props[key])
      }
    }
  })
}

(function(Drupal, _) {
  'use strict'

  Drupal.behaviors.content_block = {
    attach: function(context, settings) {
      customElementData('contentBlock', context, settings.contentBlock)
    }
  }

  Drupal.behaviors.button = {
    attach: function(context, settings) {
      createComponent('button', context, settings.button)
    }
  }

  Drupal.behaviors.buttons = {
    attach: function(context, settings) {
      customElementData('buttons', context, settings.buttons)
    }
  }

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
      customElementData('swiper', context, settings.swiper)
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
