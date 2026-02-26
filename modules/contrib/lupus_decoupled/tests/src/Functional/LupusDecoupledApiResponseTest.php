<?php

namespace Drupal\Tests\lupus_decoupled\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\node\Entity\Node;

/**
 * Test Lupus Decoupled features.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledApiResponseTest extends BrowserTestBase {

  use BaseUrlProviderTrait;

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * API path to created node.
   *
   * @var string
   */
  protected $nodePath;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'content_translation',
    // For admin/content that lists all languages for the node, we need Views:
    'views',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_schema_metatag',
    'menu_link_content',
    'rest_menu_items',
    'schema_web_page',
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

    // Create Basic page node type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->node = $this->drupalCreateNode(['title' => 'Test node']);
    $this->nodePath = 'ce-api/node/' . $this->node->id();

    // Make sure $this->getBaseUrlProvider()->getFrontendBaseUrl() always
    // returns a value. (It's not always the value set here, though; this is
    // just the default.)
    $configFactory = \Drupal::configFactory();
    $configFactory->getEditable('lupus_decoupled_ce_api.settings')
      ->set('frontend_base_url', 'https://frontend.example.com')
      ->save();
    $configFactory->getEditable('rest_menu_items.config')
      ->set('allowed_menus', ['main' => 'main'])
      ->save();
  }

  /**
   * Tests if 'API output' links in HTML page have correct URL in content admin.
   */
  public function testApiOutputLink() {
    // Prerequisite: test if admin/API base URLs aren't rewritten.
    $drupalBaseUrl = getenv('SIMPLETEST_BASE_URL');
    $this->assertSame($drupalBaseUrl, $this->getBaseUrlProvider()
      ->getAdminBaseUrl(), 'Admin base URL must be equal to Drupal URL.');
    $this->assertStringStartsWith($drupalBaseUrl, $this->getBaseUrlProvider()
      ->getApiBaseUrl(), 'API base URL must contain Drupal URL.');

    // Preparation: add German language, then reload node so it re-caches
    // available languages, and translate it.
    ConfigurableLanguage::createFromLangcode('de')->save();
    $this->node = Node::load($this->node->id());

    $translated_node = $this->node->addTranslation('de', ['title' => 'DE Test node'] + $this->node->toArray());
    $translated_node->save();

    $user = $this->drupalCreateUser([
      'access content',
      'access content overview',
      'use api operation link',
    ]);
    $this->drupalLogin($user);

    $this->doTestApiOutputLinks('admin/content');
    // Test that 'current UI language' context has no effect on links.
    $this->doTestApiOutputLinks('de/admin/content');
  }

  /**
   * Tests URLs for API output links (and regular node links) in content admin.
   */
  protected function doTestApiOutputLinks(string $path): void {
    $this->drupalGet($path);
    $domPage = $this->getSession()->getPage();

    $frontendBaseUrl = $this->getBaseUrlProvider()->getFrontendBaseUrl();
    $domTextNode = $domPage->find('xpath', '//a[text() = "Test node"]');
    $this->assertNotEmpty($domTextNode, "Element containing 'Test node' must be visible on $path.");
    $linkHtml = $domTextNode->getParent()->getHtml();
    $this->assertStringStartsWith("<a href=\"$frontendBaseUrl/", $linkHtml, "'Test node' link on $path must contain the frontend base URL.");

    $domTextNode = $domPage->find('xpath', '//a[text() = "DE Test node"]');
    $this->assertNotEmpty($domTextNode, "Element containing 'DE Test node' must be visible on $path.");
    $linkHtml = $domTextNode->getParent()->getHtml();
    $this->assertStringStartsWith("<a href=\"$frontendBaseUrl/de/", $linkHtml, "'DE Test node' link on $path must contain the frontend base URL + langcode.");

    $apiBaseUrl = $this->getBaseUrlProvider()->getApiBaseUrl();
    $domTextNode = $domPage->find('xpath', '//a[text() = "Test node"]/ancestor::tr')
      ->find('xpath', '//a[text() = "View API Output"]');
    $this->assertNotEmpty($domTextNode, "Element containing 'Test node' -> 'View API Output' must be visible on $path.");
    $linkHtml = $domTextNode->getParent()->getHtml();
    $this->assertStringStartsWith("<a href=\"$apiBaseUrl/", $linkHtml, "'Test node' -> 'View API Output' link on $path must contain the API base URL.");

    $domTextNode = $domPage->find('xpath', '//a[text() = "DE Test node"]/ancestor::tr')
      ->find('xpath', '//a[text() = "View API Output"]');
    $this->assertNotEmpty($domTextNode, "Element containing 'DE Test node' -> 'View API Output' must be visible on $path.");
    $linkHtml = $domTextNode->getParent()->getHtml();
    $this->assertStringStartsWith("<a href=\"$apiBaseUrl/de/", $linkHtml, "'DE Test node' -> 'View API Output' link on $path must contain the API base URL + langcode.");
  }

  /**
   * Tests if created node's path redirects to frontend.
   */
  public function testExistingPageResponse() {
    // DriverInterface::getClient() isn't defined but there's no better way
    // as of D10.2. (Core tests also do this.)
    /* @phpstan-ignore-next-line */
    $this->getSession()->getDriver()->getClient()->followRedirects(FALSE);
    $this->maximumMetaRefreshCount = 0;
    $this->drupalGet('node/' . $this->node->id());
    $assert = $this->assertSession();
    $assert->statusCodeEquals(302);
    $frontendBaseUrl = $this->getBaseUrlProvider()->getFrontendBaseUrl();
    $assert->responseHeaderEquals('Location', "$frontendBaseUrl/node/1");
  }

  /**
   * Tests un-existing page access.
   */
  public function test404Page() {
    $this->drupalGet('i-dont-exist');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Tests if created node is accessible at api endpoint.
   */
  public function testExistingPageApiResponse() {
    $this->drupalGet($this->nodePath);
    $this->assertSession()->statusCodeEquals(200);

  }

  /**
   * Tests if 'alternate' link has proper format.
   */
  public function testAlternateLink() {
    // As long as this is the only test that does translation: add language
    // translation here instead of in setUp(). If the number of tests grows:
    // reconsider splitting out into translation specific test file.
    ConfigurableLanguage::createFromLangcode('de')->save();
    // Node needs reload before translation can be added.
    $this->node = Node::load($this->node->id());
    $this->node->addTranslation('de', ['title' => 'DE translation ' . $this->node->label()] + $this->node->toArray());
    $this->node->save();

    $response = json_decode($this->drupalGet($this->nodePath), TRUE);
    // 'alternate' must be in the 'link' section and point to the front end.
    $this->assertStringStartsWith(
      rtrim($this->getBaseUrlProvider()->getFrontendBaseUrl(), '/') . '/de/',
      $this->findLink($response['metatags']['link'] ?? [], 'alternate', 'de')
    );
  }

  /**
   * Finds a specific 'href' value in an array of 'link' metatags.
   *
   * @param array $link_metatags
   *   The array of 'link' metatags.
   * @param string $rel
   *   The 'rel' value to search for.
   * @param string $hreflang
   *   The 'hreflang' value to search for.
   *
   * @return string
   *   The corresponding 'href' value, or empty string if not found.
   */
  private function findLink(array $link_metatags, string $rel, string $hreflang) {
    foreach ($link_metatags as $link) {
      if (isset($link['href']) && isset($link['rel']) && $link['rel'] === $rel
        && isset($link['hreflang']) && $link['hreflang'] === $hreflang) {
        return $link['href'];
      }
    }

    return '';
  }

  /**
   * Tests if created "meta title" is available at api endpoint.
   */
  public function testMetaTag() {
    $response = json_decode($this->drupalGet($this->nodePath), TRUE);
    $metaTitle = $response['metatags']['meta'][0]['content'];
    // It contains the site name as well so cannot use assertEquals().
    $this->assertStringContainsString($this->node->getTitle(), $metaTitle);
  }

  /**
   * Tests if created "schema description" is present in jsonld section.
   */
  public function testSchemaTag() {
    $bundle = 'page';
    $fieldName = 'field_meta';
    // Create storage for metatag field.
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => $fieldName,
      'entity_type' => 'node',
      'type' => 'metatag',
    ]);
    $fieldStorage->save();

    // Create config for metatag field.
    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => $bundle,
    ]);
    $field->save();

    // Create a node including schema tag for testing.
    $node = $this->drupalCreateNode([
      'type' => $bundle,
      $fieldName => serialize([
        'schema_web_page_description' => 'my schema web page description',
      ]),
    ]);

    $response = json_decode($this->drupalGet('ce-api/node/' . $node->id()), TRUE);
    $description = $response['metatags']['jsonld']['@graph'][0]['description'];
    // @todo Getting an error instead of a failure if the key doesn't exist.
    $this->assertEquals('my schema web page description', $description);
  }

  /**
   * Tests if breadcrumbs in schema metatags point to the frontend.
   */
  public function testSchemaBreadcrumbs() {
    MetatagDefaults::create([
      'id' => 'node__page',
      'tags' => [
        'schema_web_page_breadcrumb' => 'Yes',
      ],
    ])->save();

    $response = json_decode($this->drupalGet($this->nodePath), TRUE);
    // The '@graph' element in theory can contain several numbered indices.
    // Assume this barebones setup returns one, index 0, containing all tags.
    $this->assertTrue(isset($response['metatags']['jsonld']['@graph'][0]['breadcrumb']['itemListElement']), "Breadcrumb must be set in metatags (jsonld > @graph > 0)");
    $breadcrumbs = $response['metatags']['jsonld']['@graph'][0]['breadcrumb']['itemListElement'];

    // By default, there likely is only one link, containing the home page. If
    // the default happens to change, test all.
    $frontendBaseUrl = $this->getBaseUrlProvider()->getFrontendBaseUrl();
    foreach ($breadcrumbs as $index => $breadcrumb) {
      $this->assertStringStartsWith($frontendBaseUrl, $breadcrumb['item'], "Breadcrumb $index must start with the frontend base URL.");
    }
  }

  /**
   * Tests links in menu items.
   */
  public function testMenu() {
    MenuLinkContent::create([
      'title' => 'Menu link test title',
      'provider' => 'menu_link_content',
      'menu_name' => 'main',
      'link' => ['uri' => 'internal:/node/1'],
    ])->save();

    $response = json_decode($this->drupalGet('ce-api/api/menu_items/main'), TRUE);
    $this->assertSame('Menu link test title', $response[0]['title']);
    $this->assertFalse($response[0]['external'], 'Tested menu link must be marked as external:False.');
    $this->assertStringStartsWith('/', $response[0]['relative'], "'relative' menu item property must start with a forward slash.");
    $frontendBaseUrl = $this->getBaseUrlProvider()->getFrontendBaseUrl();
    $this->assertStringStartsWith($frontendBaseUrl, $response[0]['absolute'], "'absolute' menu item property must start with the frontend base URL.");
  }

}
