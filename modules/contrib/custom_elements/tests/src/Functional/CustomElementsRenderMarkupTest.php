<?php

namespace Drupal\Tests\custom_elements\Functional;

use Drupal\Core\Config\FileStorage;
use Drupal\Tests\BrowserTestBase;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;

/**
 * Test rendering custom elements into markup.
 *
 * @group custom_elements
 *
 * @todo convert these into kernel tests. The only(?) reason this isn't yet,
 *   is the filesystem copy has not been dealt with.
 */
class CustomElementsRenderMarkupTest extends BrowserTestBase {

  use CustomElementGeneratorTrait;
  use TestHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    // Also needs custom_elements_thunder; see below.
    'custom_elements_test_paragraphs',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The image used for testing.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * Entity CE display storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ceDisplayStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // custom_elements_thunder config depends on custom_elements_test_paragraphs
    // config, but we can't declare it as a dependency; setting it in $modules
    // goes wrong (because wrong install order). Install it here.
    // @todo install full module when all dependencies are D11 compatible
    //   (module name: custom_elements_thunder) and remove the line below.
    // $this->container->get('module_installer')->install(['ce_thunder'], TRUE);
    $this->importPartialThunderConfig(['twitter', 'gallery']);

    $this->ceDisplayStorage = \Drupal::service('entity_type.manager')->getStorage('entity_ce_display');

    $this->node = Node::create([
      'type' => 'article',
      'title' => 'test',
    ]);
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $this->image->save();

    $ce_display = $this->getCustomElementGenerator()->getEntityCeDisplay('node', 'article', 'default');
    $ce_display->setComponent('paragraphs', [
      'field_name' => 'field_paragraphs',
      'formatter' => 'auto',
      'is_slot' => TRUE,
    ]);
    $ce_display->setComponent('teaser-media', [
      'field_name' => 'field_teaser_media',
      'formatter' => 'auto',
      'is_slot' => TRUE,
    ]);
    $ce_display->save();

    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display */
    $ce_display = $this->getCustomElementGenerator()->getEntityCeDisplay('paragraph', 'text', 'default');
    $ce_display
      ->setCustomElementName('paragraph-text')
      ->setComponent('default', [
        'field_name' => 'field_text',
        'formatter' => 'auto',
        'is_slot' => TRUE,
      ])
      ->setComponent('title', [
        'field_name' => 'field_title',
        'formatter' => 'auto',
        'is_slot' => FALSE,
      ])
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
    $source = new FileStorage($config_dir);
    // Get config names that do not contain any of $exclude_bundles.
    $config_names = array_filter(
      $source->listAll(),
      function ($config_name) use ($exclude_bundles) {
        // Return boolean indicating config name contains any of $exclude.
        return !array_filter(
          $exclude_bundles,
          function ($bundle) use ($config_name) {
            return str_contains($config_name, $bundle);
          }
        );
      }
    );

    foreach ($config_names as $config_name) {
      // Use the entity API to create config entities.
      $entity_type_id = \Drupal::service('config.manager')->getEntityTypeIdByName($config_name);
      if ($entity_type_id) {
        \Drupal::entityTypeManager()
          ->getStorage($entity_type_id)
          ->create($source->read($config_name))
          ->save();
      }
      else {
        \Drupal::service('config.storage')->write($config_name, $source->read($config_name));
      }
    }
  }

  /**
   * @covers \Drupal\custom_elements\Processor\DefaultContentEntityProcessor
   */
  public function testNodeRendering() {
    $ce_display = $this->getCustomElementGenerator()->getEntityCeDisplay('media', 'image', 'default');
    $ce_display->setComponent('image', [
      'field_name' => 'field_image',
      'formatter' => 'auto',
      'is_slot' => TRUE,
    ]);
    $ce_display->removeComponent('thumbnail');
    $ce_display->save();

    $paragraph = Paragraph::create([
      'title' => 'Title',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some example text',
      ],
    ]);
    $this->node->field_paragraphs = [
      $paragraph,
    ];

    // Create unpublished media.
    $media = Media::create([
      'bundle' => 'image',
      'status' => FALSE,
      'field_image' => [
        'target_id' => $this->image->id(),
      ],
    ]);
    $media->save();
    $this->node->field_teaser_media = $media;
    $this->node->field_teaser_text = 'Teaser text';

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    // The results should not contain unpublished media.
    $expected_markup = <<<EOF
<node-article created="{$this->node->created->value}" title="test" uid="0">
  <paragraph-text slot="paragraphs">
    <p>Some example text</p>
  </paragraph-text>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);

    // Test behavior with auto-processing being forced (BC-mode).
    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display */
    $ce_display = $this->ceDisplayStorage->load('node.article.default');
    $ce_display->setForceAutoProcessing(TRUE);
    $ce_display->save();
    // @todo Remove this (override of saved config) along with the dependency
    //   of custom_elements_test_paragraphs on custom_elements_thunder.
    $ce_display = $this->ceDisplayStorage->load('media.image.full');
    $ce_display->setForceAutoProcessing(TRUE);
    $ce_display->save();

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<node-article uid="0" title="test" created="{$this->node->created->value}">
  <paragraph-text slot="paragraphs">
    <p>Some example text</p>
  </paragraph-text>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);

    // Create published media.
    $media2 = Media::create([
      'bundle' => 'image',
      'field_image' => [
        'target_id' => $this->image->id(),
      ],
    ]);
    $media2->save();

    $this->node->field_teaser_media->setValue($media2);
    $this->node->field_paragraphs->setValue([]);

    // First test with auto-processing.
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    // The results should contain published media.
    $expected_markup = <<<EOF
<node-article uid="0" title="test" created="{$this->node->created->value}">
  <field-image slot="teaser-media">
    <div>
      <img loading="lazy" src="{$this->image->uri->url}" width="88" height="100" />
    </div>
  </field-image>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);

    $ce_display = $this->ceDisplayStorage->load('node.article.default');
    $ce_display->setForceAutoProcessing(FALSE);
    $ce_display->save();

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<node-article created="{$this->node->created->value}" title="test" uid="0">
  <field-image slot="teaser-media">
    <div>
      <img loading="lazy" src="{$this->image->uri->url}" width="88" height="100" />
    </div>
  </field-image>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);
  }

}
