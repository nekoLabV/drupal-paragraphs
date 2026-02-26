<?php

namespace Drupal\lupus_decoupled_ce_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure lupus decoupled settings for this site.
 */
class LupusDecoupledSettingsForm extends ConfigFormBase {

  /**
   * Config name.
   */
  const CONFIG_NAME = 'lupus_decoupled_ce_api.settings';

  /**
   * The custom elements preview provider plugin manager.
   *
   * @var \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderManager|null
   */
  protected $previewProviderManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    // Inject preview provider manager as optional dependency. It's only
    // available with custom_elements >= 3.2.0.
    if ($container->has('plugin.manager.custom_elements_preview_provider')) {
      $instance->previewProviderManager = $container->get('plugin.manager.custom_elements_preview_provider');
    }
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lupus_decoupled_ce_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);
    if ($frontend_base_url = getenv('DRUPAL_FRONTEND_BASE_URL')) {
      $this->messenger()->addWarning($this->t('Environment variable DRUPAL_FRONTEND_BASE_URL is set to @frontend_base_url. It will override Frontend Base URL configuration.', [
        '@frontend_base_url' => $frontend_base_url,
      ]));
    }

    $form['frontend_base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Frontend Base URL'),
      '#default_value' => $config->get('frontend_base_url'),
      '#pattern' => 'https?://.*',
      '#placeholder' => 'https://your-frontend-site.com',
      '#description' => $this->t('The base URL of your frontend site. This value can be overridden with DRUPAL_FRONTEND_BASE_URL environment variable.'),
    ];

    $form['frontend_routes_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('View content on decoupled frontend'),
      '#default_value' => $config->get('frontend_routes_redirect'),
      '#description' => $this->t('Opens frontend routes in the frontend by redirecting to the frontend.'),
    ];

    $form['absolute_file_urls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Generate custom-elements API responses with absolute file URLs'),
      '#default_value' => $config->get('absolute_file_urls') ?? TRUE,
      '#description' => $this->t('Serving files and images using absolute URLs keeps the file URLs working in a decoupled frontend. When disabled, care must be taken to resolve URLs otherwise.'),
    ];

    // Add preview provider selection if available.
    if ($this->previewProviderManager) {
      $definitions = $this->previewProviderManager->getDefinitions();

      $options = [];
      foreach ($definitions as $plugin_id => $definition) {
        $options[$plugin_id] = $definition['label'];
      }

      if (!empty($options)) {
        $form['preview_provider'] = [
          '#type' => 'select',
          '#title' => $this->t('Preview Provider'),
          '#options' => $options,
          '#default_value' => $config->get('preview_provider') ?? 'markup',
          '#description' => $this->t('Select which preview provider to use for rendering component previews in administrative interfaces like Drupal Canvas. The Frontend Base URL will be used as the base URL for JavaScript-based providers.'),
          '#states' => [
            'visible' => [
              ':input[name="frontend_base_url"]' => ['filled' => TRUE],
            ],
          ],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config(static::CONFIG_NAME);
    $config->set('frontend_base_url', $form_state->getValue('frontend_base_url'));
    $config->set('frontend_routes_redirect', $form_state->getValue('frontend_routes_redirect'));
    $config->set('absolute_file_urls', $form_state->getValue('absolute_file_urls'));
    if ($form_state->hasValue('preview_provider')) {
      $config->set('preview_provider', $form_state->getValue('preview_provider'));
    }
    $config->save();
  }

}
