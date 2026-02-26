<?php

namespace Drupal\Tests\lupus_decoupled_user_form\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Psr7\Response;

/**
 * Tests the session cookie domain configuration and user form api response.
 *
 * @group lupus_decoupled_user_form
 */
class LupusSessionDomainFunctionalTest extends BrowserTestBase {

  use BaseUrlProviderTrait;

  /**
   * The default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'claro';

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'path_alias',
    'schema_metatag',
    'metatag',
    'token',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_form',
    'lupus_decoupled_user_form',
    'menu_link_content',
    'rest_menu_items',
  ];

  /**
   * Tests that no custom domain is set by default.
   */
  public function testNoDomainSetByDefault(): void {
    $this->setContainerParameter('session.storage.options', ['cookie_domain' => FALSE]);
    $this->rebuildContainer();
    $account = $this->createUser();
    $this->assertIsObject($account);
    // The 'passRaw' property is intentionally not defined, but is set by
    // UserCreationTrait::createUser() in test environments. Since passwords are
    // only needed for testing, we can safely ignore the PHPStan error.
    /* @phpstan-ignore property.notFound */
    $response = $this->loginRequest($account->getAccountName(), $account->passRaw);
    $this->assertSessionCookie($response, FALSE);
  }

  /**
   * Tests that a custom domain is set when configured.
   */
  public function testCustomDomainSet(): void {
    $this->setContainerParameter('session.storage.options', ['cookie_domain' => '.example.com']);
    $this->rebuildContainer();
    $account = $this->createUser();
    $this->assertIsObject($account);
    // The 'passRaw' property is intentionally not defined, but is set by
    // UserCreationTrait::createUser() in test environments. Since passwords are
    // only needed for testing, we can safely ignore the PHPStan error.
    /* @phpstan-ignore property.notFound */
    $response = $this->loginRequest($account->getAccountName(), $account->passRaw);
    $this->assertSessionCookie($response, TRUE, '.example.com');
  }

  /**
   * Executes a login HTTP request for a given serialization format.
   *
   * @param string $name
   *   The username.
   * @param string $pass
   *   The user password.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The HTTP response.
   */
  protected function loginRequest($name, $pass) {
    $user_login_url = Url::fromRoute('user.login.http')
      ->setRouteParameter('_format', 'json')
      ->setAbsolute();
    $request_body = [];
    $request_body['name'] = $name;
    $request_body['pass'] = $pass;

    return \Drupal::httpClient()->post($user_login_url->toString(), [
      'body' => Json::encode($request_body),
      'headers' => [
        'Accept' => "application/json",
      ],
      'http_errors' => FALSE,
    ]);
  }

  /**
   * Asserts the presence or absence of the domain in the session cookie.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   The login request response.
   * @param bool $hasDomain
   *   Whether the cookie should have a domain set.
   * @param string|null $expectedDomain
   *   The expected domain value, if $hasDomain is TRUE.
   */
  protected function assertSessionCookie(Response $response, bool $hasDomain, ?string $expectedDomain = NULL): void {
    $session_cookie = $this->getSessionCookie($response);
    $this->assertNotNull($session_cookie, 'Session cookie should be present in Set-Cookie header.');

    if ($hasDomain) {
      $this->assertStringContainsString('domain=' . $expectedDomain, $session_cookie, 'Session cookie should have the correct domain.');
    }
    else {
      $this->assertStringNotContainsString('domain=', $session_cookie, 'Session cookie should not have a domain set.');
    }
  }

  /**
   * Get session cookie.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   The login request response.
   *
   * @return string|null
   *   The session cookie header.
   */
  public function getSessionCookie(Response $response): mixed {
    $headers = $response->getHeaders();
    $set_cookie_headers = $headers['Set-Cookie'] ?? [];
    $session_cookie = NULL;
    foreach ($set_cookie_headers as $header) {
      if (strpos($header, 'SESS') === 0) {
        $session_cookie = $header;
        break;
      }
    }
    return $session_cookie;
  }

  /**
   * Assert the session cookie is not set.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   The login request response.
   */
  protected function assertNoSessionCookie(Response $response): void {
    $session_cookie = $this->getSessionCookie($response);
    $this->assertNull($session_cookie, 'Session cookie should not be present.');
  }

  /**
   * Test user log in api endpoint.
   */
  public function testUserLoginApi() {
    $response = json_decode($this->drupalGet('ce-api/user/login'), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('Log in', $response['title'] ?? []);
    $this->assertSame('user_login_form', $response['content']['props']['formId'] ?? []);
    $this->assertSame('post', $response['content']['props']['method'] ?? []);
    $form_content = $response['content']['slots']['default'] ?? '';
    $this->assertStringContainsString('name="pass"', $form_content);
    $this->assertStringContainsString('User', $form_content);
    $this->assertStringContainsString('Password', $form_content);
    // For logged-in user form is not accessible.
    $user = $this->drupalCreateUser([
      'access content',
      'access content overview',
      'use api operation link',
    ]);
    $this->drupalLogin($user);
    $logged_in_response = json_decode($this->drupalGet('ce-api/user/login'), TRUE);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSame('Access denied', $logged_in_response['title'] ?? []);
  }

  /**
   * Test logging in through login form.
   */
  public function testLoginFormSubmission() {
    $account = $this->createUser(admin: TRUE);
    $response = json_decode($this->drupalGet('ce-api/user/login'), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $form_build_id = $this->getFormBuildId($response['content']['slots']['default'] ?? '');
    $multipart = [];
    $multipart[] = [
      'name' => 'name',
      'contents' => $account->getAccountName(),
    ];
    $multipart[] = [
      'name' => 'pass',
      /* @phpstan-ignore property.nonObject */
      'contents' => $account->passRaw,
    ];
    $multipart[] = [
      'name' => 'form_build_id',
      'contents' => $form_build_id,
    ];
    $multipart[] = [
      'name' => 'form_id',
      'contents' => 'user_login_form',
    ];
    $multipart[] = [
      'name' => 'op',
      'contents' => 'Submit',
    ];
    $post = \Drupal::httpClient()->post($this->getBaseUrlProvider()->getAdminBaseUrl() . '/ce-api/user/login', [
      'multipart' => $multipart,
      'headers' => [
        'Accept' => "application/json",
      ],
      'http_errors' => FALSE,
    ]);
    $post->getBody()->rewind();
    $redirect = json_decode($post->getBody()->getContents(), TRUE);
    $this->assertSessionCookie($post, FALSE);
    $this->assertArrayHasKey('redirect', $redirect);
    $this->assertStringContainsString('user', $redirect['redirect']['url']);
    $this->assertSame(303, $redirect['redirect']['statusCode']);
    $multipart[1]['contents'] = 'TestWrongPass';
    $false_pass_post = \Drupal::httpClient()->post($this->getBaseUrlProvider()->getAdminBaseUrl() . '/ce-api/user/login', [
      'multipart' => $multipart,
      'headers' => [
        'Accept' => "application/json",
      ],
      'http_errors' => FALSE,
    ]);
    $this->assertNoSessionCookie($false_pass_post);
    $false_pass_post->getBody()->rewind();
    $false_pass_body = json_decode($false_pass_post->getBody()->getContents(), TRUE);
    // With wrong password get Log In page back again.
    $this->assertStringContainsString('Unrecognized username or password', $false_pass_body['messages']['error'][0]);
    $this->assertSame('Log in', $false_pass_body['title']);
  }

  /**
   * Get form_build_id needed to submit the form.
   *
   * @param string $form_html
   *   Markup of form.
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

}
