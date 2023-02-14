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
