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
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'WP_DECOUPLED_PREVIEW_ENABLED' ) );
		$this->assertTrue( WP_DECOUPLED_PREVIEW_ENABLED );
	}
}
