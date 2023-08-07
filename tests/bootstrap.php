<?php
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', __DIR__ . '/../vendor/yoast/phpunit-polyfills/phpunitpollyfills-autoload.php' );

require_once $_tests_dir . '/includes/functions.php';

if ( getenv( 'WP_CORE_DIR' ) ) {
	$_core_dir = getenv( 'WP_CORE_DIR' );
} elseif ( getenv( 'WP_DEVELOP_DIR' ) ) {
	$_core_dir = getenv( 'WP_DEVELOP_DIR' ) . '/src/';
} else {
	$_core_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress/';
}

function _manually_load_plugin() {
	require dirname( __FILE__, 2 ) . '/wp-decoupled-preview.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

function _install_sites() {
	$new_sites = [ 'preview' => _get_test_sites() ];
	add_option( 'preview_sites', $new_sites );
}

function _remove_sites() {
	delete_option( 'preview_sites' );
}

function _get_test_sites() : array {
	return [
		1 => [
			'label' => 'Example NextJS Preview',
			'url' => 'https://example.com/api/preview',
			'secret_string' => 'secret',
			'preview_type' => 'Next.js',
			'associated_user' => '',
			'id' => 1,
		],
		2 => [
			'label' => 'Test Site',
			'url' => 'https://test-site.pantheonsite.io',
			'secret_string' => 'test',
			'preview_type' => 'Next.js',
			'content_type' => [ 'post' ],
			'associated_user' => 'admin',
			'id' => 2,
		],
		3 => [
			'label' => 'Test Site 2',
			'url' => 'https://test-site-2.pantheonsite.io',
			'secret_string' => 'test',
			'preview_type' => 'Next.js',
			'content_type' => [ 'page' ],
			'associated_user' => 'admin',
			'id' => 3,
		],
	];
}
