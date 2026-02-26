<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;
use Symfony\Component\Yaml\Parser;

/**
 * Test rendering custom elements using paragraph bundles' CE displays.
 *
 * The CE displays for these tests are defined in config/install files
 * in the custom_elements_thunder module.
 *
 * @group custom_elements
 */
class ThunderParagraphRenderMarkupTest extends KernelTestBase {

  use CustomElementGeneratorTrait;
  use TestHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'crop',
    'custom_elements',
    'custom_elements_test_paragraphs',
    'custom_elements_thunder',
    'datetime',
    'entity_reference_revisions',
    'field',
    'file',
    'filter',
    'image',
    'link',
    'media',
    // @todo uncomment these + tests when there is a D11 compatible release.
    //   Restor fields + paragraph/media types in custom_elements_test_para,
    //   re-import all CE displays in setup() and functional tests, and re-add
    //   modules to composer.json's "require-dev" section and
    //   custom_elements_test_paragraphs.info.yml.
    //   Alternatively, state clearly why these won't be tested anymore. (See
    //   custom_elements_test_paragraphs/README.md.)
    // 'media_entity_slideshow',
    // 'media_entity_twitter',
    'node',
    'paragraphs',
    'system',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // All dependencies of the custom_elements_test_paragraphs module are
    // enabled, including ones not needed by below tests. If we want to not
    // install unneeded things, we may need to split the test module.
    $this->installSchema('node', 'node_access');
    $this->installSchema('file', 'file_usage');
    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('file');
    $this->installEntitySchema('filter_format');
    $this->installEntitySchema('media_type');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node_type');
    $this->installEntitySchema('node');
    $this->installEntitySchema('paragraphs_type');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('user');
    $this->installConfig('system');
    $this->installConfig('file');
    $this->installConfig('filter');
    $this->installConfig('image');
    $this->installConfig('media');
    $this->installConfig('node');
    $this->installConfig('paragraphs');
    $this->installConfig('user');
    $this->installConfig('custom_elements_test_paragraphs');
    // @todo reinstate installing all config when we can, and remove
    //   importPartialParagraphConfig again.
    // $this->installConfig('custom_elements_thunder');
    $this->importPartialThunderConfig(['twitter', 'gallery']);

    // These tests expect legacy format.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();
  }

  /**
   * Imports custom_elements_thunder config except unsupported modules.
   *
   * Only needs to be used when the full module cannot be installed because of
   * incompatible dependency modules.
   */
  private function importPartialThunderConfig(array $exclude_bundles) {
    $config_dir = dirname(dirname(dirname(__DIR__)))
      . '/modules/custom_elements_thunder/config/install';
    // Avoid warnings when opendir does not have the permissions to open a
    // directory.
    if ($handle = opendir($config_dir)) {
      while (FALSE !== ($filename = readdir($handle))) {
        if (str_ends_with($filename, '.yml') && !array_filter(
          $exclude_bundles,
          function ($bundle) use ($filename) {
            return str_contains($filename, $bundle);
          }
        )) {
          $config_name = substr($filename, 0, strlen($filename) - 4);
          $data = file_get_contents("$config_dir/$filename");
          $parser = new Parser();
          $data = $parser->parse($data);
          $config = \Drupal::configFactory()->getEditable($config_name);
          foreach ($data as $data_key => $value) {
            $config->set($data_key, $value);
          }
          $config->save();
        }
      }
      closedir($handle);
    }
  }

  /**
   * Tests rendering of a text paragraph using custom elements configuration.
   */
  public function testTextParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'text',
      'field_title' => 'The title',
      'field_text' => [
        'value' => '<strong>Some</strong> example text',
        'format' => 'restricted_html',
      ],
    ]);

    // Installed config formats both fields as 'auto', which uses
    // DefaultFieldItemProcessor / TextFieldItemProcessor; field_text is a slot.
    // The field_text output cannot be achieved with the 'raw' formatter,
    // because that will never output (only) the '(summary_)processed' property.
    // The title can be output using 'auto', 'field:string' or 'flattened' which
    // produce the same result, as long as the title contains only 'plain' text.
    // 'raw' outputs title="{&quot;value&quot;:&quot;The title&quot;}".
    $expected_markup = <<<EOF
<paragraph-text title="The title">
  <p><strong>Some</strong> example text</p>
</paragraph-text>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests rendering of a quote paragraph using custom elements configuration.
   */
  public function testQuoteParagraph() {
    $paragraph = Paragraph::create([
      'type' => 'quote',
      'field_text' => [
        'value' => '<strong>Some</strong> example text',
        'format' => 'restricted_html',
      ],
    ]);

    $expected_markup = <<<EOF
<paragraph-quote>
  <p><strong>Some</strong> example text</p>
</paragraph-quote>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests rendering of a link paragraph using custom elements configuration.
   */
  public function testLinkParagraph($vue3_style = FALSE) {
    $paragraph = Paragraph::create([
      'type' => 'link',
      'field_link' => [
        'uri' => 'http://example.com',
        'title' => 'Example site',
      ],
    ]);

    $expected_markup = $vue3_style ?
      '<paragraph-link link="http://example.com" link-title="Example site" :link-options="[]"></paragraph-link>' :
      '<paragraph-link link="http://example.com" link-title="Example site" link-options="[]"></paragraph-link>';
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  // phpcs:disable
  // @todo reinstate when there's a D11 compatible release; see $modules.
  /**
   * Tests rendering of a Twitter paragraph using custom elements configuration.
   * /
  public function testTwitterParagraph($vue3_style = FALSE) {
    // Of note:
    // - This tests only the most simple rendering of a twitter paragraph. The
    //   media type also has a field_author and field_content, which are
    //   output by Thunder's regular entity view displays in certain view modes.
    // - This file contains no tests for instagram (same field structure as
    //   twitter) and pinterest (media type only contains field_url) paragraphs.
    $paragraph = Paragraph::create([
      'type' => 'twitter',
      'field_media' => [
        Media::create([
          'bundle' => 'twitter',
          'field_url' => 'https://twitter.com/the_real_fago/status/1189191210709049344',
        ]),
      ],
    ]);

    $expected_markup = $vue3_style ?
      '<paragraph-twitter media-src="https://twitter.com/the_real_fago/status/1189191210709049344" :media-src-options="[]"></paragraph-twitter>' :
      '<paragraph-twitter media-src="https://twitter.com/the_real_fago/status/1189191210709049344" media-src-options="[]"></paragraph-twitter>';
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }
  */
  // phpcs:enable

  /**
   * Tests rendering of an image paragraph using custom elements configuration.
   */
  public function testImageParagraph() {
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $image->save();

    $paragraph = Paragraph::create([
      'type' => 'image',
      'field_image' => [
        Media::create([
          'bundle' => 'image',
          'field_image' => [
            'target_id' => $image->id(),
          ],
          'field_copyright' => 'custom elements copyright',
          'field_description' => '<strong>Custom Elements</strong> <p>image</p> description',
          'field_source' => 'custom elements images source',
        ]),
      ],
    ]);
    $paragraph->save();

    // The image is formatted with a core formatter: 'field:image_url'.
    // 'file' also outputs width + height; see the gallery test. 'auto' wraps
    // each image in an extra <div> for multi-value fields.
    $expected_markup = <<<EOF
<paragraph-image image-caption="&lt;strong&gt;Custom Elements&lt;/strong&gt; &lt;p&gt;image&lt;/p&gt; description" image-copyright="custom elements copyright" image-src="{$image->uri->url}"></paragraph-image>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'default');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  // phpcs:disable
  // @todo reinstate when there's a D11 compatible release; see $modules.
  /**
   * Tests rendering of a gallery paragraph using custom elements configuration.
   * /
  public function testGalleryParagraph($vue3_style = FALSE) {
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $image->save();

    $media_image_1 = Media::create([
      'bundle' => 'image',
      'thumbnail' => [
        'target_id' => $image->id(),
      ],
      'field_image' => [
        'target_id' => $image->id(),
      ],
    ]);
    $media_image_2 = Media::create([
      'bundle' => 'image',
      'thumbnail' => [
        'target_id' => $image->id(),
      ],
      'field_image' => [
        'target_id' => $image->id(),
        'alt' => 'alt text',
        'title' => 'title',
      ],
      'field_copyright' => 'copyright',
      'field_description' => 'description',
      'field_source' => 'source',
    ]);
    $paragraph = Paragraph::create([
      'type' => 'gallery',
      'field_media' => [
        Media::create([
          'bundle' => 'gallery',
          'field_media_images' => [
            0 => ['entity' => $media_image_1],
            1 => ['entity' => $media_image_2],
          ],
        ]),
      ],
    ]);

    // Build one array with all image data, preferably flat; most important is
    // to have all data in separate properties somehow. At least url +
    // thumbnail-url + copyright + caption + source must be present.
    // Structure, per the exported config:
    // - Flatten the gallery paragraph's reference to the gallery media entity.
    //   (Not super important, but unwraps [type: 'gallery', sources: ARRAY]
    //   into two separate attributes "media-type" and "media-sources".)
    // - Do not flatten the gallery media type's entityreference to the image
    //   media entity, because it's multi-value.
    // - use 'file' formatter for image, to have the URL with all other
    //   properties in a flat array. ('auto' outputs inconvenient HMTL; 'raw'
    //   outputs ID instead of URL.)
    // Difference with v2 processor: empty 'alt' property is now not output at
    // all (because getValue() in the formatter returns it as NULL).
    $expected_json = htmlspecialchars(json_encode([
      [
        'element' => 'media-image-full',
        'image-url' => $image->uri->url,
        'thumbnail-url' => $image->uri->url,
      ],
      [
        'element' => 'media-image-full',
        'copyright' => 'copyright',
        'description' => '<p>description</p>
',
        'image-alt' => 'alt text',
        'image-title' => 'title',
        'image-url' => $image->uri->url,
        'thumbnail-url' => $image->uri->url,
      ],
    ]));
    $expected_markup = $vue3_style ?
    <<< EOF
<paragraph-gallery :media-sources="$expected_json"></paragraph-gallery>
EOF : <<< EOF
<paragraph-gallery media-sources="$expected_json"></paragraph-gallery>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }
  */
  // phpcs:enable
}
