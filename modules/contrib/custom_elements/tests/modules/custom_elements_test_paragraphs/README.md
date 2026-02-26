This module installs configuration for various paragraph types and all
dependent config (fields and node types), to test Custom Elements handling.

It tries to track the paragraph structures as provided by the Thunder
distribution. There is no dependency on Thuhder; the configuration objects are
basically copies, which means they could go out of date.

Various paragraph types have been removed over time:
- video (the video processor shipped with custom_elements_thunder before v3
         was only compatible with non-core media fields / video_embed_field
         module, which Thunder has not supported since Drupal 9. No replacement
         processor or configuration to test Thunder's Drupal 9+ video paragraph
         has been provided in the custom_elements_thunder module, so far.)
- gallery (dependent on media_entity_slideshow module)
- twitter (dependent on media_entity_twitter module)

The mentioned media_* dependent modules had no D11 compatible release at the
time custom_eleents 3.0.1 was released, so the dependent config was removed
from this module, and the tests commented out. When these modules get a D11
compatible release, the configuration can be reinstated from v3.0.0 and the
commented tests can be made to work again, so that the custom elements
configuration objects provided in the custom_elements_thunder module can be
tested again.

Some other Thunder paragraph/media types are not tested and contain no
configuration in the module, even though their media_entity_* modules were
initially added as dependencies (and now removed):
- instagram
- pinterest

### Dependency on 'media type' modules

Media types and their fields (configuration objects), and consequently the
entityreference fields in the respective paragraph types, have a config
dependency on the modules providing a media source. E.g. the following
config objects have a dependency on the media_entity_twitter:
- field.field.media.twitter.field_* -> media.type.twitter
- field.field.paragraph.twitter.field_media -> media.type.twitter
  (also depends on paragraph.type.twitter but that has no relevant dependencies)
- media.type.twitter -> media_entity_twitter module

The custom_elements module currently does not use any code from these
dependency modules, for actually rendering these entities. That would only
happen through any CE field formatters / processors that contain
- code specific to media entities (likely doing something with the media
  source provided by these dependency modules, which is tied to media types).
  This would then likely depend on which / how (meta)data is added to those
  media types, by our tests.
- custom code calling into these specific modules.

The only other exposure which the custom_elements module can have to these
media type modules, is while instantiating a media entity + its fields, before
passing them into CE field formatters / processors. It is quite unlikely that
these modules have any effect, unless they provide their own field type that is
used in the media entities. (video_embed_field had its own field type, but the
instagram/pinterest/twitter modules do not.)

Therefore, unless / until we start testing against any media-specific metadata
structures:
- It is not very important to test all instagram/pinterest/twitter media types,
  which have equal / similar field structures.
- It would be possible to re-construct e.g. a fake twitter media type (with
  some generic 'media source') to test its URL output without any dependency
  on the specific module / its supported Drupal versions.
- These media specific tests don't seem very important, if/when we have good
  kernel tests that test the field types involved (like URL / entityreference
  fields).
