<?php
include( dirname(__FILE__) . '/include/helpers.inc');
include( dirname(__FILE__) . '/include/menu.inc');
include( dirname(__FILE__) . '/include/settings.inc');

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
