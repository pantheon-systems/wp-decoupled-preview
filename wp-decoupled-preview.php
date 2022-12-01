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

require_once(ABSPATH . 'wp-admin/includes/post.php');
require_once dirname( __FILE__ ) . '/src/class-decoupled-preview-settings.php';

register_activation_hook( __FILE__, 'wp_decoupled_preview_default_options' );
register_deactivation_hook( __FILE__, 'wp_decoupled_preview_delete_default_options' );

global $pagenow;

if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
	add_action( 'admin_bar_menu', 'add_admin_decoupled_preview_link', 100 );
	add_action( 'wp_enqueue_scripts', 'enqueue_style' );
	add_action( 'admin_enqueue_scripts', 'enqueue_style' );
	add_action( 'wp_enqueue_scripts', 'enqueue_script' );
	add_action( 'admin_enqueue_scripts', 'enqueue_script' );
}
if (isset($_GET['decoupled_preview_site'])) {
  // Return custom preview template where we can handle redirect
  add_filter( 'template_include', 'override_preview_template', 1);
}

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

add_action(
	'updated_option',
	function( $option_name, $option_value ) {
		if ( 'preview_sites' === $option_name ) {
			echo '<script type="text/javascript">window.location = "options-general.php?page=preview_sites"</script>';
			exit;
		}
	},
	10,
	2
);

/**
 * Add Preview button in admin bar menu for post & pages.
 *
 * @param stdClass $admin_bar Admin bar menu.
 *
 * @return void
 */
function add_admin_decoupled_preview_link( $admin_bar ) {

	global $pagenow;
	if ( 'post.php' === $pagenow ) {
		$post_type           = get_post_type();
		$preview_helper      = new Decoupled_Preview_Settings();
		$sites               = $preview_helper->get_preview_site();
		$enable_by_post_type = $preview_helper->get_enabled_site_by_post_type( $post_type );
		if ( $sites && ! empty( $enable_by_post_type ) && ( ( 'post' === $post_type ) || ( 'page' === $post_type ) ) ) {
			$admin_bar->add_menu(
				[
					'id'    => 'decoupled-preview',
					'title' => 'Decoupled Preview',
					'href'  => false,
					'meta'  => [
						'class' => 'components-button is-tertiary',
					],
				]
			);

			// Reinventing the wheel and creating the preview link as done in wp/wp-admin/includes/post.php.
			$post_id                     = get_the_ID();
			$post                        = get_post($post_id);
			$nonce                       = wp_create_nonce( 'post_preview_' . $post->ID );
			$query_args['preview_id']    = $post->ID;
			$query_args['preview_nonce'] = $nonce;

			foreach ( $sites as $id => $site ) {
				if ( ( ! isset( $site['content_type'] ) ) || ( in_array( $post_type, $site['content_type'], true ) ) ) {
					$query_args['decoupled_preview_site'] = $id;
					$preview_link = get_preview_post_link( $post->ID, $query_args );
					$admin_bar->add_menu(
						[
							'id'     => 'preview-site-' . $id,
							'parent' => 'decoupled-preview',
							'title'  => $site['label'],
							'href'   => $preview_link,
							'meta'   => [
								'title'  => $site['label'],
								'target' => '_blank',
								'class'  => 'dashicons-before dashicons-external components-button components-menu-item__button',
							],
						]
					);
				}
			}
		}
	}

}

/**
 * Apply style to Decoupled Preview menu.
 *
 * @return void
 */
function enqueue_style() {
	$preview_helper      = new Decoupled_Preview_Settings();
	$sites               = $preview_helper->get_preview_site();
	$enable_by_post_type = $preview_helper->get_enabled_site_by_post_type( get_post_type() );
	if ( $sites && ! empty( $enable_by_post_type ) ) {
		wp_enqueue_style( 'add-icon', plugins_url( '/css/add-icon.css', __FILE__ ), [], 1.0 );
	}
}

/**
 * Apply style to Decoupled Preview menu.
 *
 * @return void
 */
function enqueue_script() {
	$preview_helper      = new Decoupled_Preview_Settings();
	$sites               = $preview_helper->get_preview_site();
	$enable_by_post_type = $preview_helper->get_enabled_site_by_post_type( get_post_type() );
	if ( $sites && ! empty( $enable_by_post_type ) ) {
		wp_enqueue_script( 'add-new-preview-btn', plugins_url( '/js/add-new-preview-btn.js', __FILE__ ), [], 1.0, true );
	}
}

function override_preview_template($template) {
  return trailingslashit( dirname( __FILE__ ) ) . 'templates/preview-template.php';
}
