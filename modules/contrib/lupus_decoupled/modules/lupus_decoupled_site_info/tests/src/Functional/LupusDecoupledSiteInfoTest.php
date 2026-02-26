<?php

namespace Drupal\Tests\lupus_decoupled_user_login\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Lupus Decoupled Site Info Endpoint.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledSiteInfoTest extends BrowserTestBase {

  const SITE_NAME = 'Lupus Decoupled Test Site Info';
  const SITE_SLOGAN = 'Testing';
  const SITE_EMAIL = 'test@example.com';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'text',
    'serialization',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_schema_metatag',
    'menu_link_content',
    'rest_menu_items',
    'lupus_decoupled_site_info',
    'user',
    'rest',
    'schema_metatag',
    'metatag',
    'token',
    'node',
    'field',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $configFactory = \Drupal::configFactory();
    $configFactory->getEditable('system.site')
      ->set('name', self::SITE_NAME)
      ->set('slogan', self::SITE_SLOGAN)
      ->set('mail', self::SITE_EMAIL)
      ->save();
    $configFactory->getEditable('lupus_decoupled_ce_api.settings')
      ->set('frontend_base_url', 'https://frontend.example.com')
      ->save();
  }

  /**
   * Test site-info endpoint.
   */
  public function testSiteInfoEndpoint() {
    $response = json_decode($this->drupalGet('api/site-info'), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertEquals($response['system.site']['name'], self::SITE_NAME);
    $this->assertEquals($response['system.site']['mail'], self::SITE_EMAIL);
    $this->assertEquals($response['system.site']['slogan'], self::SITE_SLOGAN);
    // Change config and observe the change. Response cache should clear.
    $configFactory = \Drupal::configFactory();
    $new_site_name = $this->randomString();
    $configFactory->getEditable('system.site')
      ->set('name', $new_site_name)
      ->save();
    $site_info_settings = $configFactory->getEditable('lupus_decoupled_site_info.settings');
    $site_info_settings
      ->set('expose', array_merge($site_info_settings->get('expose'), ['system.site:empty']))
      ->save();
    $new_response = json_decode($this->drupalGet('api/site-info'), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertNotEquals($new_response['system.site']['name'], self::SITE_NAME);
    $this->assertEquals($new_response['system.site']['name'], $new_site_name);
    $this->assertEquals($new_response['system.site']['empty'], NULL);
  }

}
