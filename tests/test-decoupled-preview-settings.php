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

	/**
	 * Test that the allowed post types are the expected values.
	 *
	 * @return void
	 */
	public function test_get_allowed_post_types() : void {
		$allowed_post_types = $this->settings->get_allowed_post_types();
		$this->assertNotEmpty( $allowed_post_types );
		$this->assertContains( 'post', $allowed_post_types );
		$this->assertContains( 'page', $allowed_post_types );

		// Add a custom post type and test that it's in the list.
		add_filter( 'pantheon.dp.allowed_post_types', function( $allowed_types ) {
			$allowed_types[] = 'foo';
			return $allowed_types;
		} );
		$this->assertContains( 'foo', $this->settings->get_allowed_post_types() );

		// Override the default post types and test that the allowed post types are what we expect.
		add_filter( 'pantheon.dp.allowed_post_types', function() {
			return [ 'bar', 'baz' ];
		} );
		$allowed_post_types = $this->settings->get_allowed_post_types();
		$this->assertNotContains( 'post', $allowed_post_types );
		$this->assertNotContains( 'page', $allowed_post_types );
		$this->assertContains( 'bar', $allowed_post_types );
		$this->assertContains( 'baz', $allowed_post_types );
	}

	/**
	 * Test that the allowed preview types are the expected values.
	 *
	 * @return void
	 */
	public function test_sanitize_preview_type() : void {
		$this->assertEquals( 'Next.js', $this->settings->sanitize_preview_type( 'Next.js' ) );
		$this->assertNotEquals( 'Next.js', $this->settings->sanitize_preview_type( 'next.js' ) );
		$this->assertEquals( '', $this->settings->sanitize_preview_type( 'foo' ) );

		// Add a custom preview type and test that it's in the list.
		add_filter( 'pantheon.dp.allowed_preview_types', function( $preview_types ) {
			$preview_types[] = 'foo';
			return $preview_types;
		} );
		$this->assertEquals( 'foo', $this->settings->sanitize_preview_type( 'foo' ) );

		// Override the default preview types and test that the allowed preview types are what we expect.
		add_filter( 'pantheon.dp.allowed_preview_types', function() {
			return [ 'Gatsby' ];
		} );
		$this->assertEquals( 'Gatsby', $this->settings->sanitize_preview_type( 'Gatsby' ) );
		$this->assertNotEquals( 'Next.js', $this->settings->sanitize_preview_type( 'Next.js' ) );
	}
}