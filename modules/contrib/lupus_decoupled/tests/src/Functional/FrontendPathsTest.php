<?php

namespace Drupal\Tests\lupus_decoupled\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the frontend paths behavior.
 *
 * @group lupus_decoupled
 */
class FrontendPathsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'views',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'menu_link_content',
    'rest_menu_items',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Disable schema checking.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->config('lupus_decoupled.settings')
      ->set('frontend_base_url', 'http://frontend.example.com')
      ->set('frontend_routes_redirect', 'true')
      ->save();

    // Create test content and admin user.
    $this->drupalCreateContentType(['type' => 'article']);
    $this->drupalCreateNode(['type' => 'article', 'title' => 'Test article']);

    // Create user with minimal required permissions.
    $admin_user = $this->drupalCreateUser([
      'access content',
      'access content overview',
    ]);
    $this->drupalLogin($admin_user);
    // Disable following redirects.
    // DriverInterface::getClient() isn't defined but there's no better way
    // as of D10.2. (Core tests also do this.)
    /* @phpstan-ignore-next-line */
    $this->getSession()->getDriver()->getClient()->followRedirects(FALSE);
    $this->maximumMetaRefreshCount = 0;
  }

  /**
   * Tests that frontend paths are properly kept or redirected.
   */
  public function testKeepFrontendPaths() {
    // Test /ce-api/user to NOT redirect to the backend.
    $this->drupalGet('/ce-api/user');
    $this->assertSession()->statusCodeEquals(200);
    $content = $this->getSession()->getPage()->getContent();
    $this->assertStringContainsString('"statusCode":302', $content);
    $this->assertStringNotContainsString($this->baseUrl, $content);

    // Test /user - should stay on backend.
    $this->drupalGet('/user');
    $this->assertSession()->statusCodeEquals(302);
    $this->assertSession()->responseHeaderEquals('Location', $this->baseUrl . '/user/' . $this->loggedInUser->id());
  }

}
