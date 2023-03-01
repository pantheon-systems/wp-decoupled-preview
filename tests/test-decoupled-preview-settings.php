<?php
/**
 * Test the Decoupled Preview Settings.
 *
 * @package Decoupled_Preview
 */

namespace Pantheon\DecoupledPreview;

use WP_UnitTestCase;

/**
 * Main test suite.
 */
class Test_Settings extends WP_UnitTestCase {
	public $settings = false;

	public function setUp() : void {
		parent::setUp();
		require_once __DIR__ . '/../src/class-list-table.php';
		_install_sites();
		$this->settings = new Decoupled_Preview_Settings();
	}

	public function tearDown() : void {
		parent::tearDown();
		_remove_sites();
		$this->settings = false;
	}

	public function test_get_allowed_post_types() : void {
		$allowed_post_types = $this->settings->get_allowed_post_types();
		$this->assertNotEmpty( $allowed_post_types );
		$this->assertContains( 'post', $allowed_post_types );
		$this->assertContains( 'page', $allowed_post_types );
	}
}