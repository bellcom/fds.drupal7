<?php

/**
 * Implements hook_form_FORM_ID_alter().
 */
function fds_base_theme_form_system_theme_settings_alter(&$form, $form_state, $form_id = NULL) {

  // Vertical tabs
  $form['options'] = [
    '#type' => 'vertical_tabs',
    '#default_tab' => 'main',
    '#weight' => '-20',
    '#prefix' => '<h2><small>' . t('FDS settings') . '</small></h2>',
    '#title' => t('FDS settings'),
  ];

  /*
  |--------------------------------------------------------------------------
  | Header.
  |--------------------------------------------------------------------------
  */

  // Fieldset
  $form['options']['header'] = [
    '#type' => 'fieldset',
    '#title' => t('Header'),
  ];

  // Authority details - show.
  $form['options']['header']['authority_details']['authority_details_show'] = [
    '#type' => 'checkbox',
    '#title' => t('Show authority details'),
    '#default_value' => theme_get_setting('authority_details_show'),
  ];

  // Authority details - name.
  $form['options']['header']['authority_details']['authority_details_name'] = [
    '#type' => 'textfield',
    '#title' => t('Name of authority'),
    '#description' => t('Ex. Myndighedsnavn'),
    '#default_value' => theme_get_setting('authority_details_name'),
    '#states' => [
      'visible' => [
        ':input[name="authority_details_show"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ];

  // Authority details - text.
  $form['options']['header']['authority_details']['authority_details_text'] = [
    '#type' => 'textfield',
    '#title' => t('Text'),
    '#description' => t('Ex. Support:'),
    '#default_value' => theme_get_setting('authority_details_text'),
    '#states' => [
      'visible' => [
        ':input[name="authority_details_show"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ];

  // Authority details - phone number - system.
  $form['options']['header']['authority_details']['authority_details_phone_system'] = [
    '#type' => 'textfield',
    '#title' => t('Phone no. - technical format'),
    '#description' => t('Remember language code. Ex: 004512345678'),
    '#default_value' => theme_get_setting('authority_details_phone_system'),
    '#states' => [
      'visible' => [
        ':input[name="authority_details_show"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ];

  // Authority details - phone number - readable.
  $form['options']['header']['authority_details']['authority_details_phone_readable'] = [
    '#type' => 'textfield',
    '#title' => t('Phone no. - readable format'),
    '#description' => t('The number that is visible for the user. Can contain spaces. Ex. +45 12 34 56 78'),
    '#default_value' => theme_get_setting('authority_details_phone_readable'),
    '#states' => [
      'visible' => [
        ':input[name="authority_details_show"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ];
}
