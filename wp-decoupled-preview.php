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

register_activation_hook( __FILE__, 'wp_decoupled_preview_default_options' );
register_deactivation_hook( __FILE__, 'wp_decoupled_preview_delete_default_options' );

/**
 * Set default values for the preview sites options.
 *
 * @return void
 */
function wp_decoupled_preview_default_options() {
	add_option(
		'preview_sites',
		[
			'preview' => [
				1 => [
					'label'         => null,
					'url'           => null,
					'secret_string' => null,
				],
			],
		]
	);
}

/**
 * Delete preview sites options when deactivation plugin.
 *
 * @return void
 */
function wp_decoupled_preview_delete_default_options() {
	delete_option( 'preview_sites' );
}

require_once dirname( __FILE__ ) . '/src/class-decoupled-preview-settings.php';

new Decoupled_Preview_Settings();

add_action(
	'updated_option',
	function( $option_name, $option_value ) {
		if ( $option_name === 'preview_sites' ) {
			echo '<script type="text/javascript">window.location = "options-general.php?page=preview_sites"</script>';
			exit;
		}
	},
	10,
	2
);
