<?php

/**
 * @param $menu_name
 * @param bool|FALSE $theme_wrapper
 * @param null $max_depth
 *
 * @return array
 */
function _fds_base_theme_generate_menu($menu_name, $theme_wrapper = NULL, $max_depth = NULL) {
  if (empty($menu_name)) {
    return array();
  }
  $i18n = module_exists('i18n_menu');
  $tree = menu_tree_page_data($menu_name, $max_depth);

  if ($i18n) {
    $tree = i18n_menu_localize_tree($tree);
  }

  $output = menu_tree_output($tree);
  $output['#theme_wrappers'] = array(
    'menu_tree__' . strtr($theme_wrapper, '-', '_'),
  );

  // Links
  $menu_link_function_names = array(
    'old' => 'menu_link__' . strtr($menu_name, '-', '_'),
    'new' => 'menu_link__' . strtr($theme_wrapper, '-', '_'),
  );
  array_walk_recursive($output, '_fds_base_theme_alter_menu_theme_function', $menu_link_function_names);

  return $output;
}

/**
 * Alter menu theme function suggestions.
 *
 * @param $item
 * @param $key
 * @param $theme_functions
 */
function _fds_base_theme_alter_menu_theme_function(&$item, $key, $theme_functions) {
  $old_theme_function = $theme_functions['old'];
  $new_theme_function = $theme_functions['new'];

  if ($item === $old_theme_function) {
    $item = $new_theme_function;
  }
}

/**
 * Checks whether an item is active or in the active trail.
 *
 * This is based on the code in the core toolbar module
 * /modules/toolbar/toolbar.module function toolbar_in_active_trail($path)
 *
 * @param
 *   The path of a menu item we're evaluating
 *
 * @return
 *   An array of classes if active or in the active trail.
 *   An empty array if not active or in the active trail.
 */
function _fds_base_theme_in_active_trail($link_path, $wantedClasses = array()) {
  $classes = array();
  $active_paths = array();

  $active_class = (!empty($wantedClasses['active'])) ? $wantedClasses['active'] : 'active';
  $trail_class = (!empty($wantedClasses['trail'])) ? $wantedClasses['trail'] : 'active-trail';

  // Don't waste cycles: if we're on the front page, we're either active or not
  // and we don't need to look at the rest of the tree.
  if (drupal_is_front_page()) {
    if ($link_path == '<front>') {
      $classes[] = $active_class;
    }

    return $classes;
  }

  // If the current page matches the item path, it's active and we don't need to
  // look further. Comment out the return statement to have both 'active' and
  // 'active-trail' classes applied to the item for the current page.
  if ($link_path == current_path()) {
    $classes[] = $active_class;

    return $classes;
  }
  // If we're not on the front page and the current link is not the current page,
  // The we need to check the whole active trail. Since Drupal caches the result
  // of menu_get_active_trail(), this is not too costly.
  $active_trail = menu_get_active_trail();

  foreach ($active_trail as $item) {
    if(!empty($item['link_path'])) {
      $active_paths[$item['link_path']] = TRUE;
    }
  }

  if (!empty($active_paths[$link_path])) {
    $classes[] = $trail_class;
  }

  return $classes;
}
