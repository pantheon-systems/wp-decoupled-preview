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


add_action( 'admin_bar_menu', 'add_admin_decoupled_preview_link', 100 );
add_action( 'wp_enqueue_scripts', 'enqueue_style' );
add_action( 'admin_enqueue_scripts', 'enqueue_style' );

function add_admin_decoupled_preview_link( $admin_bar ) {
    global $pagenow;
    if (( $pagenow == 'post.php' ) || (get_post_type() == 'post') || (get_post_type() == 'page')) {
        $admin_bar->add_menu([
            'id'    => 'decoupled-preview',
            'title' => 'Decoupled Preview',
            'href'  => '#',
            'meta'  => [
                'title' => __('Decoupled Preview'),
                'target' => '_blank',
            ],
        ]);
    }
}

function enqueue_style() {
    wp_enqueue_style( 'add-icon', plugins_url( '/css/add-icon.css', __FILE__ ) );
}