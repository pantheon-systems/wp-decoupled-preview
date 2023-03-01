<?php
/**
 * Test the Decoupled Preview list table.
 *
 * @package Decoupled_Preview
 */

namespace Pantheon\DecoupledPreview;

use ReflectionMethod;
use WP_UnitTestCase;

class Test_List_Table extends WP_UnitTestCase {
	public $list_table = false;

	public function setUp() : void {
		parent::setUp();
		require_once __DIR__ . '/../src/class-list-table.php';
		_install_sites();
		$this->list_table = new List_Table();
	}

	public function tearDown() : void {
		parent::tearDown();
		_remove_sites();
		$this->list_table = false;
	}

	/**
	 * Test that the list table exists and has items.
	 *
	 * Sites are created via _install_sites() in tests/bootstrap.php.
	 *
	 * @return void
	 */
	public function test_prepare_items() : void {
		$this->list_table->prepare_items();
		$items = $this->list_table->items;
		$this->assertNotEmpty( $items );
		$this->assertEquals( 3, count( $items ) );
	}

	/**
	 * Test that the list table has the expected sortable columns.
	 *
	 * @return void
	 */
	public function test_get_sortable() : void {
		$sortable = $this->list_table->get_sortable_columns();
		$this->assertNotEmpty( $sortable );
		$this->assertArrayHasKey( 'label', $sortable );
		$this->assertArrayHasKey( 'preview_type', $sortable );
		$this->assertArrayHasKey( 'content_type', $sortable );
	}

	/**
	 * Test that the no items message is the expected value. This test is
	 * pretty trivial but it's here to demonstrate how to test a method that
	 * outputs HTML.
	 *
	 * @return void
	 */
	public function test_no_items() : void {
		ob_start();
		$this->list_table->no_items();
		$no_items = ob_get_clean();
		$this->assertEquals( 'No preview sites configured.', $no_items );
	}

}