import { getTimeline } from './timeline-bridge'

(function(Drupal, drupalSettings) {
  'use strict'

  Drupal.behaviors.timeline = {
    attach: function(context, settings) {
      const timelines = settings.timeline
      getTimeline(timelines)
    }
  }

})(Drupal, drupalSettings)
