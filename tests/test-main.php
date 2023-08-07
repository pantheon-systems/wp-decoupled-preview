<?php
/**
 * Test the Decoupled Preview plugin.
 *
 * @package Decoupled_Preview
 */

namespace Pantheon\DecoupledPreview;

use WP_UnitTestCase;

/**
 * Main test suite.
 */
class Test_Main extends WP_UnitTestCase {
	/**
	 * Test that the plugin is loaded.
	 *
	 * @return void
	 */
	public function test_plugin_loaded() : void {
		$this->assertTrue( defined( 'WP_DECOUPLED_PREVIEW_ENABLED' ) );
		$this->assertTrue( WP_DECOUPLED_PREVIEW_ENABLED );
	}

	/**
	 * Test that the plugin class exists.
	 *
	 * @return void
	 */
	public function test_class_exists() : void {
		$this->assertTrue( class_exists( __NAMESPACE__ . '\\Decoupled_Preview_Settings' ) );
	}
}
