<?php
include( dirname(__FILE__) . '/include/helpers.inc');
include( dirname(__FILE__) . '/include/menu.inc');
include( dirname(__FILE__) . '/include/settings.inc');

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
  $i18n = module_exists('i18n_menu');
  $primary_navigation_name = variable_get('menu_main_links_source', 'main-menu');
  $secondary_navigation_name = variable_get('menu_secondary_links_source', 'user-menu');

  // Navigation - primary.
  $variables['navigation__primary'] = FALSE;
  if ($variables['main_menu']) {
    $tree = menu_tree_page_data(variable_get('menu_main_links_source', $primary_navigation_name));

    if ($i18n) {
      $tree = i18n_menu_localize_tree($tree);
    }

    $variables['navigation__primary'] = menu_tree_output($tree);
    $variables['navigation__primary']['#theme_wrappers'] = array('menu_tree__primary');
  }

  // Navigation - secondary.
  $variables['navigation__secondary'] = FALSE;
  if ($variables['main_menu']) {
    $tree = menu_tree_page_data(variable_get('menu_main_links_source', $secondary_navigation_name));

    if ($i18n) {
      $tree = i18n_menu_localize_tree($tree);
    }

    $variables['navigation__secondary'] = menu_tree_output($tree);
    $variables['navigation__secondary']['#theme_wrappers'] = array('menu_tree__secondary');
  }

  // Theme settings
  $variables['theme_settings'] = _fds_base_theme_collect_theme_settings();
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
function fds_base_theme_menu_tree__primary(array &$variables) {
  return '<ul class="nav-primary">' . $variables['tree'] . '</ul>';
}

/**
 * Returns HTML for a menu link and submenu.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: Structured array data for a menu link.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see theme_menu_link()
 *
 * @ingroup theme_functions
 */
function fds_base_theme_menu_link(array $variables) {
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

    // Has a dropdown menu.
    if ($element['#below']) {

      if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
        $sub_menu = drupal_render($element['#below']);
      }
      elseif ((!empty($element['#original_link']['depth']))) {
        $generate_link = FALSE;

        // Add our own wrapper.
        unset($element['#below']['#theme_wrappers']);
        $sub_menu = '<div class="overflow-menu">';
        $sub_menu .=   '<button class="current button-overflow-menu js-dropdown js-dropdown--responsive-collapse" data-js-target="#headeroverflow_' . $element['#original_link']['mlid'] . '" aria-haspopup="true" aria-expanded="false">';
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
  $active_classes = _fds_in_active_trail($element['#href']);

  if (!empty($link_class)) {
    $link_class = array_merge($link_class, $active_classes);
  }
  else {
    $link_class = $active_classes;
  }

  if ($generate_link) {
    $options = array();
    $options['html'] = TRUE;

    if ($element['#localized_options']['attributes']['title']) {
      $options['attributes']['title'] = $element['#localized_options']['attributes'];
    }

    if ($link_class) {
      $options['attributes']['class'] = $link_class;
    }

    if ($element['#original_link']['depth'] > 1) {
      $link = l($element['#title'], $element['#href'], $options);
    } else {
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
