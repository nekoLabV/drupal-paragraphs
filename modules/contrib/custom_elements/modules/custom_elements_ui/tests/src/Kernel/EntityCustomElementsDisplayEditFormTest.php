<?php

namespace Drupal\Tests\custom_elements_ui\Kernel;

use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\custom_elements_ui\Form\EntityCustomElementsDisplayEditForm;
use Drupal\Core\Form\FormState;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\user\Entity\User;

/**
 * Tests the EntityCustomElementsDisplayEditForm.
 *
 * @group custom_elements_ui
 */
class EntityCustomElementsDisplayEditFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field',
    'text',
    'filter',
    'user',
    'node',
    'custom_elements',
    'custom_elements_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The form under test.
   *
   * @var \Drupal\custom_elements_ui\Form\EntityCustomElementsDisplayEditForm
   */
  protected EntityCustomElementsDisplayEditForm $form;

  /**
   * A test custom elements display.
   *
   * @var \Drupal\custom_elements\Entity\EntityCeDisplay
   */
  protected EntityCeDisplay $ceDisplay;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('entity_ce_display');
    $this->installEntitySchema('entity_view_display');
    $this->installEntitySchema('entity_view_mode');
    $this->installConfig(['field', 'node', 'system', 'custom_elements', 'custom_elements_ui']);

    // Create a test user to avoid entity reference issues.
    User::create([
      'uid' => 1,
      'name' => 'test-user',
      'mail' => 'test@example.com',
      'status' => 1,
    ])->save();

    // Create a content type.
    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $node_type->save();

    // Create a test field.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_test_text',
      'entity_type' => 'node',
      'type' => 'text',
    ]);
    $field_storage->save();

    $field_config = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => 'Test Text Field',
    ]);
    $field_config->save();

    // Create test view modes.
    EntityViewMode::create([
      'id' => 'node.test',
      'targetEntityType' => 'node',
      'label' => 'Test',
    ])->save();

    // Create a custom elements display.
    // It would be good to test other content as well: blocks, media, etc.
    $this->ceDisplay = EntityCeDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'test',
      'id' => 'node.article.test',
      'status' => TRUE,
    ]);
    $this->ceDisplay->setComponent('field_test_text', [
      'type' => 'auto',
      'field_name' => 'field_test_text',
      'settings' => [],
    ]);
    $this->ceDisplay->save();

    // Create the form instance.
    $this->form = EntityCustomElementsDisplayEditForm::create($this->container);
    $this->form->setEntity($this->ceDisplay);

    // Set entity type manager explicitly if needed.
    // Fix Error: Call to a member function getStorage() on null.
    $reflection = new \ReflectionClass($this->form);
    if ($reflection->hasProperty('entityTypeManager')) {
      $entityTypeManagerProperty = $reflection->getProperty('entityTypeManager');
      $entityTypeManagerProperty->setAccessible(TRUE);
      if ($entityTypeManagerProperty->getValue($this->form) === NULL) {
        $entityTypeManagerProperty->setValue($this->form, $this->container->get('entity_type.manager'));
      }
    }
  }

  /**
   * Tests the buildPreview method.
   */
  public function testBuildPreview() {
    $form_state = new FormState();
    $form = $this->form->form([], $form_state);
    // Test form creation.
    $this->assertIsArray($form);
    $this->assertArrayHasKey('custom_element_name', $form);
    $this->assertArrayHasKey('preview', $form);
    $this->assertEquals('details', $form['preview']['#type']);
    $this->assertEquals('Preview', $form['preview']['#title']);

    // Verify preview controls are present.
    $this->assertArrayHasKey('controls', $form['preview']);
    $this->assertArrayHasKey('preview_provider', $form['preview']['controls']);
    $this->assertArrayHasKey('preview_refresh', $form['preview']['controls']);

    // Initially, preview shows empty message (button not clicked yet).
    $preview = $form['preview']['content'];
    $this->assertIsArray($preview);
    $this->assertArrayHasKey('message', $preview);

    // Simulate clicking the Update button and rebuild the form.
    $form_state->setTriggeringElement($form['preview']['controls']['preview_refresh']);
    $form_state->setValue(['preview', 'controls', 'preview_provider'], 'markup');
    $form = $this->form->form([], $form_state);

    // Now the preview should contain content.
    $preview = $form['preview']['content'];
    $this->assertIsArray($preview);
    $this->assertArrayHasKey('#type', $preview);
    $this->assertEquals('container', $preview['#type']);
    $this->assertArrayHasKey('#prefix', $preview);
    $this->assertArrayHasKey('#plain_text', $preview['content']['content']);
    $this->assertArrayHasKey('#suffix', $preview);
    // Decode the var_export string to get the actual custom element HTML.
    $custom_element_html = html_entity_decode($preview['content']['content']['#plain_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Verify the custom element structure.
    $this->assertStringContainsString('<node-article-test', $custom_element_html);
    $this->assertStringContainsString('</node-article-test>', $custom_element_html);
    // Verify custom element attributes are present.
    $this->assertStringContainsString('created=', $custom_element_html);
    $this->assertStringContainsString('title=', $custom_element_html);
    $this->assertStringContainsString('uid="1"', $custom_element_html);
    // Verify it's properly closed (self-closing or with closing tag).
    $this->assertTrue(
      strpos($custom_element_html, '</node-article-test>') !== FALSE ||
      strpos($custom_element_html, '/>') !== FALSE,
      'Custom element should be properly closed'
    );
  }

}
