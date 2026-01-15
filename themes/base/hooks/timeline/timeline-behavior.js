import { initTimeline } from './timeline'

(function(Drupal, drupalSettings) {
  'use strict'

  Drupal.behaviors.timeline = {
    attach: function(context, settings) {
      const timelines = settings.timeline
      // console.log('behaviors timelines', timelines)
      initTimeline(timelines)
    }
  }

})(Drupal, drupalSettings)