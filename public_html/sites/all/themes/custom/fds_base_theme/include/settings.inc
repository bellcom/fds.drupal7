<?php

/**
 * Collect and assemble theme settings.
 *
 * @return array
 */
function _fds_base_theme_collect_theme_settings() {
  $settings = [];

  // Header.
  $settings['authority_details']['show'] = theme_get_setting('authority_details_show');
  $settings['authority_details']['name'] = theme_get_setting('authority_details_name');
  $settings['authority_details']['text'] = theme_get_setting('authority_details_text');
  $settings['authority_details']['phone_system'] = theme_get_setting('authority_details_phone_system');
  $settings['authority_details']['phone_readable'] = theme_get_setting('authority_details_phone_readable');

  return $settings;
}
