import { ref, computed } from '@vue/reactivity'
import { initTimeline } from './timeline'

(function(Drupal, drupalSettings) {
  'use strict'

  Drupal.behaviors.timeline = {
    attach: function(context, settings) {
      const timelines = settings.timeline
      initTimeline(timelines)
    }
  }

})(Drupal, drupalSettings)