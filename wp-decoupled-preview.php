<?php
/**
 * Plugin Name:     Pantheon Decoupled Preview
 * Plugin URI:      https://pantheon.io/
 * Description:     Preview WordPress content on Front-end sites including Next.js
 * Version:         0.1.0
 * Author:          Pantheon
 * Author URI:      https://pantheon.io/
 * Text Domain:     wp-decoupled-preview
 * Domain Path:     /languages
 *
 * @package         Pantheon_Decoupled
 */

register_activation_hook(__FILE__, 'wp_decoupled_preview_default_options');

function wp_decoupled_preview_default_options() {
	add_option( 'preview_sites', ['preview' => [ 0 => [ 'label' => NULL, 'url' => NULL, 'secret_string' => NULL ] ] ] );
}

require_once dirname( __FILE__ ) . '/src/settings.php';

new Decoupled_Preview_Settings();