import './customElements'
import { mountComponent } from './registry'
import { modulePropsMap } from '@/jsUtils/paragraphPropsProcessor'
import { toKebabCase } from '@/jsUtils/string'

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

  Drupal.behaviors.content_block = {
    attach: function(context, settings) {
      const blocks = context.querySelectorAll('paragraph-block')
      blocks.forEach(el => {
        const id = el.getAttribute('data-id')
        if (settings.contentBlock && settings.contentBlock[id]) {
          const data = settings.contentBlock[id]
          el.theme = toKebabCase(data?.theme)
          el.blockAlign = data?.blockAlign
          el.colWidth = data?.colWidth
          el.paddingTop = data?.paddingTop
          el.paddingBottom = data?.paddingBottom
          el.marginTop = data?.marginTop
          el.marginBottom = data?.marginBottom
          el.backgroundImageSrc = data?.backgroundImageSrc?.url
          el.backgroundImageMobileSrc = data?.backgroundImageMobileSrc?.url
        }
      })
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
