<?php

/**
 * @file
 * Contains \Drupal\unlimited_number\Plugin\Field\FieldWidget\UnlimitedNumberWidget.
 */

namespace Drupal\unlimited_number\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Plugin implementation of the 'unlimited_number' widget.
 *
 * @FieldWidget(
 *   id = "unlimited_number",
 *   label = @Translation("Unlimited or Number"),
 *   field_types = {
 *     "integer",
 *   }
 * )
 */
class UnlimitedNumberWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label_unlimited' => t('Unlimited'),
      'label_number' => t('Limited'),
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['label_unlimited'] = [
      '#type' => 'textfield',
      '#title' => t('Unlimited Label'),
      '#default_value' => $this->getSetting('label_unlimited'),
      '#description' => t('Text that will be used for the unlimited radio.'),
    ];

    $element['label_number'] = [
      '#type' => 'textfield',
      '#title' => t('Number Label'),
      '#default_value' => $this->getSetting('label_number'),
      '#description' => t('Text that will be used for the number radio.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;
    $unlimited = empty($value);
    $parents = [$items->getName(), $delta, 'unlimited_number'];

    $form_element['unlimited_number'] = $element + [
      '#type' => 'radios',
      '#options' => NULL,
      '#title' => $element['#title'],
      '#description' => $element['#description'],
      '#tree' => FALSE,
    ];

    $form_element['unlimited_number']['unlimited']['radio'] = [
      '#type' => 'radio',
      '#title' => SafeMarkup::checkPlain($this->getSetting('label_unlimited')),
      '#return_value' => 'unlimited',
      '#parents' => $parents,
      '#default_value' => isset($value) && $unlimited,
    ];

    $form_element['unlimited_number']['limited'] = [
      '#prefix' => '<div class="form-item container-inline">',
      '#suffix' => '</div>',
    ];

    $form_element['unlimited_number']['limited']['radio'] = [
      '#type' => 'radio',
      '#title' => SafeMarkup::checkPlain($this->getSetting('label_number')),
      '#return_value' => 'limited',
      '#parents' => $parents,
      '#default_value' => isset($value) && !$unlimited,
    ];

    $number_element = parent::formElement($items, $delta, [], $form, $form_state);
    if ($unlimited) {
      $number_element['value']['#default_value'] = '';
    }
    $form_element['unlimited_number']['limited']['value'] = $number_element['value'] + [
      '#parents' => [$items->getName(), $delta, 'number'],
    ];

    return $form_element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = [];
    foreach ($values as $value) {
      if ($value['unlimited_number'] == 'unlimited') {
        $new_values[]['value'] = 0;
      }
      else {
        $new_values[]['value'] = $value['number'];
      }
    }
    return $new_values;
  }

}
