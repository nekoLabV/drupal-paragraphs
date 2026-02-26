<?php

namespace Drupal\Tests\lupus_decoupled_webform\Functional;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Url;
use Drupal\Tests\webform\Functional\WebformBrowserTestBase;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;

/**
 * Tests for webform entity access rules.
 *
 * @group webform
 */
class CustomElementWebformTest extends WebformBrowserTestBase {

  use BaseUrlProviderTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'system',
    'user',
    'field',
    'text',
    'path_alias',
    'path',
    'rest',
    'serialization',
    'token',
    'block',
    'block_content',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_form',
    'lupus_decoupled_cors',
    'menu_link_content',
    'webform',
    'lupus_decoupled_webform',
    'test_ce_webform',
  ];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = ['test_ce_webform'];

  /**
   * Test webform path.
   *
   * @var string
   */
  protected $webformPath = 'ce-api/form/test-ce-webform';

  /**
   * Webform id.
   *
   * @var string
   */
  protected $testWebformId = 'webform_submission_test_ce_webform_add_form';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->loadWebform(static::$testWebforms[0]);
  }

  /**
   * Test webform ce api output and webform submission.
   */
  public function testCustomElementWebform(): void {
    // Test the api output.
    $response = json_decode($this->drupalGet($this->webformPath), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('Test: Custom elements webform', $response['title'] ?? []);
    $this->assertSame('Test: Custom elements webform', $response['content']['props']['title'] ?? []);
    $this->assertSame('webform', $response['content']['props']['type'] ?? []);
    $this->assertSame('post', $response['content']['props']['method'] ?? []);
    $this->assertSame($this->testWebformId, $response['content']['props']['formId'] ?? []);
    $form_content = $response['content']['slots']['default'] ?? '';
    $this->assertStringContainsString('first_name', $form_content);
    $this->assertStringContainsString('form_build_id', $form_content);
    $this->assertStringContainsString('Submit', $form_content);
    // Create the form submission.
    $form_build_id = $this->getFormBuildId($form_content);
    $this->assertNotNull($form_build_id);
    $this->submitCustomElementWebform($form_build_id);
    /** @var \Drupal\webform\WebformSubmissionInterface[] $submissions */
    $submissions = array_values(\Drupal::entityTypeManager()->getStorage('webform_submission')->loadByProperties(['webform_id' => 'test_ce_webform']));
    $this->assertCount(1, $submissions);
    /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
    $webform_submission = reset($submissions);
    $submission_data = $webform_submission->getData();
    $this->assertEquals('John', $submission_data['first_name']);
    $this->assertEquals('Doe', $submission_data['last_name']);
    $this->assertEquals('1', $submission_data['checkbox']);
  }

  /**
   * Get form_build_id needed to submit the webform.
   *
   * @param string $form_html
   *   Markup of webform.
   *
   * @return string|null
   *   Form build id.
   */
  protected function getFormBuildId(string $form_html) {
    $pattern = '/<input[^>]*name=[\\"]+form_build_id[\\"]+[^>]*value=[\\"]+([^\\"]+)[\\"]+/';
    if (preg_match($pattern, $form_html, $matches)) {
      return $matches[1];
    }
    return NULL;
  }

  /**
   * Test submitting the webform.
   *
   * @param string $formBuildId
   *   Form build id.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function submitCustomElementWebform(string $formBuildId) {
    $multipart = [];
    $multipart[] = [
      'name' => 'first_name',
      'contents' => 'John',
    ];
    $multipart[] = [
      'name' => 'last_name',
      'contents' => 'Doe',
    ];
    $multipart[] = [
      'name' => 'checkbox',
      'contents' => 1,
    ];
    $multipart[] = [
      'name' => 'form_build_id',
      'contents' => $formBuildId,
    ];
    $multipart[] = [
      'name' => 'form_id',
      'contents' => $this->testWebformId,
    ];
    $multipart[] = [
      'name' => 'op',
      'contents' => 'Submit',
    ];
    $webform_url = Url::fromRoute('entity.webform.canonical', ['webform' => 'test_ce_webform'])
      ->setRouteParameter('_format', 'json')
      ->setAbsolute();
    return \Drupal::httpClient()->post($this->getBaseUrlProvider()->getAdminBaseUrl() . '/' . $this->webformPath, [
      'multipart' => $multipart,
      'headers' => [
        'Accept' => "application/json",
      ],
      'http_errors' => FALSE,
    ]);
  }

  /**
   * {@inheritDoc}
   *
   * @see \Drupal\webform\Tests\Traits\WebformBrowserTestTrait::loadWebform
   */
  protected function loadWebform($id) {
    $storage = \Drupal::entityTypeManager()->getStorage('webform');
    if ($webform = $storage->load($id)) {
      return $webform;
    }
    else {
      $config_name = 'webform.webform.' . $id;
      if (strpos($id, 'test_') === 0) {
        $config_directory = __DIR__ . '/../../modules/test_ce_webform/config/install';
      }
      else {
        throw new \Exception("Webform $id not valid");
      }

      if (!file_exists("$config_directory/$config_name.yml")) {
        throw new \Exception("Webform $id does not exist in $config_directory");
      }

      $file_storage = new FileStorage($config_directory);
      $values = $file_storage->read($config_name);
      $webform = $storage->create($values);
      $webform->save();
      return $webform;
    }
  }

}
