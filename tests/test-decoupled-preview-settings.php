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

	/**
	 * Test the preview ID validation.
	 *
	 * @dataProvider provider_preview_id
	 *
	 * @param int $expected The expected result.
	 * @param int $input    The input to test.
	 *
	 * @return void
	 */
	public function test_validate_preview_id( $input, $expected ) : void {
		$this->assertEquals( $expected, $this->settings->validate_preview_id( $input ) );
	}

	/**
	 * Test the get_preview_site function.
	 *
	 * @dataProvider provider_preview_site
	 *
	 * @param array $expected The expected result.
	 * @param int   $input    The input to test.
	 *
	 * @return void
	 */
	public function test_get_preview_site( $expected, $input ) : void {
		$this->assertEquals( $expected, $this->settings->get_preview_site( $input ) );
	}

	/**
	 * Test the remove_site_from_list function.
	 *
	 * @covers Pantheon\DecoupledPreview\Decoupled_Preview_Settings::filter_preview_sites
	 *
	 * @return void
	 */
	public function test_remove_site_from_list() : void {
		// Get the sites before so we can count them.
		$sites_before = get_option( 'preview_sites' );
		$sites_before_count = count( $sites_before['preview'] );

		// Remove a site.
		$sites_after = $this->settings->remove_site_from_list( 1 );
		$sites_after_count = count( $sites_after['preview'] ) ;

		// Make sure the count is one less.
		$this->assertEquals( $sites_before_count - 1, $sites_after_count );
	}

	/**
	 * Test the get_enabled_site_by_post_type function.
	 *
	 * @dataProvider provider_sites_by_post_type
	 *
	 * @param string $post_type The post type to test.
	 * @param array  $expected  The expected result.
	 *
	 * @return void
	 */
	public function test_get_enabled_site_by_post_type( $post_type, $expected ) : void {
		$sites = $this->settings->get_enabled_site_by_post_type( $post_type );

		// Make sure the count is the same.
		$this->assertEquals( count( $expected ), count( $sites ) );
		foreach ( $sites as $site ) {
			if ( isset( $site['content_type'] ) ) {
				// If the site has a content type, make sure it's the one we expect. No value here means all post types are allowed.
				$this->assertContains( $post_type, $site['content_type'] );
			}

			// Pluck the IDs from the expected array and make sure the current site is in the list.
			$ids = wp_list_pluck( $expected, 'id' );
			$this->assertContains( $site['id'], $ids );
		}
	}

	/**
	 * Data provider for test_validate_preview_id.
	 *
	 * @return array
	 */
	public function provider_preview_id() : array {
		return [
			[ 1, 1 ],
			[ '1', 1 ],
			[ 0, 4 ],
			[ -1, 4 ],
			[ 4, 4 ],
			[ 35, 35 ],
		];
	}

	/**
	 * Data provider for test_get_preview_site.
	 *
	 * @return array
	 */
	public function provider_preview_site() : array {
		$sites = _get_test_sites();
		return [
			[
				'expected' => $sites[1],
				'input' => 1,
			],
			[
				'expected' => $sites[2],
				'input' => 2,
			],
			[
				'expected' => $sites[3],
				'input' => 3,
			],
			[
				'expected' => [],
				'input' => 4,
			],
		];
	}

	/**
	 * Data provider for test_get_enabled_site_by_post_type.
	 *
	 * @return array
	 */
	public function provider_sites_by_post_type() : array {
		return [
			[
				'post_type' => 'post',
				'expected' => [
					[
						'label' => 'Example NextJS Preview',
						'url' => 'https://example.com/api/preview',
						'secret_string' => 'secret',
						'preview_type' => 'Next.js',
						'associated_user' => '',
						'id' => 1,
					],
					[
						'label' => 'Test Site',
						'url' => 'https://test-site.pantheonsite.io',
						'secret_string' => 'test',
						'preview_type' => 'Next.js',
						'content_type' => [ 'post' ],
						'associated_user' => 'admin',
						'id' => 2,
					],
				],
			],
			[
				'post_type' => 'page',
				'expected' => [
					[
						'label' => 'Example NextJS Preview',
						'url' => 'https://example.com/api/preview',
						'secret_string' => 'secret',
						'preview_type' => 'Next.js',
						'associated_user' => '',
						'id' => 1,
					],
					[
						'label' => 'Test Site 2',
						'url' => 'https://test-site-2.pantheonsite.io',
						'secret_string' => 'test',
						'preview_type' => 'Next.js',
						'content_type' => [ 'page' ],
						'associated_user' => 'admin',
						'id' => 3,
					],
				],
			],
		];
	}
}