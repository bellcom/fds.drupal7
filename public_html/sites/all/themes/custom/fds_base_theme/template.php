<?php
include( dirname(__FILE__) . '/include/common.inc');
include( dirname(__FILE__) . '/include/helpers.inc');
include( dirname(__FILE__) . '/include/menu.inc');
include( dirname(__FILE__) . '/include/settings.inc');


/**
 * Implements hook_element_info_alter().
 */
function fds_base_theme_element_info_alter(&$info) {
  global $theme_key;

  $cid = "theme_registry:bootstrap:element_info";
  $cached = array();
  if (($cache = cache_get($cid)) && !empty($cache->data)) {
    $cached = $cache->data;
  }

  $themes = _bootstrap_get_base_themes($theme_key, TRUE);
  foreach ($themes as $theme) {
    if (!isset($cached[$theme])) {
      $cached[$theme] = array();
      foreach (array_keys($info) as $type) {
        $element = array();

        // Replace fieldset theme implementations with bootstrap_panel.
        if (!empty($info[$type]['#theme']) && $info[$type]['#theme'] === 'fieldset') {
          $element['#bootstrap_replace']['#theme'] = 'bootstrap_panel';
        }
        if (!empty($info[$type]['#theme_wrappers']) && array_search('fieldset', $info[$type]['#theme_wrappers']) !== FALSE) {
          $element['#bootstrap_replace']['#theme_wrappers']['fieldset'] = 'bootstrap_panel';
        }

        // Setup a default "icon" variable. This allows #icon to be passed
        // to every template and theme function.
        // @see https://www.drupal.org/node/2219965
        $element['#icon'] = NULL;
        $element['#icon_position'] = 'before';

        $properties = array(
          '#process' => array(
            'form_process',
            'form_process_' . $type,
          ),
          '#pre_render' => array(
            'pre_render',
            'pre_render_' . $type,
          ),
        );
        foreach ($properties as $property => $callbacks) {
          foreach ($callbacks as $callback) {
            $function = $theme . '_' . $callback;
            if (function_exists($function)) {
              // Replace direct core function correlation.
              if (!empty($info[$type][$property]) && array_search($callback, $info[$type][$property]) !== FALSE) {
                $element['#bootstrap_replace'][$property][$callback] = $function;
              }
              // Check for a "form_" prefix instead (for #pre_render).
              elseif (!empty($info[$type][$property]) && array_search('form_' . $callback, $info[$type][$property]) !== FALSE) {
                $element['#bootstrap_replace'][$property]['form_' . $callback] = $function;
              }
              // Otherwise, append the function.
              else {
                $element[$property][] = $function;
              }
            }
          }
        }
        $cached[$theme][$type] = $element;
      }

      // Cache the element information.
      cache_set($cid, $cached);
    }

    // Merge in each theme's cached element info.
    $info = _bootstrap_element_info_array_merge($info, $cached[$theme]);
  }
}

/**
 * Implements hook_pre_render().
 */
function fds_base_theme_pre_render($element) {

  // Only add the "form-control" class to supported theme hooks.
  $theme_hooks = array(
    'password',
    'select',
    'textarea',
    'textfield',
  );

  // Additionally, support some popular 3rd-party modules that don't follow
  // standards by creating custom theme hooks to use in their element types.
  // Go ahead and merge in the theme hooks as a start since most elements mimic
  // their theme hook counterparts as well.
  $types = array_merge($theme_hooks, array(
    // Elements module (HTML5).
    'date',
    'datefield',
    'email',
    'emailfield',
    'number',
    'numberfield',
    'range',
    'rangefield',
    'search',
    'searchfield',
    'tel',
    'telfield',
    'url',
    'urlfield',

    // Webform module.
    'webform_email',
    'webform_number',
  ));

  // Determine element theme hook.
  $theme = !empty($element['#theme']) ? $element['#theme'] : FALSE;

  // Handle array of theme hooks, just use first one (rare, but could happen).
  if (is_array($theme)) {
    $theme = array_shift($theme);
  }

  // Remove any suggestions.
  $parts = explode('__', $theme);
  $theme = array_shift($parts);

  // Determine element type.
  $type = !empty($element['#type']) ? $element['#type'] : FALSE;

  // Add necessary classes for specific element types/theme hooks.
  if (($theme && in_array($theme, $theme_hooks)) || ($type && in_array($type, $types)) || ($type === 'file' && empty($element['#managed_file']))) {
    $element['#attributes']['class'][] = 'form-input';
  }
  if ($type === 'machine_name') {
    $element['#wrapper_attributes']['class'][] = 'form-inline';
  }

  // Add smart descriptions to the element, if necessary.
  //bootstrap_element_smart_description($element);

  // Return the modified element.
  return $element;
}

/**
 * Returns HTML to wrap child elements in a container.
 */
function fds_base_theme_container(array $variables) {
  $element = $variables['element'];

  // Ensure #attributes is set.
  $element += array('#attributes' => array());

  // Special handling for form elements.
  if (isset($element['#array_parents'])) {
    // Assign an html ID.
    if (!isset($element['#attributes']['id'])) {
      $element['#attributes']['id'] = $element['#id'];
    }

    // Core's "form-wrapper" class is required for states.js to function.
    $element['#attributes']['class'][] = 'form-wrapper';

    // Add Bootstrap "form-group" class.
    if (!isset($element['#form_group']) || !!$element['#form_group']) {
      $element['#attributes']['class'][] = 'form-group';
    }
  }

  return '<div' . drupal_attributes($element['#attributes']) . '>' . $element['#children'] . '</div>';
}
/**
 * Returns HTML for a form element.
 *
 * Each form element is wrapped in a DIV container having the following CSS
 * classes:
 * - form-item: Generic for all form elements.
 * - form-type-#type: The internal element #type.
 * - form-item-#name: The internal form element #name (usually derived from the
 *   $form structure and set via form_builder()).
 * - form-disabled: Only set if the form element is #disabled.
 *
 * In addition to the element itself, the DIV contains a label for the element
 * based on the optional #title_display property, and an optional #description.
 *
 * The optional #title_display property can have these values:
 * - before: The label is output before the element. This is the default.
 *   The label includes the #title and the required marker, if #required.
 * - after: The label is output after the element. For example, this is used
 *   for radio and checkbox #type elements as set in system_element_info().
 *   If the #title is empty but the field is #required, the label will
 *   contain only the required marker.
 * - invisible: Labels are critical for screen readers to enable them to
 *   properly navigate through forms but can be visually distracting. This
 *   property hides the label for everyone except screen readers.
 * - attribute: Set the title attribute on the element to create a tooltip
 *   but output no label element. This is supported only for checkboxes
 *   and radios in form_pre_render_conditional_form_element(). It is used
 *   where a visual label is not needed, such as a table of checkboxes where
 *   the row and column provide the context. The tooltip will include the
 *   title and required marker.
 *
 * If the #title property is not set, then the label and any required marker
 * will not be output, regardless of the #title_display or #required values.
 * This can be useful in cases such as the password_confirm element, which
 * creates children elements that have their own labels and required markers,
 * but the parent element should have neither. Use this carefully because a
 * field without an associated label can cause accessibility challenges.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #title_display, #description, #id, #required,
 *     #children, #type, #name.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see theme_form_element()
 *
 * @ingroup theme_functions
 */
function fds_base_theme_form_element(array &$variables) {
  $element = &$variables['element'];
  $name = !empty($element['#name']) ? $element['#name'] : FALSE;
  $type = !empty($element['#type']) ? $element['#type'] : FALSE;
  $wrapper = isset($element['#form_element_wrapper']) ? !!$element['#form_element_wrapper'] : TRUE;
  $form_group = isset($element['#form_group']) ? !!$element['#form_group'] : $wrapper && $type && $type !== 'hidden';
  $checkbox = $type && $type === 'checkbox';
  $radio = $type && $type === 'radio';

  // Create an attributes array for the wrapping container.
  if (empty($element['#wrapper_attributes'])) {
    $element['#wrapper_attributes'] = array();
  }
  $wrapper_attributes = &$element['#wrapper_attributes'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add wrapper ID for 'item' type.
  if ($type && $type === 'item' && isset($element['#markup']) && !empty($element['#id'])) {
    $wrapper_attributes['id'] = $element['#id'];
  }

  // Check for errors and set correct error class.
  if ((isset($element['#parents']) && form_get_error($element) !== NULL) || (!empty($element['#required']) && (!isset($element['#value']) || $element['#value'] === '') && bootstrap_setting('forms_required_has_error'))) {
    $wrapper_attributes['class'][] = 'has-error';
  }

  // Add necessary classes to wrapper container.
  $wrapper_attributes['class'][] = 'form-item';
  if ($name) {
    $wrapper_attributes['class'][] = 'form-item-' . drupal_html_class($name);
  }
  if ($type) {
    $wrapper_attributes['class'][] = 'form-type-' . drupal_html_class($type);
  }
  if (!empty($element['#attributes']['disabled'])) {
    $wrapper_attributes['class'][] = 'form-disabled';
  }
  if (!empty($element['#autocomplete_path']) && drupal_valid_path($element['#autocomplete_path'])) {
    $wrapper_attributes['class'][] = 'form-autocomplete';
  }

  // Checkboxes and radios do no receive the 'form-group' class, instead they
  // simply have their own classes.
  if ($checkbox || $radio) {
    $wrapper_attributes['class'][] = drupal_html_class($type);
  }
  elseif ($form_group) {
    $wrapper_attributes['class'][] = 'form-group';
  }

  // Create a render array for the form element.
  $build = array(
    '#form_group' => $form_group,
    '#attributes' => $wrapper_attributes,
  );

  if ($wrapper) {
    $build['#theme_wrappers'] = array('container__form_element');

    // Render the label for the form element.
    /* @noinspection PhpUnhandledExceptionInspection */
    $build['label'] = array(
      '#markup' => theme('form_element_label', $variables),
      '#weight' => $element['#title_display'] === 'before' ? 0 : 2,
    );
  }

  // Checkboxes and radios render the input element inside the label. If the
  // element is neither of those, then the input element must be rendered here.
  if (!$checkbox && !$radio) {
    $prefix = isset($element['#field_prefix']) ? $element['#field_prefix'] : '';
    $suffix = isset($element['#field_suffix']) ? $element['#field_suffix'] : '';
    if ((!empty($prefix) || !empty($suffix)) && (!empty($element['#input_group']) || !empty($element['#input_group_button']))) {
      if (!empty($element['#field_prefix'])) {
        $prefix = '<span class="input-group-' . (!empty($element['#input_group_button']) ? 'btn' : 'addon') . '">' . $prefix . '</span>';
      }
      if (!empty($element['#field_suffix'])) {
        $suffix = '<span class="input-group-' . (!empty($element['#input_group_button']) ? 'btn' : 'addon') . '">' . $suffix . '</span>';
      }

      // Add a wrapping container around the elements.
      $input_group_attributes = &_fds_base_theme_get_attributes($element, 'input_group_attributes');
      $input_group_attributes['class'][] = 'input-group';
      $prefix = '<div' . drupal_attributes($input_group_attributes) . '>' . $prefix;
      $suffix .= '</div>';
    }

    // Build the form element.
    $build['element'] = array(
      '#markup' => $element['#children'],
      '#prefix' => !empty($prefix) ? $prefix : NULL,
      '#suffix' => !empty($suffix) ? $suffix : NULL,
      '#weight' => 1,
    );
  }

  // Construct the element's description markup.
  if (!empty($element['#description'])) {
    $build['description'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('help-block'),
      ),
      '#weight' => isset($element['#description_display']) && $element['#description_display'] === 'before' ? 0 : 20,
      0 => array('#markup' => filter_xss_admin($element['#description'])),
    );
  }

  // Render the form element build array.
  return drupal_render($build);
}

/**
 * Returns HTML for a form element label and required marker.
 *
 * Form element labels include the #title and a #required marker. The label is
 * associated with the element itself by the element #id. Labels may appear
 * before or after elements, depending on theme_form_element() and
 * #title_display.
 *
 * This function will not be called for elements with no labels, depending on
 * #title_display. For elements that have an empty #title and are not required,
 * this function will output no label (''). For required elements that have an
 * empty #title, this will output the required marker alone within the label.
 * The label will use the #id to associate the marker with the field that is
 * required. That is especially important for screenreader users to know
 * which field is required.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #required, #title, #id, #value, #description.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see theme_form_element_label()
 *
 * @ingroup theme_functions
 */
function fds_base_theme_form_element_label(array &$variables) {
  $element = $variables['element'];

  // Extract variables.
  $output = '';

  $title = !empty($element['#title']) ? filter_xss_admin($element['#title']) : '';

  // Only show the required marker if there is an actual title to display.
  $marker = array('#theme' => 'form_required_marker', '#element' => $element);
  if ($title && $required = !empty($element['#required']) ? drupal_render($marker) : '') {
    $title .= ' ' . $required;
  }

  $display = isset($element['#title_display']) ? $element['#title_display'] : 'before';
  $type = !empty($element['#type']) ? $element['#type'] : FALSE;
  $checkbox = $type && $type === 'checkbox';
  $radio = $type && $type === 'radio';

  // Immediately return if the element is not a checkbox or radio and there is
  // no label to be rendered.
  if (!$checkbox && !$radio && ($display === 'none' || !$title)) {
    return '';
  }

  // Retrieve the label attributes array.
  $attributes = &_fds_base_theme_get_attributes($element, 'label_attributes');

  // Add Bootstrap label class.
  $attributes['class'][] = 'form-label';

  // Add the necessary 'for' attribute if the element ID exists.
  if (!empty($element['#id'])) {
    $attributes['for'] = $element['#id'];
  }

  // Checkboxes and radios must construct the label differently.
  if ($checkbox || $radio) {
    if ($display === 'before') {
      $output .= $title;
    }
    elseif ($display === 'none' || $display === 'invisible') {
      $output .= '<span class="element-invisible">' . $title . '</span>';
    }
    // Inject the rendered checkbox or radio element inside the label.
    if (!empty($element['#children'])) {
      $output .= $element['#children'];
    }
    if ($display === 'after') {
      $output .= $title;
    }
  }
  // Otherwise, just render the title as the label.
  else {
    // Show label only to screen readers to avoid disruption in visual flows.
    if ($display === 'invisible') {
      $attributes['class'][] = 'element-invisible';
    }
    $output .= $title;
  }

  // The leading whitespace helps visually separate fields from inline labels.
  return ' <label' . drupal_attributes($attributes) . '>' . $output . "</label>\n";
}

/**
 * Implements hook_css_alter().
 */

function fds_base_theme_css_alter(&$css) {

  // Remove default implementation of alerts.
  unset($css[drupal_get_path('module','system').'/system.messages.css']);
}

/**
 * Implements theme_preprocess_html().
 */
function fds_base_theme_preprocess_html(&$variables) {
  $theme_path = path_to_theme();

  // Add javascript files
  drupal_add_js($theme_path . '/dist/js/dkfds.js',
    [
      'type' => 'file',
      'scope' => 'footer',
      'group' => JS_THEME,
    ]);
}

/*
 * Implements theme_preprocess_page().
 */
function fds_base_theme_preprocess_page(&$variables) {
  $primary_navigation_name = variable_get('menu_main_links_source', 'main-menu');
  $secondary_navigation_name = variable_get('menu_secondary_links_source', 'user-menu');

  // Navigation.
  $variables['navigation__primary'] = _fds_base_theme_generate_menu($primary_navigation_name, 'header_primary', 2);
  $variables['navigation__secondary'] = _fds_base_theme_generate_menu($secondary_navigation_name, 'header_secondary', 3);

  // Add information about the number of sidebars.
  if (!empty($variables['page']['content__sidebar_left']) && !empty($variables['page']['content__sidebar_right'])) {
    $variables['content_column_class'] = ' class="col-12 col-lg-6"';
  }
  elseif (!empty($variables['page']['content__sidebar_left']) || !empty($variables['page']['content__sidebar_right'])) {
    $variables['content_column_class'] = ' class="col-12 col-lg-9"';
  }
  else {
    $variables['content_column_class'] = ' class="col-12"';
  }

  // Theme settings
  $variables['theme_settings'] = _fds_base_theme_collect_theme_settings();
}

/**
 * Returns HTML for status and/or error messages, grouped by type.
 *
 * An invisible heading identifies the messages for assistive technology.
 * Sighted users see a colored box. See http://www.w3.org/TR/WCAG-TECHS/H69.html
 * for info.
 *
 * @param array $variables
 *   An associative array containing:
 *   - display: (optional) Set to 'status' or 'error' to display only messages
 *     of that type.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see theme_status_messages()
 *
 * @ingroup theme_functions
 */
function fds_base_theme_status_messages(array $variables) {
  $display = $variables['display'];
  $output = '';

  $status_heading = array(
    'status' => t('Status message'),
    'error' => t('Error message'),
    'warning' => t('Warning message'),
    'info' => t('Informative message'),
  );

  // Map Drupal message types to their corresponding Bootstrap classes.
  // @see http://twitter.github.com/bootstrap/components.html#alerts
  $status_class = array(
    'status' => 'success',
    'error' => 'error',
    'warning' => 'warning',
    'info' => 'info',
  );

  // Retrieve messages.
  $message_list = drupal_get_messages($display);

  // Allow the disabled_messages module to filter the messages, if enabled.
  if (module_exists('disable_messages') && variable_get('disable_messages_enable', '1')) {
    $message_list = disable_messages_apply_filters($message_list);
  }

  foreach ($message_list as $type => $messages) {
    $class = (isset($status_class[$type])) ? ' alert-' . $status_class[$type] : '';
    $label = filter_xss_admin($status_heading[$type]);
    $output .= "<div class=\"alert alert--show-icon has-close$class messages $type\" role=\"alert\" aria-label=\"$label\">\n";

    $output .= "<div class=\"alert-body\">";

    // Heading.
    $output .= '<p class="alert-heading pr-7">';
    $output .=   filter_xss_admin(reset($messages));
    $output .= '</p>';

    // Close button.
    $output .= '<a
                href="javascript:void(0);"
                class="alert-close"><svg class="icon-svg" aria-hidden="true" focusable="false" tabindex="-1"><use xlink:href="#close"></use></svg>Luk</a>';

    // Content.
    if (count($messages) > 1) {
      $output .= " <p class='alert-text'><ul>\n";

      foreach ($messages as $message) {
        $output .= '  <li>' . filter_xss_admin($message) . "</li>\n";
      }

      $output .= " </ul></p>\n";
    }

    $output .= "</div></div>\n";
  }

  return $output;
}

function fds_base_theme_preprocess_region(array &$variables) {
  $region = $variables['region'];
  $classes = &$variables['classes_array'];

  // Content region.
  if ($region === 'content__center') {
    $variables['theme_hook_suggestions'][] = 'region__no_wrapper';
  }
  // Help region.
  elseif ($region === 'help' && !empty($variables['content'])) {
    $classes[] = 'alert';
    $classes[] = 'alert-info';
    $classes[] = 'messages';
    $classes[] = 'info';
  }
}

/**
 * Bootstrap theme wrapper function for the primary menu links.
 */
function fds_base_theme_menu_tree__header_primary(array &$variables) {
  return '<ul class="nav-primary">' . $variables['tree'] . '</ul>';
}

/**
 * Returns HTML for a menu link and submenu.
 */
function fds_base_theme_menu_link__header_primary(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';
  $link = '';
  $generate_link = TRUE;
  $link_class = array();

  // @TODO - current level
  // --- https://drupal.stackexchange.com/questions/32873/how-to-theme-only-top-level-menu
  // If we are on second level or below, we need to add other classes to the list items.

  // The navbar.
  if ($element['#original_link']['depth'] > 1) {

    // Has a dropdown menu
    if ($element['#below']) {

      if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
        $sub_menu = drupal_render($element['#below']);
      }
      elseif ((!empty($element['#original_link']['depth']))) {

        // Add our own wrapper.
        unset($element['#below']['#theme_wrappers']);
        $sub_menu = '<ul>' . drupal_render($element['#below']) . '</ul>';

        // Generate as dropdown.
        $element['#localized_options']['html'] = TRUE;
      }
    }
  }

  // Inside dropdown menu.
  else {
    $link_class[] = 'nav-link';

    // Has a dropdown menu.
    if ($element['#below']) {

      if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
        $sub_menu = drupal_render($element['#below']);
      }
      elseif ((!empty($element['#original_link']['depth']))) {
        $generate_link = FALSE;

        // If this item is active and/or in the active trail, add necessary classes.
        $wantedClasses = array(
          'active' => '',
          'trail' => 'current',
        );
        $button_active_classes = _fds_base_theme_in_active_trail($element['#href'], $wantedClasses);
        $button_class = implode(' ', $button_active_classes);

        // Add our own wrapper.
        unset($element['#below']['#theme_wrappers']);
        $sub_menu = '<div class="overflow-menu">';
        $sub_menu .=   '<button class="' . $button_class . ' button-overflow-menu js-dropdown js-dropdown--responsive-collapse" data-js-target="#headeroverflow_' . $element['#original_link']['mlid'] . '" aria-haspopup="true" aria-expanded="false">';
        $sub_menu .=     '<span>' . $element['#title'] . '</span>';
        $sub_menu .=   '</button>';
        $sub_menu .=   '<div class="overflow-menu-inner" id="headeroverflow_' . $element['#original_link']['mlid'] . '" aria-hidden="true">';
        $sub_menu .=     '<ul class="overflow-list">' . drupal_render($element['#below']) . '</ul>';
        $sub_menu .=   '</div>';
        $sub_menu .= '</div>';

        // Generate as dropdown.
        $element['#localized_options']['html'] = TRUE;
      }
    }
  }

  // If this item is active and/or in the active trail, add necessary classes.
  $wantedClasses = array(
    'active' => 'current',
    'trail' => 'current',
  );
  $active_classes = _fds_base_theme_in_active_trail($element['#href'], $wantedClasses);

  if (!empty($link_class)) {
    $link_class = array_merge($link_class, $active_classes);
  }
  else {
    $link_class = $active_classes;
  }

  if ($generate_link) {
    $options = array();
    $options['html'] = TRUE;
    $options['attributes']['class'] = array();

    if (isset($element['#localized_options']['attributes']['title'])) {
      $options['attributes']['title'] = $element['#localized_options']['attributes']['title'];
    }

    if ($link_class) {
      $options['attributes']['class'] = $link_class;
    }

    if ($element['#original_link']['depth'] > 1) {
      $link = l($element['#title'], $element['#href'], $options);
    }
    else {
      $link = l('<span>' . $element['#title'] . '</span>', $element['#href'], $options);
    }
  }

  return '<li>' . $link . $sub_menu . "</li>\n";
}

/**
 * Bootstrap theme wrapper function for the secondary menu links.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function fds_base_theme_menu_tree__secondary(array &$variables) {
  return '<ul class="menu nav navbar-nav secondary">' . $variables['tree'] . '</ul>';
}

/**
 * Bootstrap theme wrapper function for the primary menu links.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function fds_base_theme_menu_tree(array &$variables) {
  return '<nav><ul class="sidenav-list">' . $variables['tree'] . '</ul></nav>';
}

/*
 * Implements theme_menu_link().
 */
function fds_base_theme_menu_link(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if ($element['#below']) {

    // Prevent dropdown functions from being added to management menu so it
    // does not affect the navbar module.
    if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
      $sub_menu = drupal_render($element['#below']);
    }

    elseif ((!empty($element['#original_link']['depth']))) {

      // Add our own wrapper.
      unset($element['#below']['#theme_wrappers']);

      // Submenu classes
      $sub_menu = ' <ul class="sidenav-sub_list">' . drupal_render($element['#below']) . '</ul>';
    }
  }

  // If this item is active and/or in the active trail, add necessary classes.
  $wantedClasses = array(
    'active' => 'active',
    'trail' => 'current',
  );
  $link_item['class'] = _fds_base_theme_in_active_trail($element['#href'], $wantedClasses);

  $link_text = $element['#title'];
  if (isset($element['#localized_options']['attributes']['title'])) {
    $link_text = $element['#title'] . '<span class="sidenav-information">' . $element['#localized_options']['attributes']['title'] . '</span> ';
  }

  $options = array();
  $options['html'] = true;
  $options['attributes']['class'] = array();

  $output = l($link_text, $element['#href'], $options);

  return '<li' . drupal_attributes($link_item) . '>' . $output . $sub_menu . "</li>\n";
}
