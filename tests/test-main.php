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

	/**
	 * Test the set_default_options() and delete_default_options() functions.
	 *
	 * @covers \Pantheon\DecoupledPreview\set_default_options()
	 * @covers \Pantheon\DecoupledPreview\delete_default_options()
	 *
	 * @return void
	 */
	public function test_default_options() : void {
		// Set the default options.
		set_default_options();
		$options = get_option( 'preview_sites' );
		$this->assertNotEmpty( $options );

		// Ensure that the transient was set.
		$transient = get_transient( 'example_preview_password' );
		$this->assertNotEmpty( $transient );

		// Ensure that the options were set.
		$this->assertNotEmpty( $options );
		$this->assertArrayHasKey( 'preview', $options );
		$this->assertArrayHasKey( 'label', $options['preview'][1] );
		$this->assertEquals( 'Example NextJS Preview', $options['preview'][1]['label'] );
		$this->assertArrayHasKey( 'url', $options['preview'][1] );
		$this->assertEquals( 'https://example.com/api/preview', $options['preview'][1]['url'] );
		$this->assertArrayHasKey( 'secret_string', $options['preview'][1] );
		$this->assertEquals( $transient, $options['preview'][1]['secret_string'] );
		$this->assertArrayHasKey( 'preview_type', $options['preview'][1] );
		$this->assertEquals( 'Next.js', $options['preview'][1]['preview_type'] );
		$this->assertArrayHasKey( 'id', $options['preview'][1] );
		$this->assertEquals( 1, $options['preview'][1]['id'] );
		$this->assertArrayHasKey( 'associated_user', $options['preview'][1] );
		$this->assertEquals( '', $options['preview'][1]['associated_user'] );

		// Delete the options.
		delete_default_options();
		$options = get_option( 'preview_sites' );

		// Ensure that the options were deleted.
		$this->assertEmpty( $options );
	}
}
