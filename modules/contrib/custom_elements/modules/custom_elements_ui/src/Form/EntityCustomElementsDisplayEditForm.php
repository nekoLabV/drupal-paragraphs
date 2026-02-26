<?php

namespace Drupal\custom_elements_ui\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Field\PluginSettingsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementsFieldFormatterInterface;
use Drupal\custom_elements\CustomElementsFieldFormatterPluginManager;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver;
use Drupal\field_ui\FieldUI;
use Drupal\field_ui\Form\EntityDisplayFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\custom_elements\Entity\EntityCeDisplayInterface;

/**
 * Forked version of EntityDisplayFormBase class of core.
 *
 * The EntityCeDisplay object has makes use of CeFormatters instead of core
 * field formatters for rendering display components - as returned by
 * EntityCeDisplay::getRenderer(). Our CE-field-formatters are properly
 * configurable plugins. So instead of having plugin-settings forms like
 * core-field formatters, we have plugin configuration forms. The existing
 * EntityDisplay logic has been adapted to the new API, some keys, variable
 * names and comments still refer to "plugin settings" instead of "plugin
 * configuration" though.
 * This is good to keep, because we are also supporting the configuration of
 * core field formatters as part of our CoreFieldCeFieldFormatter plugins.
 *
 * However, the core field formatter settings form API is simple and pre-dates
 * the core SubformState, so it needs to have the same global location in the
 * form array as core does it. Long story short, we need to keep the
 * 'settings_edit_form' key there. For example,
 * empty_fields_field_formatter_third_party_settings_form() relies on it.
 *
 * @internal
 *   Form classes are internal.
 */
class EntityCustomElementsDisplayEditForm extends EntityDisplayFormBase {

  use CustomElementGeneratorTrait;
  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'view';

  /**
   * The custom element formatter plugin manager.
   *
   * @var \Drupal\custom_elements\CustomElementsFieldFormatterPluginManager
   */
  protected $customElementFormatterPluginManager;

  /**
   * The custom elements display preview resolver service.
   *
   * @var \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver
   */
  protected $previewResolver;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Constructs a new EntityCustomElementsDisplayEditForm.
   *
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager.
   * @param \Drupal\Component\Plugin\PluginManagerBase $plugin_manager
   *   The widget or formatter plugin manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   (optional) The entity display_repository.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   (optional) The entity field manager.
   * @param \Drupal\custom_elements\CustomElementsFieldFormatterPluginManager $ce_formatter_plugin_manager
   *   The custom element formatter plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver $preview_resolver
   *   The preview resolver service.
   */
  public function __construct(
    FieldTypePluginManagerInterface $field_type_manager,
    PluginManagerBase $plugin_manager,
    EntityDisplayRepositoryInterface $entity_display_repository,
    EntityFieldManagerInterface $entity_field_manager,
    CustomElementsFieldFormatterPluginManager $ce_formatter_plugin_manager,
    RendererInterface $renderer,
    CustomElementsPreviewResolver $preview_resolver,
  ) {
    parent::__construct($field_type_manager, $plugin_manager, $entity_display_repository, $entity_field_manager);
    $this->customElementFormatterPluginManager = $ce_formatter_plugin_manager;
    $this->renderer = $renderer;
    $this->previewResolver = $preview_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.formatter'),
      $container->get('entity_display.repository'),
      $container->get('entity_field.manager'),
      $container->get('custom_elements.plugin.manager.field.custom_element_formatter'),
      $container->get('renderer'),
      $container->get('custom_elements.preview_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    assert($entity instanceof EntityCeDisplayInterface);
    // Convert loaded entity's components to the 'fixed row names' which the
    // form can use throughout AJAX reloads. Add fake component properties that
    // are unset on save():
    // - 'name' for the editable names,
    // - 'region' for compatibility with Core tabledrag.js (always 'content')
    $new_components = $skip_components = $broken_components = [];
    $remove_components = array_fill_keys(array_keys($entity->getComponents()), TRUE);
    foreach ($entity->getComponents() as $component_name => $component) {
      if (!isset($component['field_name'])) {
        $broken_components[$component_name] = TRUE;
        continue;
      }
      if (isset($component['name'])) {
        // This is an already-converted entity (set by e.g. an AJAX call).
        $skip_components[$component_name] = TRUE;
        unset($remove_components[$component_name]);
        continue;
      }
      $component['name'] = $component_name;
      $new_components[$component['field_name']] = $component;
    }

    if ($broken_components) {
      $this->messenger()->addWarning($this->t("Some fields in the custom elements display are not shown and will be lost, because they have no 'field_name' property: @keys", ['@keys' => implode(', ', array_keys($broken_components))]));
    }

    if ($new_components) {
      if ($skip_components) {
        // Either all, or none, of the components should already have 'name'
        // set. A combination of both points to a bug / wrongly saved entity.
        // But do not log; the form will correct this on save. Only if one
        // component's 'name' clashes with another's key, warn that it's being
        // overwritten.
        $overwritten_components = array_intersect_key($skip_components, $new_components);
        if ($overwritten_components) {
          $this->messenger()->addWarning($this->t("Some fields in the custom elements display are not shown and will be lost, because they clash with other components' names: @keys", ['@keys' => implode(', ', array_keys($skip_components))]));
        }
      }

      // Key the changed components by the form row names; remove unused keys.
      foreach ($new_components as $row_name => $component) {
        $entity->setComponent($row_name, $component);
        unset($remove_components[$row_name]);
      }
      foreach (array_keys($remove_components) as $key) {
        $entity->removeComponent($key);
      }
    }

    return parent::setEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $entity */
    $entity = $this->getEntity();

    [$show_lb_option, $lb_extra_message, $layout_link_view_mode, $always_show_non_lb_settings]
      = $this->layoutBuilderEnabledOptions();

    if (!$show_lb_option && $lb_extra_message) {
      // General message, when "use LB" is not applicable / invisible.
      $form['header_message'] = [
        '#markup' => '<p><em>' . $lb_extra_message . '</em></p>',
      ];
    }

    $form['custom_element_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom element name'),
      '#description' => $this->t('Defines the custom element which is going to be used for the display. There must be a matching implementation in the frontend to render the display.'),
      '#default_value' => $entity->getCustomElementName(),
      '#size' => 20,
      // We could make the CE-name not-required when force_auto=on, because
      // code can set their own CE-name/tag. However,
      // - an empty CE-name for a loaded entity always gets overwritten with a
      //   default value by EntityCeDisplay::init();
      // - the text element is always visible on screen, so saving an empty
      //   value will just make the user confused about why the value shows as
      //   non-empty after save.
      // So, just keep it required.
      '#required' => TRUE,
    ];

    // If Layout Builder isn't enabled, useLayoutBuilder is ignored, but if
    // this value is True then don't force-override to False on next save.
    $form['force_layout'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Layout Builder'),
      '#description' => $this->t("The custom element will consist of 'drupal-layout' elements, each representing a layout's section."),
      '#default_value' => $entity->getUseLayoutBuilder(),
      '#access' => (bool) $show_lb_option,
    ];
    if ($show_lb_option && $lb_extra_message) {
      $form['force_layout']['#description'] .= '<br>' . $lb_extra_message;
    }
    if ($layout_link_view_mode) {
      $entity_type = $this->entityTypeManager->getDefinition($entity->getTargetEntityTypeId());
      $bundle_parameter_key = $entity_type->getBundleEntityType() ?: 'bundle';
      $route_parameters = [
        $bundle_parameter_key => $entity->getTargetBundle(),
        'view_mode_name' => $layout_link_view_mode,
      ];
      $form['manage_layout_wrapper'] = [
        '#type' => 'fieldset',
        '#states' => [
          'visible' => [
            ':input[name="force_layout"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['manage_layout_wrapper']['manage_layout'] = [
        '#type' => 'link',
        '#title' => $this->t('Manage layout'),
        '#attributes' => ['class' => ['button']],
        '#url' => Url::fromRoute("layout_builder.defaults." . $entity->getTargetEntityTypeId() . '.view', $route_parameters, ['query' => $this->getDestinationArray()]),
      ];
      // If "Use layout" is originally off, remind the user that it will
      // still be off if they click "Manage layout" now.
      $messages = [];
      if (!$entity->getUseLayoutBuilder()) {
        $messages[] = $this->t('(Save this screen first.)');
      }
      if ($layout_link_view_mode != $this->entity->getMode()) {
        $messages[] = $this->t('Note this is the layout for view mode %other_mode, not %this_mode.', [
          '%this_mode' => $this->entity->getMode(),
          '%other_mode' => $layout_link_view_mode,
        ]);
      }
      if ($show_lb_option && !$always_show_non_lb_settings) {
        // The field list cannot be hidden (because draggable-js stuff), which
        // makes it unclear that it's sometimes not used.
        $messages[] = $this->t("The below field table is not used when 'Use Layout Builder' or 'Automatic processing' are enabled.");
      }
      if ($messages) {
        $form['manage_layout_wrapper']['manage_layout_message'] = ['#markup' => implode('<br>', $messages)];
      }
    }

    // @todo replace by parent::form() when EntityDisplayFormBase is removed
    //   as parent.
    $form = EntityForm::form($form, $form_state);

    $field_definitions = $this->getConfiguredFieldDefinitions();

    if ($show_lb_option && !$always_show_non_lb_settings) {
      // The field list cannot be hidden (because draggable-js stuff), which
      // makes it unclear that it's sometimes not used.
      // This was added into the fieldset above, if possible.
      if (!$layout_link_view_mode) {
        $form['fields_usage_message_wrapper'] = [
          '#type' => 'fieldset',
          '#states' => [
            'visible' => [
              ':input[name="force_layout"]' => ['checked' => TRUE],
            ],
          ],
          'message' => [
            '#markup' => $this->t("The below field table is not used when 'Use Layout Builder' or 'Automatic processing' are enabled."),
          ],
        ];
      }
    }
    elseif ($entity->getForceAutoProcessing()) {
      // Since we moved the autoprocessing option to below the field table,
      // it's not clear that the field table is not used, especially if it
      // contains several fields. No need to make it invisible dynamically
      // when autoprocessing gets enabled.
      $form['fields_usage_message_wrapper'] = [
        '#markup' => '<p><em>' . $this->t("The below field table is not used when 'Automatic processing' is enabled.") . '</em></p>',
      ];
    }

    $form += [
      '#entity_type' => $this->entity->getTargetEntityTypeId(),
      '#bundle' => $this->entity->getTargetBundle(),
      '#fields' => array_keys($field_definitions),
    ];

    $regions = $this->getRegions();
    $table = [
      '#type' => 'field_ui_table',
      '#header' => $this->getTableHeader(),
      '#regions' => [
        'content' => $regions['content'],
      ],
      '#attributes' => [
        'class' => ['field-ui-overview'],
        'id' => 'field-display-overview',
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'field-weight',
        ],
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'field-parent',
          'subgroup' => 'field-parent',
          'source' => 'field-name',
        ],
      ],
    ];

    // Build only fields configured in the display.
    foreach ($field_definitions as $field_name => $field_definition) {
      $table[$field_name] = $this->buildFieldRow($field_definition, $form, $form_state);
    }

    $trigger = $form_state->getTriggeringElement();
    if ($trigger && isset($trigger['#name']) && $trigger['#name'] == 'add_select') {
      // Add a new field.
      $field_name = $form_state->getValue('add_select');
      $field_definition = $this->getFieldDefinitions()[$field_name];
      // Component name is never saved; for the moment, it must be equal to the
      // field name.
      $component_name = $field_name;
      $default_name = str_starts_with($field_name, 'field_') ? substr($field_name, strlen('field_')) : $field_name;
      $this->entity->setComponent($component_name, [
        'name' => $default_name,
        'field_name' => $field_name,
        'is_slot' => str_starts_with($field_definition->getType(), 'text') ? 1 : 0,
        'formatter' => 'auto',
      ]);
      $table[$field_name] = $this->buildFieldRow($field_definition, $form, $form_state);

      $form['#fields'][] = $field_name;
    }

    $form['fields'] = $table;

    // Add new fields: for now, only the ones that aren't added yet.
    $add_options = [];
    $configured_fields_as_keys = array_flip($form['#fields']);
    foreach ($this->getFieldDefinitions() as $field_name => $field_definition) {
      if (!isset($configured_fields_as_keys[$field_name])) {
        $add_options[$field_name] = $field_definition->getLabel();
      }
    }
    if ($add_options) {
      asort($add_options);
      $form['add_select'] = [
        '#type' => 'select',
        '#title' => $this->t('Add field'),
        '#options' => $add_options,
        '#empty_option' => $this->t('- Select -'),
        '#ajax' => [
          'callback' => '::multistepAjax',
          'wrapper' => 'field-display-overview-wrapper',
          'effect' => 'fade',
        ],
      ];
    }

    // Advanced settings.
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#open' => $entity->getForceAutoProcessing(),
    ];

    $form['advanced']['force_auto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatic processing'),
      '#description' => $this->t('Builds the custom element with code-controlled processing logic that varies by field type. This may override the custom element name. The field table above will not be used.'),
      '#default_value' => $entity->getForceAutoProcessing(),
    ];

    // Custom display settings.
    if ($this->entity->getMode() == 'default') {
      // Only show the settings if there is at least one custom display mode.
      $display_mode_options = $this->getDisplayModeOptions();
      // Unset default option.
      unset($display_mode_options['default']);
      if ($display_mode_options) {
        $form['modes'] = [
          '#type' => 'details',
          '#title' => $this->t('Custom display settings'),
        ];
        // Prepare default values for the 'Custom display settings' checkboxes.
        $default = [];
        if ($enabled_displays = array_filter($this->getDisplayStatuses())) {
          $default = array_keys(array_intersect_key($display_mode_options, $enabled_displays));
        }
        natcasesort($display_mode_options);
        $form['modes']['display_modes_custom'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Use custom display settings for the following @display_context modes', ['@display_context' => $this->displayContext]),
          '#options' => $display_mode_options,
          '#default_value' => $default,
        ];
        // Provide link to manage display modes.
        $form['modes']['display_modes_link'] = $this->getDisplayModesLink();
      }
    }
    // Custom elements display preview.
    $form['preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Preview'),
      '#open' => TRUE,
    ];

    // Get available preview providers from the resolver service.
    $preview_provider_options = [];
    foreach ($this->previewResolver->getProviders() as $provider_id => $provider) {
      $plugin_definition = $provider->getPluginDefinition();
      $preview_provider_options[$provider_id] = $plugin_definition['label'];
    }

    // Preview controls container (provider select and update button).
    $form['preview']['controls'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => 'display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;',
      ],
    ];
    $form['preview']['controls']['preview_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Preview provider'),
      '#title_display' => 'invisible',
      '#options' => $preview_provider_options,
      '#default_value' => $form_state->getValue('preview_provider', 'json'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['style' => 'margin: 0;'],
    ];
    $form['preview']['controls']['preview_refresh'] = [
      '#type' => 'submit',
      '#op' => 'refresh_preview',
      '#submit' => ['::multistepSubmit'],
      '#value' => $this->t('Update'),
      '#ajax' => [
        'callback' => '::buildPreviewAjax',
        'wrapper' => 'ce-display-preview-wrapper',
        'effect' => 'fade',
        'progress' => 'none',
      ],
      '#wrapper_attributes' => ['style' => 'margin: 0;'],
    ];

    // Preview content area (empty initially, populated on button click).
    $form['preview']['content'] = $this->buildPreview($form, $form_state);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
    ];

    $form['#attached']['library'][] = 'custom_elements_ui/drupal.custom_elements_ui';

    return $form;
  }

  /**
   * Checks if Layout Builder option in our CE display is usable.
   *
   * This is split out into a separate private method, and returns several
   * values at once, to abstract away the convoluted logic of deriving them.
   *
   * @return array
   *   Four values indicating:
   *   - Whether the "Use layout builder" option should be visible (bool)
   *   - An extra message to add either to the "Use layout builder" option
   *     description, or if the option isn't visible: this is a general message
   *     to display on top of the screen.
   *   - The view mode to use in the link to the layout edit page. (Empty if
   *     the option is invisible, or if there is no logical single layout to
   *     refer to.)
   *   - Whether the other options should always remain visible (because they
   *     are used by view modes cannot use the "Use layout builder" option.) If
   *     false, these options have no functions when "Use layout builder" is
   *     enabled.
   */
  private function layoutBuilderEnabledOptions(): array {
    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display */
    $ce_display = $this->getEntity();
    $id_bundle_prefix = $ce_display->getTargetEntityTypeId() . '.' . $ce_display->getTargetBundle() . '.';
    if ($ce_display->getMode() === 'default') {
      // Prepare: check existence of CE displays and entity view displays.
      $non_default_ce_display_ids = $this->entityTypeManager->getStorage('entity_ce_display')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('id', $id_bundle_prefix, 'STARTS_WITH')
        ->condition('status', TRUE)
        ->condition('id', $id_bundle_prefix . 'default', '<>')
        ->execute();
      $ce_displays_in_keys = array_flip($non_default_ce_display_ids);

      $display_storage = $this->entityTypeManager->getStorage('entity_view_display');
      $view_display_ids = $display_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('id', $id_bundle_prefix, 'STARTS_WITH')
        ->condition('status', TRUE)
        ->condition('id', $id_bundle_prefix . 'default', '<>')
        ->execute();
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay[] $non_default_view_displays */
      $non_default_view_displays = $display_storage->loadMultiple($view_display_ids);

      // Derive (view modes with) own displays which can/not use Layout Builder.
      $non_default_view_displays_not_using_lb = array_filter(
        $non_default_view_displays,
        fn($display) => !$display->getThirdPartySetting('layout_builder', 'enabled')
      );
      $non_default_view_displays_without_own_ce_not_using_lb = array_diff_key($non_default_view_displays_not_using_lb, $ce_displays_in_keys);

      // Get the default entity view display separately: it's also returned
      // if disabled/nonexistent in active configuration.
      $default_view_display = $this->entityDisplayRepository->getViewDisplay($ce_display->getTargetEntityTypeId(), $ce_display->getTargetBundle());

      // This default CE display can be used for building in several view modes.
      // Whether a Layout is used, is determined by this CE display's "use LB"
      // option, plus the "use LB" setting in:
      // - the default entity view display, for
      //   - "default" view mode,
      //   - any view mode that has no own entity view display AND no own CE
      //     display. This includes nonexistent view modes.
      // - their own entity view display, for any view mode where that exists
      //   AND which has no own CE display.
      if ($default_view_display->getThirdPartySetting('layout_builder', 'enabled')) {
        $enable_option = TRUE;
        $layout_link_view_mode = 'default';
        // This CE display's "use LB" option can influence building in all view
        // modes (including nonexistent ones), except:
        // 1. those with their own CE display; this speaks for itself, because
        //    they are visible in other tabs on the screen.
        // 2. those without their own CE display but with their own view display
        //    that does not have "use LB" enabled. Those are still using this
        //    CE display's other settings.
        // If the second category exists: add this info, because it is not
        // apparent.
        $extra_message = '';
        if ($non_default_view_displays_without_own_ce_not_using_lb) {
          $args = ['%modes' => $this->getViewModeLabelsFromDisplays($non_default_view_displays_without_own_ce_not_using_lb)];
          $extra_message = $non_default_ce_display_ids
            ? $this->t("This is done for all view modes that don't have their own custom element display, <em>except</em> %modes (which always use the below settings).", $args)
            : $this->t('This is done for all view modes <em>except</em> %modes (which always use the below settings).', $args);
        }
        $always_keep_other_settings_enabled = (bool) $non_default_view_displays_without_own_ce_not_using_lb;
      }
      else {
        // This CE display's "use LB" option can only influence building in view
        // modes without their own CE display but with their own view display
        // that has "use LB" enabled.
        $non_default_view_displays_using_lb = array_filter(
          $non_default_view_displays,
          fn($display) => $display->getThirdPartySetting('layout_builder', 'enabled')
        );
        $non_default_view_displays_without_own_ce_using_lb = array_diff_key($non_default_view_displays_using_lb, $ce_displays_in_keys);
        $enable_option = (bool) $non_default_view_displays_without_own_ce_using_lb;
        $layout_link_view_mode = count($non_default_view_displays_without_own_ce_using_lb) == 1
          ? current($non_default_view_displays_without_own_ce_using_lb)->getMode() : '';
        $extra_message = $enable_option ? $this->t('The default display has "use Layout Builder" disabled, so this option can only influence building custom elements for view mode: %lb_modes.', [
          '%lb_modes' => $this->getViewModeLabelsFromDisplays($non_default_view_displays_without_own_ce_using_lb),
        ]) : '';

        // This CE display's other settings are used by 'default' and all other
        // view modes without their own CE display (including nonexistent view
        // modes). If those view modes exist: extend the message for some extra
        // clarity. (Without naming them; that gets too long.)
        if ($enable_option && $non_default_view_displays_without_own_ce_not_using_lb) {
          $extra_message .= ' ' . ($non_default_ce_display_ids
            ? $this->t("Other view modes (that don't have their own custom element display) use the below settings.")
            : $this->t('Other view modes use the below settings.'));
        }
        $always_keep_other_settings_enabled = TRUE;
      }
    }
    else {
      // This non-default CE display's "use LB" option can influence building
      // (only in its own view mode), if:
      // - the corresponding entity view display exists and has use-LB enabled;
      // - it does not exist, but the default entity view display has use-LB
      //   enabled.
      // entityDisplayRepository::>getViewDisplay() cannot check if a display
      // actually exists, so do 'generic' loading.
      $displays = $this->entityTypeManager->getStorage('entity_view_display')
        ->loadMultiple([
          $id_bundle_prefix . $ce_display->getMode(),
          $id_bundle_prefix . 'default',
        ]);
      // Disabled displays are ignored. If none remain, LB is not enabled.
      $displays = array_filter(
        $displays,
        fn($display) => $display->status()
      );
      if (count($displays) > 1) {
        // Check the non-default display.
        unset($displays[$id_bundle_prefix . 'default']);
      }
      $enable_option = $displays && current($displays)->getThirdPartySetting('layout_builder', 'enabled');
      // If only the default display exists, the "edit" link should point to
      // it.
      $layout_link_view_mode = $enable_option ? current($displays)->getMode() : '';
      $extra_message = '';
      $always_keep_other_settings_enabled = FALSE;
    }
    return [$enable_option, $extra_message, $layout_link_view_mode, $always_keep_other_settings_enabled];
  }

  /**
   * Gets view modes from Entity (CE/view) displays, as comma separated string.
   */
  protected function getViewModeLabelsFromDisplays(array $displays): string {
    $view_mode_info = $this->entityDisplayRepository->getViewModes($this->entity->getTargetEntityTypeId());
    $labels = array_map(
      function ($display) use ($view_mode_info) {
        assert($display instanceof EntityDisplayInterface);
        return $view_mode_info[$display->getMode()]['label'] ?? $display->getMode();
      },
      $displays
    );
    return implode(', ', $labels);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityDisplay($entity_type_id, $bundle, $mode) {
    return $this->getCustomElementGenerator()->getEntityCeDisplay($entity_type_id, $bundle, $mode);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultPlugin($field_type) {
    return 'auto';
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    // This form only uses the "content" region for all fields.
    // Deletion is handled via Remove buttons, not regions.
    return [
      'content' => [
        'title' => $this->t('-'),
        'invisible' => TRUE,
        'message' => $this->t('No components are displayed.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRowRegion(&$row) {
    // All rows are in the 'content' region.
    // We use Remove button for deletion instead of region-based hiding.
    return 'content';
  }

  /**
   * Builds the table row structure for a single field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   A table row array.
   *
   * @todo Redo this with UI revamp in #3455435. parent:form() currently loops
   *   through field definitions, but we likely want to loop through component
   *   names instead, so that multiple components for the same fields can be
   *   set. This means the parameter definition of buildFieldRow() likely isn't
   *   good to use anymore.
   * @todo At the same time (or later?), decide whether we can support other
   *   'kinds of formatters' (like static values, #3446287) in that same loop.
   *   If not, we'll need to create some separate loop later (just like
   *   parent::form() loops through the extraFields at the moment). Ordering
   *   likely doesn't matter much, since this is going to be reordered by
   *   weight later, anyway.
   */
  protected function buildFieldRow(FieldDefinitionInterface $field_definition, array $form, FormStateInterface $form_state) {
    $display_options = NULL;
    $field_name = $field_definition->getName();
    $label = $field_definition->getLabel();
    $component_name = $this->getComponentNameFromFieldName($field_name, NULL, FALSE);
    if ($component_name) {
      $display_options = $this->entity->getComponent($component_name);

      // Disable fields without any applicable plugins.
      if (empty($this->getApplicablePluginOptions($field_definition))) {
        $this->entity->removeComponent($component_name);
        $display_options = $this->entity->getComponent($component_name);
      }
    }

    $field_row['human_name'] = [
      '#plain_text' => $label,
    ];
    $field_row['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attribute / Slot name'),
      '#title_display' => 'invisible',
      '#default_value' => $display_options['name'],
      '#size' => 20,
      '#required' => TRUE,
    ];
    $field_row['is_slot'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is Slot'),
      '#title_display' => 'invisible',
      '#default_value' => $display_options['is_slot'] ?? FALSE,
    ];
    $field_row['formatter'] = [
      '#type' => 'select',
      '#title' => $this->t('Custom element formatter'),
      '#options' => $this->getApplicablePluginOptions($field_definition),
      '#title_display' => 'invisible',
      '#default_value' => $display_options['formatter'] ?? 'auto',
      '#ajax' => [
        'callback' => '::multistepAjax',
        'wrapper' => 'field-display-overview-wrapper',
        'progress' => 'none',
      ],
      '#op' => 'formatter_change',
      '#field_name' => $field_name,
    ];
    if (!isset($field_row['formatter']['#options'][$field_row['formatter']['#default_value']])) {
      // Display warning, but do not prevent saving the value unmodified.
      $field_row['formatter']['#options'][$field_row['formatter']['#default_value']] = $this->t('Unknown: @value', ['@value' => $field_row['formatter']['#default_value']]);
      if (!$form_state->getUserInput()) {
        $this->messenger()->addWarning($this->t(
          "@label has unknown formatter %value.",
          ['@label' => $label, '%value' => $field_row['formatter']['#default_value']]
        ));
      }
    }
    $field_row += [
      '#attributes' => ['class' => ['draggable', 'tabledrag-leaf']],
      '#row_type' => 'field',
      '#region_callback' => [$this, 'getRowRegion'],
      '#js_settings' => [
        'rowHandler' => 'field',
        'defaultPlugin' => $this->getDefaultPlugin($field_definition->getType()),
      ],
      'weight' => [
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', ['@title' => $label]),
        '#title_display' => 'invisible',
        '#default_value' => $display_options['weight'] ?? '0',
        '#size' => 3,
        '#attributes' => ['class' => ['field-weight']],
      ],
      'parent_wrapper' => [
        // Somehow FieldUiTable element needs this, so keep it.
        'parent' => [
          '#type' => 'hidden',
          '#empty_value' => '',
          '#attributes' => ['class' => ['js-field-parent', 'field-parent']],
          '#parents' => ['fields', $field_name, 'parent'],
        ],
        'hidden_name' => [
          '#type' => 'hidden',
          '#default_value' => $field_name,
          '#attributes' => ['class' => ['field-name']],
        ],
      ],
    ];

    // Get the corresponding plugin object.
    // @todo Safely remove the case for !$component_name in #3446485 / second
    //   call parameter, when all rows represent components.
    try {
      $plugin = $component_name ? $this->entity->getRenderer($component_name)
        : $this->entity->getRenderer($field_name, TRUE);
    }
    catch (PluginNotFoundException) {
      $plugin = NULL;
    }

    $field_row = $this->buildFieldRowPluginForm($field_row, $field_name, $plugin, $form, $form_state);

    // Actions column contains the Remove button.
    $field_row['actions'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#name' => 'remove_' . $field_name,
      '#submit' => ['::removeFieldSubmit'],
      '#ajax' => [
        'callback' => '::removeFieldAjax',
        'wrapper' => 'field-display-overview-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return $field_row;
  }

  /**
   * Builds the plugin form into the field row.
   *
   * @param array $field_row
   *   The field form row.
   * @param string $field_name
   *   The namae of the field.
   * @param \Drupal\custom_elements\CustomElementsFieldFormatterInterface|null $plugin
   *   The formatter plugin. Passing NULL will add enough items to the row, to
   *   have any further 'cells' in the row be indented correctly.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated field form row.
   */
  private function buildFieldRowPluginForm(array $field_row, string $field_name, ?CustomElementsFieldFormatterInterface $plugin, array $form, FormStateInterface $form_state) {
    // Base button element for the various plugin settings actions.
    $base_button = [
      '#submit' => ['::multistepSubmit'],
      '#ajax' => [
        'callback' => '::multistepAjax',
        'wrapper' => 'field-display-overview-wrapper',
        'effect' => 'fade',
      ],
      '#field_name' => $field_name,
    ];

    $field_row['plugin']['settings_edit_form']['form'] = [];
    if ($plugin) {
      $subform_state = SubformState::createForSubform($field_row['plugin']['settings_edit_form']['form'], $form, $form_state);
      $field_row['plugin']['settings_edit_form']['form'] = $plugin
        ->buildConfigurationForm($field_row['plugin']['settings_edit_form']['form'], $subform_state);
    }

    if ($plugin && $form_state->get('plugin_settings_edit') == $field_name) {
      // We are currently editing this field's plugin settings. Display the
      // settings form and submit buttons.
      if ($field_row['plugin']['settings_edit_form']['form']) {
        $field_row['plugin']['#cell_attributes'] = ['colspan' => 3];
        $field_row['plugin']['settings_edit_form'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['field-plugin-settings-edit-form']],
          '#parents' => ['fields', $field_name, 'settings_edit_form'],
          'label' => [
            '#markup' => $this->t('Plugin settings'),
          ],
          'form' => $field_row['plugin']['settings_edit_form']['form'],
          'actions' => [
            '#type' => 'actions',
            'save_settings' => $base_button + [
              '#type' => 'submit',
              '#validate' => ['::validatePluginConfigurationForm'],
              '#button_type' => 'primary',
              '#name' => $field_name . '_plugin_settings_update',
              '#value' => $this->t('Update'),
              '#op' => 'update',
            ],
            'cancel_settings' => $base_button + [
              '#type' => 'submit',
              '#name' => $field_name . '_plugin_settings_cancel',
              '#value' => $this->t('Cancel'),
              '#op' => 'cancel',
                // Do not check errors for the 'Cancel' button, but make sure we
                // get the value of the 'plugin type' select.
              '#limit_validation_errors' => [['fields', $field_name, 'type']],
            ],
          ],
        ];
        $field_row['#attributes']['class'][] = 'field-plugin-settings-editing';
      }
    }
    else {
      $field_row['settings_summary'] = [];
      $field_row['settings_edit'] = [];

      // Display a summary of the current plugin settings, and (if the
      // summary is not empty) a button to edit them.
      $summary = $plugin?->settingsSummary();

      if ($summary) {
        $field_row['settings_summary'] = [
          '#type' => 'inline_template',
          '#template' => '<div class="field-plugin-summary">{{ summary|safe_join("<br />") }}</div>',
          '#context' => ['summary' => $summary],
          '#cell_attributes' => ['class' => ['field-plugin-summary-cell']],
        ];
      }

      // When the plugin provides a form, show an edit button.
      // But actually hide the form for now.
      if (!empty($field_row['plugin']['settings_edit_form']['form'])) {
        unset($field_row['plugin']['settings_edit_form']['form']);
        $field_row['settings_edit'] = $base_button + [
          '#type' => 'image_button',
          '#name' => $field_name . '_settings_edit',
          '#src' => 'core/misc/icons/787878/cog.svg',
          '#attributes' => ['class' => ['field-plugin-settings-edit'], 'alt' => $this->t('Edit')],
          '#op' => 'edit',
            // Do not check errors for the 'Edit' button, but make sure we get
            // the value of the 'plugin formatter' select.
          '#limit_validation_errors' => [['fields', $field_name, 'formatter']],
          '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
          '#suffix' => '</div>',
        ];
      }
    }
    return $field_row;
  }

  /**
   * Gets component name from field name.
   *
   * This is temporary code as long as the UI still has one row per field.
   *
   * @return string
   *   The component name.
   *
   * @todo Remove this method; at the same time, change the component name for
   *   the 'add_select' case to still be unique.
   */
  private function getComponentNameFromFieldName(string $field_name, $entity = NULL, $log = TRUE): string {
    return $field_name;
  }

  /**
   * Gets the field definitions currently configured in the CE display.
   */
  private function getConfiguredFieldDefinitions(): array {
    $field_definitions = $this->getFieldDefinitions();
    $configured_field_definitions = [];
    foreach ($this->entity->getComponents() as $component) {
      $field_name = $component['field_name'];
      if (isset($field_definitions[$field_name])) {
        $configured_field_definitions[$field_name] = $field_definitions[$field_name];
      }
    }

    return $configured_field_definitions;
  }

  /**
   * Form validation callback for plugin configuration form.
   *
   * @param array $form
   *   A nested array of form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validatePluginConfigurationForm(array &$form, FormStateInterface $form_state) {
    // The entity was not populated yet on afterBuild(), because we cannot
    // guarantee correctness of all components when they are being renamed. So,
    // temporarily set current component into cloned entity to make sure we can
    // at least validate its configuration form.
    $validate_entity = clone $this->entity;
    $field_name = $form_state->getTriggeringElement()['#field_name'];
    $component_name = $this->getComponentNameFromFieldName($field_name, $validate_entity);

    // Allow the 'triggering' formatter to validate its configuration.
    $plugin = $validate_entity->getRenderer($component_name);
    if ($plugin) {
      $plugin_form =& $form['fields'][$field_name]['plugin']['settings_edit_form']['form'];
      $subform_state = SubformState::createForSubform($plugin_form, $form, $form_state);
      $plugin->validateConfigurationForm($plugin_form, $subform_state);
    }
    else {
      // Should never happen. Not a user-friendly message.
      $form_state->setError($form['fields'][$field_name]['name'], $this->t('Cannot find renderer for component @name', ['@name' => $component_name]));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    assert($entity instanceof EntityCeDisplayInterface);
    $form_values = $form_state->getValues();
    if ($this->entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $form_values = array_diff_key($form_values, $this->entity->getPluginCollections());
    }

    // Collect data for 'regular' fields.
    // @todo Change this structure in #3446485: $form keys will be component
    //   names, not field names. Rename '#fields' (here and elsewhere)?
    foreach ($form['#fields'] as $field_name) {
      $values = $form_values['fields'][$field_name];
      $component_name = $field_name;

      $options = $entity->getComponent($component_name);
      $options['formatter'] = $values['formatter'];
      $options['field_name'] = $field_name;
      $options['name'] = $values['name'];
      $options['is_slot'] = (bool) $values['is_slot'];
      $options['weight'] = $values['weight'];

      // Update field settings only if the submit handler told us to.
      if ($form_state->get('plugin_settings_update') === $field_name) {
        $component_name = $this->getComponentNameFromFieldName($field_name);
        // getRenderer() needs basic properties to be updated.
        $entity->setComponent($component_name, $options);
        $plugin = $entity->getRenderer($component_name);
        $plugin_form =& $form['fields'][$field_name]['plugin']['settings_edit_form']['form'];
        $subform_state = SubformState::createForSubform($plugin_form, $form, $form_state);
        $plugin->submitConfigurationForm($plugin_form, $subform_state);
        // Remove some fixed variables which are passed as part of
        // configuration from configuration storage.
        // @see \Drupal\custom_elements\Entity\EntityCeDisplay::getRenderer()
        $options['configuration'] = array_diff_key(
          $plugin->getConfiguration(),
          array_flip(['field_definition', 'view_mode', 'name', 'is_slot'])
        );
        $form_state->set('plugin_settings_update', NULL);
      }

      $entity->setComponent($component_name, $options);
    }
    // Sometimes during ajax form values miss the required form fields.
    if (!empty($form_values['custom_element_name'])) {
      $entity->setCustomElementName($form_values['custom_element_name']);
    }
    if (isset($form_values['force_auto'])) {
      $entity->setForceAutoProcessing($form_values['force_auto']);
    }
    if (isset($form_values['force_layout'])) {
      $entity->setUseLayoutBuilder($form_values['force_layout']);
    }
  }

  /**
   * Submit handler for removing a field.
   *
   * @param array $form
   *   Form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function removeFieldSubmit(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    // Extract field name from button name.
    if (isset($trigger['#name']) && strpos($trigger['#name'], 'remove_') === 0) {
      $field_name = substr($trigger['#name'], strlen('remove_'));

      // Get the component name for this field.
      $component_name = $this->getComponentNameFromFieldName($field_name, $this->entity, FALSE);

      if ($component_name) {
        // Remove the component from the entity immediately.
        $this->entity->removeComponent($component_name);
      }

      $form_state->setRebuild();
    }
  }

  /**
   * AJAX callback for removing a field.
   *
   * @param array $form
   *   Form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AJAX response to replace the field table and add field dropdown.
   */
  public function removeFieldAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#field-display-overview-wrapper', $form['fields']));
    // Also update the "Add field" dropdown to show the removed field.
    $response->addCommand(new ReplaceCommand('.js-form-item-add-select', $form['add_select']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function multistepAjax($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    if (isset($trigger['#name']) && $trigger['#name'] == 'add_select') {
      // Replace the whole table. (Don't call parent, which can only handle
      // its own known '#op's.)
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#field-display-overview-wrapper', $form['fields']));
      $response->addCommand(new ReplaceCommand('.js-form-item-add-select', $form['add_select']));
    }
    elseif (isset($trigger['#op']) && $trigger['#op'] == 'formatter_change') {
      // Formatter dropdown changed. Replace the whole table.
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#field-display-overview-wrapper', $form['fields']));
    }
    else {
      $response = parent::multistepAjax($form, $form_state);
      // Remove any TabledragWarning commands to prevent "unsaved changes"
      // message that causes content to jump.
      $commands = &$response->getCommands();
      $commands = array_values(array_filter($commands, function ($command) {
        return !isset($command['command']) || $command['command'] !== 'tabledragChanged';
      }));
    }
    $response->addCommand(new ReplaceCommand('#ce-display-preview-wrapper', $this->buildPreview($form, $form_state)));

    return $response;
  }

  /**
   * Ajax handler for preview.
   *
   * @param array $form
   *   Form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Values associated with the form.
   *
   * @return Drupal\Core\Ajax\AjaxResponse
   *   Updated preview content.
   */
  public function buildPreviewAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#ce-display-preview-wrapper', $this->buildPreview($form, $form_state)));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $components = $this->entity->getComponents();
    // Components are keyed by the form's 'fixed row value'. Key them by
    // component name as defined the schema.
    $new_components = [];
    foreach ($components as $form_row_name => $component) {
      $component_name = $component['name'];
      if (isset($new_components[$component_name])) {
        // Duplicate name; the first one is more likely to be the one we want.
        // Do not cancel; just don't save.
        // @todo figure out duplicate name detection after #3446485.
        $this->messenger()->addWarning(
          $this->t('Several components have the key %name. The component for row %row_name was not saved.',
            ['%name' => $component_name, '%row_name' => $form_row_name]
          ));
        continue;
      }

      // Remove temporary form-only properties before saving.
      unset($component['name']);
      if (isset($component['region'])) {
        unset($component['region']);
      }
      $new_components[$component_name] = $component;
    }

    // Key the changed components by their intended names; remove unused keys.
    $remove_components = array_fill_keys(array_keys($components), TRUE);
    foreach ($new_components as $component_name => $component) {
      $this->entity->setComponent($component_name, $component);
      unset($remove_components[$component_name]);
    }
    foreach (array_keys($remove_components) as $key) {
      $this->entity->removeComponent($key);
    }

    return parent::save($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayModes() {
    return $this->entityDisplayRepository->getViewModes($this->entity->getTargetEntityTypeId());
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayModeOptions() {
    return $this->entityDisplayRepository->getViewModeOptions($this->entity->getTargetEntityTypeId());
  }

  /**
   * {@inheritdoc}
   */
  protected function getDisplayModesLink() {
    return [
      '#type' => 'link',
      '#title' => $this->t('Manage view modes'),
      '#url' => Url::fromRoute('entity.entity_view_mode.collection'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getOverviewUrl($mode) {
    $entity_type = $this->entityTypeManager->getDefinition($this->entity->getTargetEntityTypeId());
    return Url::fromRoute('entity.entity_ce_display.' . $this->entity->getTargetEntityTypeId() . '.view_mode', [
      'view_mode_name' => $mode,
    ] + FieldUI::getRouteBundleParameter($entity_type, $this->entity->getTargetBundle()));
  }

  /**
   * Allows all fields and not only whose display is configurable.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   The array of field definitions
   */
  protected function getFieldDefinitions() {
    return $this->entityFieldManager->getFieldDefinitions($this->entity->getTargetEntityTypeId(), $this->entity->getTargetBundle());
  }

  /**
   * {@inheritdoc}
   */
  protected function getApplicablePluginOptions(FieldDefinitionInterface $field_definition) {
    $options = $this->customElementFormatterPluginManager->getOptions($field_definition->getType());
    $applicable_options = [];
    foreach ($options as $plugin_id => $label) {
      $plugin_class = DefaultFactory::getPluginClass($plugin_id, $this->customElementFormatterPluginManager->getDefinition($plugin_id));
      try {
        if ($plugin_class::isApplicable($plugin_id, $field_definition)) {
          $applicable_options[$plugin_id] = $label;
        }
      }
      catch (\Exception $exception) {
        $this->logger('custom_elements')
          ->warning('Error when determining compatible plugins: @error', ['@error' => $exception->getMessage()]);
      }
    }
    return $applicable_options;
  }

  /**
   * Returns an array containing the table headers.
   *
   * @return array
   *   The table header.
   */
  protected function getTableHeader() {
    return [
      $this->t('Field'),
      $this->t('Key'),
      $this->t('Is Slot'),
      $this->t('Formatter'),
      $this->t('Weight'),
      // Hidden column for "parent_wrapper" element:
      '',
      ['data' => $this->t('Settings'), 'colspan' => 3],
      ['data' => $this->t('Action')],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function thirdPartySettingsForm(PluginSettingsInterface $plugin, FieldDefinitionInterface $field_definition, array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function alterSettingsSummary(array &$summary, PluginSettingsInterface $plugin, FieldDefinitionInterface $field_definition) {}

  /**
   * Builds a preview of the custom elements display.
   *
   * @return array
   *   A render array containing the preview.
   */
  protected function buildPreview($form, FormStateInterface $form_state): array {
    $preview = [
      '#type' => 'container',
      '#attributes' => ['class' => ['custom-elements-preview']],
      '#prefix' => '<div id="ce-display-preview-wrapper">',
      '#suffix' => '</div>',
    ];

    // Check if preview should be generated (only when button is clicked).
    $triggering_element = $form_state->getTriggeringElement();
    $should_generate_preview = $triggering_element
      && isset($triggering_element['#op'])
      && $triggering_element['#op'] === 'refresh_preview';

    // Show empty area if preview hasn't been requested yet.
    if (!$should_generate_preview) {
      $preview['message'] = [
        '#markup' => '<p>' . $this->t('No preview. Select a preview provider and press <em>Update</em> to generate it.') . '</p>',
      ];
      return $preview;
    }

    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display */
    $ce_display = clone $this->getEntity();
    // Update the cloned entity with current form values during AJAX callbacks.
    if ($form_state->isProcessingInput() && $triggering_element) {
      try {
        $this->copyFormValuesToEntity($ce_display, $form, $form_state);
      }
      catch (\Exception $e) {
        // If copying form values fails, use the current entity state.
        $this->logger('custom_elements')
          ->warning('Error copying values: @error', ['@error' => $e->getMessage()]);
      }
    }

    // Only generate preview if the display has components.
    $components = $ce_display->getComponents();
    if (empty($components)) {
      $preview['message'] = [
        '#markup' => '<p><em>' . $this->t('No fields configured for preview.') . '</em></p>',
      ];
      return $preview;
    }

    try {
      $entity_type_id = $ce_display->getTargetEntityTypeId();
      $bundle = $ce_display->getTargetBundle();
      $sample = $this->generateSampleEntity($entity_type_id, $bundle);

      // Get the selected preview provider.
      $preview_provider_id = $form_state->getValue(['preview', 'controls', 'preview_provider']);
      if (!$preview_provider_id) {
        $preview_provider_id = $form_state->getValue('preview_provider', 'json');
      }
      $provider = $this->previewResolver->getProviderById($preview_provider_id);
      if (!$provider) {
        // This should actually never happen.
        throw new \InvalidArgumentException(sprintf('Preview provider "%s" is not available as a configured service.', $preview_provider_id));
      }
      // Generate preview with the selected provider instance.
      $custom_element = $this->getCustomElementGenerator()
        ->generateWithCeDisplay($sample, $ce_display);
      $preview['content'] = $provider->preview($custom_element);
    }
    catch (\RuntimeException $e) {
      $preview['error'] = [
        '#markup' => '<p><em>' . $this->t('Preview generation failed: @message', ['@message' => $e->getMessage()]) . '</em></p>',
      ];
    }

    return $preview;
  }

  /**
   * Generates a sample entity for the given entity type and bundle.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle name.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface|null
   *   The sample entity or NULL if generation failed.
   */
  protected function generateSampleEntity(string $entity_type_id, string $bundle) {
    // Create entity with sample field values.
    try {
      $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type_id);
      $storage = $this->entityTypeManager->getStorage($entity_type_id);

      $values = [];

      // Set bundle if entity type has bundles.
      if ($bundle_key = $entity_type_definition->getKey('bundle')) {
        $values[$bundle_key] = $bundle;
      }
      // Create entity with basic required values.
      $sample_entity = $storage->createWithSampleValues($bundle, $values);
      // Set a temporary ID to avoid "entity cannot have a URI" errors.
      $this->setIdOnSampleEntity($sample_entity);
      // Generate sample values for all fields that might miss the value.
      $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      foreach ($field_definitions as $field_name => $field_definition) {
        // Skip read-only fields (like computed fields).
        if ($field_definition->isReadOnly()) {
          continue;
        }
        // Skip fields that are set already but make sure that id is set on
        // referenced entities.
        if (!$sample_entity->get($field_name)->isEmpty()) {
          $sample_value = $sample_entity->get($field_name)->getValue();
          foreach ($sample_value as $sample_value_item) {
            if (isset($sample_value_item['entity']) && $sample_value_item['entity'] instanceof EntityInterface) {
              $this->setIdOnSampleEntity($sample_value_item['entity']);
            }
          }
          continue;
        }
        try {
          $field_item_class = $field_definition->getItemDefinition()->getClass();
          if (method_exists($field_item_class, 'generateSampleValue')) {
            $sample_value = $field_item_class::generateSampleValue($field_definition);
            if (isset($sample_value['entity']) && $sample_value['entity'] instanceof EntityInterface) {
              $this->setIdOnSampleEntity($sample_value['entity']);
            }
            if (!empty($sample_value)) {
              $sample_entity->set($field_name, $sample_value);
            }
          }
        }
        catch (\Exception $e) {
          // Use default values if available, otherwise generate sample values.
          if ($default_value = $field_definition->getDefaultValueLiteral()) {
            $sample_entity->set($field_name, $default_value);
          }
          else {
            continue;
          }
        }
      }
      return $sample_entity;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Set id on sample entity if it is not set.
   *
   * @param \Drupal\Core\Entity\EntityInterface $sample_entity
   *   Sample entity.
   *
   * @return void
   *   Sample entity with id set.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Random\RandomException
   */
  protected function setIdOnSampleEntity(EntityInterface $sample_entity) {
    $entity_type_definition = $this->entityTypeManager->getDefinition($sample_entity->getEntityTypeId());
    if ($entity_type_definition->hasKey('id')) {
      $id_key = $entity_type_definition->getKey('id');
      if ($sample_entity->get($id_key)->isEmpty()) {
        $sample_entity->set($id_key, random_int(999999, 9999999));
      }
    }
  }

}
