<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Datetime\TimeZoneFormHelper;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementation of the 'timestamp' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "timestamp",
 *   label = @Translation("Formatted date"),
 *   field_types = {
 *     "timestamp",
 *     "created",
 *     "changed"
 *   },
 *   weight = -9
 * )
 */
class TimestampCeFieldFormatter extends RawCeFieldFormatter {

  /**
   * Used to specify a date format that is customizable by user.
   *
   * @var string
   */
  protected const CUSTOM_DATE_FORMAT = 'custom';

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date format entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormatStorage;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->dateFormatStorage = $container->get('entity_type.manager')->getStorage('date_format');
    $instance->time = $container->get('datetime.time');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldItemListValue(FieldItemListInterface $items): array {
    foreach ($items as $item) {
      $values[] = $this->dateFormatter->format(
        $item->value,
        $this->configuration['date_format'],
        $this->configuration['custom_date_format'],
        $this->configuration['timezone'] ?: NULL
      );
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'date_format' => 'medium',
      'custom_date_format' => '',
      'timezone' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $date_formats = [];
    foreach ($this->dateFormatStorage->loadMultiple() as $machine_name => $value) {
      $date_formats[$machine_name] = $this->t('@name format: @date', [
        '@name' => $value->label(),
        '@date' => $this->dateFormatter->format($this->time->getRequestTime(),
          $machine_name),
      ]);
    }
    $date_formats[static::CUSTOM_DATE_FORMAT] = $this->t('Custom');

    $form['date_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format'),
      '#options' => $date_formats,
      '#default_value' => $this->configuration['date_format'],
    ];

    $form['custom_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom date format'),
      '#description' => $this->t('See <a href="https://www.php.net/manual/datetime.format.php#refsect1-datetime.format-parameters" target="_blank">the documentation for PHP date formats</a>.'),
      '#default_value' => $this->configuration['custom_date_format'],
      '#states' => $this->buildStates(['date_format'], [
        'value' => static::CUSTOM_DATE_FORMAT,
      ]),
    ];

    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Time zone'),
      '#options' => ['' => $this->t('- Default site/user time zone -')] + TimeZoneFormHelper::getOptionsListByRegion(),
      '#default_value' => $this->configuration['timezone'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['date_format'] = $form_state->getValue('date_format');
    $this->configuration['custom_date_format'] = $form_state->getValue('custom_date_format');
    $this->configuration['timezone'] = $form_state->getValue('timezone');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $date_format = $this->configuration['date_format'];
    $date_format = $date_format === static::CUSTOM_DATE_FORMAT ? $this->configuration['custom_date_format'] : $date_format;
    $summary[] = $this->t('Date format: @date_format', ['@date_format' => $date_format]);
    return $summary;
  }

  /**
   * Builds the #states key for form elements.
   *
   * @param string[] $path
   *   The remote element path.
   * @param array $conditions
   *   The conditions to be checked.
   *
   * @return array[]
   *   The #states array.
   */
  protected function buildStates(array $path, array $conditions): array {
    $path = '[' . implode('][', $path) . ']';
    return [
      'visible' => [
        [
          ":input[name='fields[{$this->getFieldDefinition()->getName()}][settings_edit_form][form]$path']" => $conditions,
        ],
      ],
    ];
  }

}
