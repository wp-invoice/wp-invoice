<?php
/**
 * UsabilityDynamics General UI Classes - Customized for WP-Invoice
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 * @package UsabilityDynamics
*/

if(!class_exists('WPI_UD_F')):

/**
 * General Shared Functions used in UsabilityDynamics and TwinCitiesTech.com plugins and themes.
 *
 * Used for performing various useful functions applicable to different plugins.
 *
 * @link http://usabilitydynamics/codex/CRM_UD_F
 * @package UsabilityDynamics
 */

class WPI_UD_F {

  /**
   * Check if the current WP version is older then given parameter $version.
   * @param string $version Version for checking with the current one.
   * @author peshkov@UD
   */
  static function is_older_wp_version ($version = '') {
    if(empty($version) || (float)$version == 0) return false;
    $current_version = get_bloginfo('version');
    /** Clear version numbers */
    $current_version = preg_replace("/^([0-9\.]+)-(.)+$/", "$1", $current_version);
    $version = preg_replace("/^([0-9\.]+)-(.)+$/", "$1", $version);
    return ((float)$current_version < (float)$version) ? true : false;
  }

}

endif; /* f(!class_exists('WPI_UD_F')): */