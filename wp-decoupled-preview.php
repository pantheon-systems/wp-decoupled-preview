<?php
/**
 * Plugin Name:     Pantheon Decoupled Preview
 * Plugin URI:      https://github.com/pantheon-systems/wp-decoupled-preview
 * Description:     Preview WordPress content on Front-end sites including Next.js
 * Version:         1.0.6-dev
 * Author:          Pantheon
 * Author URI:      https://pantheon.io/
 * Text Domain:     wp-decoupled-preview
 * Domain Path:     /languages
 *
 * @package         Pantheon_Decoupled
 */

/**
 * Adjusts preview button to work with external decoupled preview sites.
 *
 * @package wp-decoupled-preview
 */

namespace Pantheon\DecoupledPreview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kick off the plugin.
 *
 * @return void
 */
function bootstrap() {
	define( 'WP_DECOUPLED_PREVIEW_ENABLED', true );

	// Load the settings class and kick it off.
	require_once __DIR__ . '/src/class-decoupled-preview-settings.php';
	new Decoupled_Preview_Settings();

	add_action( 'init', __NAMESPACE__ . '\\conditionally_enqueue_scripts' );
	add_action( 'updated_option', __NAMESPACE__ . '\\redirect_to_preview_site' );
}

/**
 * Maybe add some actions and filters.
 *
 * Moved out of the global scope by @jazzsequence <Chris Reynolds chris.reynolds@pantheon.io>
 *
 * @return void
 */
function conditionally_enqueue_scripts() {
	global $pagenow;

	if ( ! function_exists( 'post_preview' ) ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';
	}

	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_admin_decoupled_preview_link', 100 );
		add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
	}

	// We're not processing this information at all so we can bypass the nonce here.
	if ( isset( $_GET['decoupled_preview_site'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Return custom preview template where we can handle redirect.
		add_filter( 'template_include', __NAMESPACE__ . '\\override_preview_template', 1 );
	}
}

/**
 * Redirect to preview site on updated option
 *
 * @param string $option_name Option name.
 *
 * @return void
 */
function redirect_to_preview_site( $option_name ) {
	if ( 'preview_sites' === $option_name && is_admin() ) {
		echo '<script type="text/javascript">window.location = "options-general.php?page=preview_sites"</script>';
		exit;
	}
}

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
		$preview_helper = new Decoupled_Preview_Settings();
		$sites = $preview_helper->get_preview_site();
		$post_type = get_post_type();
		$enable_by_post_type = $preview_helper->get_enabled_site_by_post_type( $post_type );
		$query_args = [];

		if (
			$sites &&
			! empty( $enable_by_post_type ) &&
			( ( 'post' === $post_type ) || ( 'page' === $post_type ) )
		) {
			$admin_bar->add_menu( [
				'id' => 'decoupled-preview',
				'title' => 'Decoupled Preview',
				'href' => false,
				'meta' => [
					'class' => 'components-button is-tertiary',
				],
			] );

			// Reinventing the wheel and creating the preview link as done in wp/wp-admin/includes/post.php.
			$post_id = get_the_ID();
			$nonce = wp_create_nonce( 'post_preview_' . $post_id );
			$query_args['preview_id'] = $post_id;
			$query_args['preview_nonce'] = $nonce;

			foreach ( $sites['preview'] as $id => $site ) {
				if (
					( ! isset( $site['content_type'] ) ) ||
					( in_array( $post_type, $site['content_type'], true ) )
				) {
					$query_args['decoupled_preview_site'] = $id;
					$preview_link                         = get_preview_post_link( $post_id, $query_args );
					$admin_bar->add_menu( [
						'id' => 'preview-site-' . $id,
						'parent' => 'decoupled-preview',
						'title' => $site['label'],
						'href' => $preview_link,
						'meta' => [
							'title' => $site['label'],
							'target' => '_blank',
							'class' => 'dashicons-before dashicons-external components-button components-menu-item__button',
						],
					] );
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
function enqueue_scripts() {
	$preview_helper = new Decoupled_Preview_Settings();
	$sites = $preview_helper->get_preview_site();
	$enable_by_post_type = $preview_helper->get_enabled_site_by_post_type( get_post_type() );
	if ( $sites && ! empty( $enable_by_post_type ) ) {
		wp_enqueue_style( 'add-icon', plugins_url( '/css/add-icon.css', __FILE__ ), [], 1.0 );
		wp_enqueue_script( 'add-new-preview-btn', plugins_url( '/js/add-new-preview-btn.js', __FILE__ ), [], 1.0, true );
	}
}

/**
 * Override preview template.
 *
 * @return string
 */
function override_preview_template() {
	return trailingslashit( __DIR__ ) . 'templates/preview-template.php';
}

// Let's rock.
bootstrap();
