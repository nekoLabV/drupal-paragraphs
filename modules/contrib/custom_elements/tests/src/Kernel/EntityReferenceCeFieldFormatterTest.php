<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Tests rendering of entityreference fields.
 *
 * @group custom_elements
 */
class EntityReferenceCeFieldFormatterTest extends KernelTestBase {

  use CustomElementGeneratorTrait;
  use UserCreationTrait;
  use TestHelperTrait;

  /**
   * {@inheritdoc}
   *
   * @var bool
   * @todo Fix config schema for CE-displays and re-enable.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to install.
   *
   * @var string[]
   */
  protected static $modules = [
    'custom_elements',
    'user',
    'field',
    'file',
    'paragraphs',
    'entity_reference_revisions',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installConfig(['custom_elements']);

    // These tests expect legacy format.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();
  }

  /**
   * Creates user reference field, CE display and user(s).
   *
   * @param bool $multi_value
   *   Whether the field should be multi-value (cardinality >1).
   * @param bool $multiple_values
   *   Whether multiple users should be referenced.
   */
  protected function setupUser(bool $multi_value = FALSE, bool $multiple_values = FALSE): User {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'ref_user',
      'type' => 'entity_reference',
      'cardinality' => $multi_value ? FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED : 1,
      'entity_type' => 'user',
      'settings' => [
        'target_type' => 'user',
      ],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'ref_user',
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => 'Referenced user',
      'settings' => [
        'handler' => 'default',
      ],
    ]);
    $field->save();

    // Create CE config with is_slot=TRUE for the reference fields. Callers can
    // change it.
    EntityCeDisplay::create([
      'targetEntityType' => 'user',
      'customElementName' => 'user',
      'bundle' => 'user',
      'mode' => 'default',
    ])
      ->setComponent('name', [
        'field_name' => 'name',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->setComponent('ref-user', [
        'field_name' => 'ref_user',
        'is_slot' => TRUE,
        'formatter' => 'entity_ce_render',
        'configuration' => [
          'mode' => 'full',
        ],
      ])
      ->save();

    $ref_user = $this->createUser([], 'target');
    if ($multi_value && $multiple_values) {
      $ref_user2 = $this->createUser([], 'target2');
      $value = [$ref_user, $ref_user2];
    }
    else {
      $value = [$ref_user];
    }
    $referrer = $this->createUser([], 'referrer', FALSE, ['ref_user' => $value]);

    return $referrer;
  }

  /**
   * Changes configuration of the 'ref_user' display component.
   *
   * @param bool $is_slot
   *   Value for is_slot property.
   * @param array $config
   *   Other configuration values.
   */
  protected function changeReferenceDisplayComponent(bool $is_slot, array $config = []): void {
    EntityCeDisplay::load('user.user.default')
      ->setComponent('ref-user', [
        'field_name' => 'ref_user',
        'is_slot' => $is_slot,
        'formatter' => 'entity_ce_render',
        'configuration' => $config + ['mode' => 'full'],
      ])
      ->save();
  }

  /**
   * Creates paragraph type, entity_reference_revisions field, CE display.
   */
  protected function setupParagraph(): User {
    // Create paragraph type.
    ParagraphsType::create([
      'id' => 'test_paragraph',
      'label' => 'Test Paragraph',
    ])->save();

    // Create entity_reference_revisions field storage.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'ref_paragraph',
      'type' => 'entity_reference_revisions',
      'cardinality' => 1,
      'entity_type' => 'user',
      'settings' => [
        'target_type' => 'paragraph',
      ],
    ]);
    $field_storage->save();

    // Create field instance.
    $field = FieldConfig::create([
      'field_name' => 'ref_paragraph',
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => 'Referenced paragraph',
      'settings' => [
        'handler' => 'default:paragraph',
        'handler_settings' => [
          'target_bundles' => [
            'test_paragraph' => 'test_paragraph',
          ],
        ],
      ],
    ]);
    $field->save();

    // Create CE display for paragraph.
    EntityCeDisplay::create([
      'targetEntityType' => 'paragraph',
      'customElementName' => 'test-paragraph',
      'bundle' => 'test_paragraph',
      'mode' => 'default',
    ])
      ->setComponent('id', [
        'field_name' => 'id',
        'is_slot' => FALSE,
        'formatter' => 'auto',
      ])
      ->save();

    // Create CE config for user with paragraph reference field.
    EntityCeDisplay::create([
      'targetEntityType' => 'user',
      'customElementName' => 'user',
      'bundle' => 'user',
      'mode' => 'default',
    ])
      ->setComponent('name', [
        'field_name' => 'name',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->setComponent('ref-paragraph', [
        'field_name' => 'ref_paragraph',
        'is_slot' => TRUE,
        'formatter' => 'entity_ce_render',
        'configuration' => [
          'mode' => 'full',
        ],
      ])
      ->save();

    // Create paragraph.
    $paragraph = Paragraph::create([
      'type' => 'test_paragraph',
    ]);
    $paragraph->save();

    // Create user with paragraph reference.
    $user = $this->createUser([], 'paragraph_user', FALSE, ['ref_paragraph' => $paragraph]);
    return $user;
  }

  /**
   * Tests output for an entityreference field with cardinality 1.
   */
  public function testSingleCardinality() {
    // $referrer:
    // - name="referrer"
    // - ref_user field = user with name="target"
    // CE display:
    // - name = username (flattened)
    // - ref-user = entityref-formatter: view mode = "full"
    $referrer = $this->setupUser();

    // Slot.
    // @todo fix 'drupal-' prefix
    $expected_markup = <<<EOF
<user name="referrer">
  <user name="target" slot="ref-user"></user>
</user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // No slot. (Change ref-user config to is_slot = FALSE).
    // Use hide_element = FALSE to include element key in output.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => FALSE]);
    $data = htmlspecialchars(json_encode([
      'element' => 'user',
      'name' => 'target',
    ]));
    $expected_markup = <<<EOF
<user name="referrer" ref-user="$data"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // Flatten: all referenced fields are added in the current tag, prefixed by
    // the configured element name ("ref-user").
    $this->changeReferenceDisplayComponent(FALSE, ['flatten' => TRUE]);
    $expected_markup = <<<EOF
<user name="referrer" ref-user-name="target"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
    // is_slot is ignored for 'flatten'.
    $this->changeReferenceDisplayComponent(TRUE, ['flatten' => TRUE]);
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // Rename component.
    EntityCeDisplay::load('user.user.default')
      ->removeComponent('name')
      ->setComponent('user-name', [
        'field_name' => 'name',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->save();
    $expected_markup = <<<EOF
<user ref-user-user-name="target" user-name="referrer"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests output for a field with cardinality >1, with 1 value.
   *
   * Differences in the elements' structure are based on cardinality, not the
   * number of referenced entities.
   */
  public function testMultiValueFieldSingleValue() {
    $referrer = $this->setupUser(TRUE);

    // Slot: no difference with single-value field.
    $expected_markup = <<<EOF
<user name="referrer">
  <user name="target" slot="ref-user"></user>
</user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // No slot: array of elements, instead of a single element.
    // Use hide_element = FALSE to include element key in output.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => FALSE]);
    $data = htmlspecialchars(json_encode([
      ['element' => 'user', 'name' => 'target'],
    ]));
    $expected_markup = <<<EOF
<user name="referrer" ref-user="$data"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // 'Flatten' disregards cardinality, acts as single-value.
    $this->changeReferenceDisplayComponent(FALSE, ['flatten' => TRUE]);
    $expected_markup = <<<EOF
<user name="referrer" ref-user-name="target"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests output for a field with cardinality >1, with multiple values.
   *
   * Differences in the elements' structure are based on cardinality, not the
   * number of referenced entities.
   */
  public function testMultiValueFieldMultiValue() {
    $referrer = $this->setupUser(TRUE, TRUE);

    // Slot.
    $expected_markup = <<<EOF
<user name="referrer">
  <user name="target" slot="ref-user"></user>
  <user name="target2" slot="ref-user"></user>
</user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // No slot: array of elements, instead of a single element.
    // Use hide_element = FALSE to include element key in output.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => FALSE]);
    $data = htmlspecialchars(json_encode([
      ['element' => 'user', 'name' => 'target'],
      ['element' => 'user', 'name' => 'target2'],
    ]));
    $expected_markup = <<<EOF
<user name="referrer" ref-user="$data"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);

    // 'Flatten' disregards cardinality, acts as single-value.
    $this->changeReferenceDisplayComponent(FALSE, ['flatten' => TRUE]);
    $expected_markup = <<<EOF
<user name="referrer" ref-user-name="target"></user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests output for an entity_reference_revisions field with paragraph.
   */
  public function testParagraphReferenceRevisions() {
    $user = $this->setupParagraph();

    // Get the paragraph ID for assertions.
    $paragraph_id = $user->get('ref_paragraph')->entity->id();

    // Test slot rendering - paragraph should render as child element with slot.
    $expected_markup = <<<EOF
<user name="paragraph_user">
  <test-paragraph id="$paragraph_id" slot="ref-paragraph"></test-paragraph>
</user>
EOF;
    $custom_element = $this->getCustomElementGenerator()->generate($user, 'default');
    $tested_markup = $this->renderCustomElement($custom_element);
    $this->assertMarkupEquals($expected_markup, $tested_markup);
  }

  /**
   * Tests that prop output uses implicit format even when explicit is enabled.
   *
   * When entity references are output as props (not slots), they should use
   * implicit/flat format without the props wrapper, regardless of the global
   * json_format setting.
   */
  public function testPropOutputUsesImplicitFormat() {
    // Switch to explicit format globally.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'explicit')
      ->save();

    $referrer = $this->setupUser();

    // Configure as prop (not slot), with hide_element = TRUE (new default).
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => TRUE]);

    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $normalized = $custom_element->toArray();

    // The ref-user prop should use implicit format (flat, no props wrapper).
    $this->assertArrayHasKey('refUser', $normalized);
    $ref_user_value = $normalized['refUser'];

    // Should NOT have 'props' wrapper (implicit format).
    $this->assertArrayNotHasKey('props', $ref_user_value);
    // Should have the 'name' attribute directly.
    $this->assertArrayHasKey('name', $ref_user_value);
    $this->assertEquals('target', $ref_user_value['name']);
    // Element key should be hidden when hide_element is TRUE.
    $this->assertArrayNotHasKey('element', $ref_user_value);
  }

  /**
   * Tests multi-value prop output uses implicit format.
   */
  public function testMultiValuePropOutputUsesImplicitFormat() {
    // Switch to explicit format globally.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'explicit')
      ->save();

    $referrer = $this->setupUser(TRUE, TRUE);

    // Configure as prop (not slot), with hide_element = TRUE.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => TRUE]);

    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $normalized = $custom_element->toArray();

    // The ref-user prop should be an array with implicit format elements.
    $this->assertArrayHasKey('refUser', $normalized);
    $ref_user_value = $normalized['refUser'];

    $this->assertIsArray($ref_user_value);
    $this->assertCount(2, $ref_user_value);

    // Each element should use implicit format (no props wrapper).
    foreach ($ref_user_value as $item) {
      $this->assertArrayNotHasKey('props', $item);
      $this->assertArrayHasKey('name', $item);
      // Element key should be hidden when hide_element is TRUE.
      $this->assertArrayNotHasKey('element', $item);
    }
  }

  /**
   * Tests hide_element configuration option.
   */
  public function testHideElementConfiguration() {
    // Switch to explicit format globally.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'explicit')
      ->save();

    $referrer = $this->setupUser();

    // Configure as prop with hide_element = FALSE.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => FALSE]);

    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $normalized = $custom_element->toArray();

    $ref_user_value = $normalized['refUser'];
    // Element key should be present when hide_element is FALSE.
    $this->assertArrayHasKey('element', $ref_user_value);
    $this->assertEquals('user', $ref_user_value['element']);

    // Configure as prop with hide_element = TRUE.
    $this->changeReferenceDisplayComponent(FALSE, ['hide_element' => TRUE]);

    $custom_element = $this->getCustomElementGenerator()->generate($referrer, 'full');
    $normalized = $custom_element->toArray();

    $ref_user_value = $normalized['refUser'];
    // Element key should be hidden when hide_element is TRUE.
    $this->assertArrayNotHasKey('element', $ref_user_value);
  }

}
