<?php

namespace Drupal\toolbar_language_switcher;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Provides a builder for rendering a language switcher in the admin toolbar.
 */
class RenderBuilder {

  use StringTranslationTrait;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Id of the current language.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $currentLanguage;

  /**
   * List of the available languages.
   *
   * @var \Drupal\Core\Language\LanguageInterface[]
   */
  protected $languages;

  /**
   * Current route name.
   *
   * @var string
   */
  protected $route;

  /**
   * Current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * RenderBuilder constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The currently active route match object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    RouteMatchInterface $route_match,
    RendererInterface $renderer,
  ) {
    $this->languageManager = $language_manager;
    $this->currentRouteMatch = $route_match;
    $this->renderer = $renderer;
    // Get languages, get current route.
    $this->currentLanguage = $this->languageManager->getCurrentLanguage();
  }

  /**
   * Main build method.
   *
   * @return array
   *   Render array for the toolbar items.
   */
  public function build() {
    $native_languages = $this->languageManager->getNativeLanguages();
    $urls = $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_INTERFACE, Url::fromRouteMatch($this->currentRouteMatch));
    $urls = $urls->links ?? [];
    $urls_count = count($urls);
    if ($urls_count < 2) {
      return [];
    }
    // Set cache.
    $items['admin_toolbar_langswitch'] = [
      '#cache' => [
        'contexts' => [
          'languages:language_interface',
          'url',
        ],
      ],
    ];

    // Build toolbar item and tray.
    $items['admin_toolbar_langswitch'] += [
      '#type' => 'toolbar_item',
      '#weight' => 999,
      'tab' => [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#attributes' => [
          'class' => [
            'toolbar-icon',
            'toolbar-icon-language',
          ],
        ],
        '#attached' => [
          'library' => [
            'toolbar_language_switcher/toolbar',
          ],
        ],
      ],
    ];
    $current_language_name = NULL;
    $current_language_id = $this->currentLanguage->getId();
    if (isset($urls[$current_language_id]['title'])) {
      $current_language_title = $urls[$current_language_id]['title'];

      if (is_string($current_language_title)) {
        $current_language_name = $current_language_title;
      }
      // Support render arrays.
      elseif (is_array($current_language_title)) {
        $current_language_title = $this->renderer->render($current_language_title);
      }
    }
    if (empty($current_language_name)) {
      $current_language_name = $native_languages[$current_language_id]->getName();
    }
    if ($urls_count > 2) {
      // Get links.
      $links = [];
      foreach ($urls as $langcode => $url_info) {
        $link = [
          'attributes' => [],
        ];
        $link_title = $url_info['title'];
        if (is_string($link_title)) {
          $language_name = $link_title;
        }
        else {
          $language_name = $native_languages[$langcode]->getName();

          // Support render arrays.
          if (is_array($link_title)) {
            $link_title = $this->renderer->render($link_title);
          }
        }
        $url_options = [
          'query' => $url_info['query'],
        ];
        if (!empty($url_info['language'])) {
          $url_options['language'] = $url_info['language'];
          if ($url_info['language']->getId() === $current_language_id) {
            $link['attributes']['class'][] = 'is-active';
            $link['attributes']['title'] = $this->t(
              'Current active @current language',
              ['@current' => $language_name]
            );
            $url_options['fragment'] = '!';
          }
        }
        else {
          $link['attributes']['title'] = $this->t(
            'Change @current language to @another',
            [
              '@current' => $current_language_name,
              '@another' => $language_name,
            ]
          );
        }
        $link['attributes'] = NestedArray::mergeDeep(
          $link['attributes'],
          $url_info['attributes']
        );
        $link['title'] = $link_title;
        /** @var \Drupal\Core\Url $url */
        $url = $url_info['url'];
        $link['url'] = $url->setOptions($url_options);
        $links[] = $link;
      }
      // Build toolbar item and tray.
      $items['admin_toolbar_langswitch'] = NestedArray::mergeDeep(
        $items['admin_toolbar_langswitch'],
        [
          'tab' => [
            '#value' => $current_language_title,
            '#attributes' => [
              'href' => '#',
              'title' => $this->t('Selected language: @lang', [
                '@lang' => $current_language_name,
              ]),
            ],
          ],
          'tray' => [
            '#heading' => $this->t('Admin Toolbar Language Switcher'),
            'content' => [
              '#theme' => 'links',
              '#links' => $links,
              '#attributes' => [
                'class' => ['toolbar-menu'],
              ],
            ],
          ],
        ]
      );
    }
    else {
      unset($urls[$current_language_id]);
      $another_language_link_info = reset($urls);
      $another_language_langcode = key($urls);
      $another_language_name = $native_languages[$another_language_langcode]->getName();
      $another_language_title = $another_language_link_info['title'] ?? $another_language_name;
      // Support render arrays.
      if (is_array($another_language_title)) {
        $another_language_title = $this->renderer->render($another_language_title);
      }
      /** @var \Drupal\Core\Url $another_language_url */
      $another_language_url = $another_language_link_info['url'];
      if (isset($another_language_link_info['language'])) {
        $another_language = $another_language_link_info['language'];
        $another_language_url->setOption('language', $another_language);
      }
      $another_language_url
        ->setOption('query', $another_language_link_info['query']);

      // Build toolbar item.
      $items['admin_toolbar_langswitch'] = NestedArray::mergeDeep(
        $items['admin_toolbar_langswitch'],
        [
          'tab' => [
            '#value' => $this->t(
              'Switch to @another',
              ['@another' => $another_language_title],
              ['langcode' => $another_language_langcode]
            ),
            '#attributes' => NestedArray::mergeDeep(
              $another_language_link_info['attributes'],
              [
                'href' => $another_language_url->toString(),
                'title' => $this->t(
                  'Switch @current language to @another',
                  [
                    '@current' => $current_language_name,
                    '@another' => $another_language_name,
                  ],
                  ['langcode' => $another_language_langcode]
                ),
              ]
            ),
          ],
        ]
      );
    }

    return $items;
  }

}
