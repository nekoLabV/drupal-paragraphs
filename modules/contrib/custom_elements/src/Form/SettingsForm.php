<?php

namespace Drupal\custom_elements\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Custom Elements settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The preview provider resolver.
   *
   * @var \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver
   */
  protected $previewResolver;

  /**
   * Constructs a SettingsForm object.
   *
   * @param \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver $preview_resolver
   *   The preview provider resolver.
   */
  public function __construct(CustomElementsPreviewResolver $preview_resolver) {
    $this->previewResolver = $preview_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('custom_elements.preview_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['custom_elements.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_elements_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_elements.settings');

    $form['settings_warning'] = [
      '#type' => 'item',
      '#markup' => '<div class="messages messages--warning">' . $this->t('<strong>Warning:</strong> Changing these settings may require updating your frontend application.') . '</div>',
    ];

    // Build options for render variant, including all available preview
    // providers.
    $render_variant_options = [
      'markup' => $this->t('Custom Elements Markup'),
    ];

    // Add specific preview provider services.
    foreach ($this->previewResolver->getProviders() as $provider) {
      $definition = $provider->getPluginDefinition();
      $render_variant_options['preview:' . $provider->getPluginId()] = $this->t('Preview: @label', [
        '@label' => $definition['label'],
      ]);
    }

    $form['default_render_variant'] = [
      '#type' => 'radios',
      '#title' => $this->t('Default rendering mode in Drupal pages'),
      '#description' => $this->t('Choose the default rendering mode for custom elements when they are part of a traditionally rendered Drupal page. <em>Custom Elements Markup</em> renders the actual custom element tags as part of the markup. <em>Preview options</em> render a preview representation for content editing and administration interfaces. Note: This does not influence API responses of the <a href=":lupus_ce_renderer_url">Lupus Custom Elements Renderer</a> or modules that explicitly render a preview representation, e.g. <a href=":canvas_url">Canvas ExtJS</a>.', [
        ':lupus_ce_renderer_url' => 'https://www.drupal.org/project/lupus_ce_renderer',
        ':canvas_url' => 'https://www.drupal.org/project/canvas_extjs',
      ]),
      '#options' => $render_variant_options,
      '#default_value' => $config->get('default_render_variant') ?? 'markup',
    ];

    $form['markup_style'] = [
      '#type' => 'radios',
      '#title' => $this->t('Markup style'),
      '#description' => $this->t('The markup style to use when rendering custom elements as markup. This affects the slot syntax used in the generated markup.'),
      '#options' => [
        'web_component' => $this->t('Web component style'),
        'vue-3' => $this->t('Vue 3 shorthand style'),
      ],
      '#default_value' => $config->get('markup_style') ?? 'web_component',
    ];

    $form['json_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('JSON serialization format'),
      '#description' => $this->t('The format to use when serializing custom elements to JSON for API responses.'),
      '#options' => [
        'explicit' => $this->t('Explicit (props and slots separated)'),
        'legacy' => $this->t('Legacy (props and slots mixed at root level)'),
      ],
      '#default_value' => $config->get('json_format') ?? 'explicit',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('custom_elements.settings')
      ->set('markup_style', $form_state->getValue('markup_style'))
      ->set('json_format', $form_state->getValue('json_format'))
      ->set('default_render_variant', $form_state->getValue('default_render_variant'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
