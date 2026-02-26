## Submodule status

Version 8.x-2.x of this module contained processors (implementations of
CustomElementProcessorInterface) which all applied to a certain paragraph type.

Version 3.x ships without any of these processors. it should be possible to
replace those with Custom Elements Displays that are included as configuration.

Given the very low install base of this module, there's no
seamless/supported upgrade path.

## Status of support

No Custom Elements Displays (configuration) are not provided for the 'instagram'
and 'pinterest' paragraphs and media types. These were simply forgotten, due
to the fact that the latest 8.x-2.x module lacked automated tests for them.
They are very similar to the 'twitter' paragraphs / media type:
- the paragraph types contain a single entityreference field 'field_media';
- the media types contain a 'field_url' field, plus 'instagram' contains two
  extra fields 'field_author' and 'field_content' (just like twitter).
So it would be quite simple to add them, especially since the shipped Custom
Elements Display for the 'twitter' media type only has the URL field enabled.

The video processor from version 8.x-2.x was still dependent on the
[Video Embed field](https://www.drupal.org/project/video_embed_field) module,
which has not been used by Thunder's video media type since version
8.x-3.x-beta1 / 3.3.0-beta1 (targeted at Drupal 9).

Support for this module, and therefore the 'old' video paragraph / media type,
has been dropped as of custom_elements v3.0.0, because video_embed_field is not
compatible with Drupal 11. All 3.x-beta versions of the custom_elements module
contained configuration for the 'old' video paragraph, including a rewritten
field formatter, that still works with the video_embed_field module.

## Upgrading this module from version 8.x-2.x

The upgrade is NOT designed to be seamless: output may change when you start
using configuration. If you want to investigate all details beforehand: check
the intended new output in tests/src/Kernel/ThunderParagraphRenderMarkupTest.php
vs. the old output in tests/src/Functional/CustomElementsRenderMarkupTest.php
in version 8.x-2.x.

It is expected that a slight change in the output is not a problem, because
this is easily changed accordingly, in the Vue frontend components - and
things will work again.

### Reinstating old processors (WARNING: extended processors break with 3.x)

If your own codebase is extending the processors that were shipped with version
8.x-2.x, you need the old processors (or need to update your code before
upgrading to version 3.x), otherwise your code will break.

This module made a decision to stop supporting those processors immediately
with the 3.x release. But a module to install alongside version 3.x,
containing the old processors plus a README document outlining update steps, is
available at https://www.drupal.org/i/3443366.

### Upgrading without the old processors

* Check if you're still using video_embed_field. (Likely yes, because this
  module still has that dependency. If not: remove
  config/install/custom_elements.entity_ce_display.media.video.default.yml.)

* Import the configuration:
  `drush config:import --partial --source=MODULE_ROOT/modules/custom_elements_thunder/config-install`

* See what the new output looks like.

If you want to experiment / roll some things back: enable custom_elements_ui and
* Configure your own formatter at e.g. admin/structure/media/manage/video/ce-display
  or admin/structure/paragraphs_type/video/ce-display.
* Enable custom display settings for non-default view modes, if needed.
* If you want to switch back to using a custom Processor that was used in
  version 8.x-2.x, instead of this display: turn on the "Automatic processing"
  option.
