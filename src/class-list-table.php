<?php
/**
 * List table for displaying the list of sites.
 *
 * @package Pantheon_Decoupled
 */

namespace Pantheon\DecoupledPreview;

use WP_List_Table;

/**
 * List table for displaying the list of sites.
 */
class List_Table extends WP_List_table {
	/**
	 * Prepare the items for the table.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$preview_sites = get_option( 'preview_sites' );
		$columns = $this->get_columns();
		$hidden  = [];
		$items = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$per_page = 20; // TODO: For pagination. This should be configurable via screen options but 20 is probably more than anyone would realistically ever need.
		// We can bypass nonce verification here because we're not processing any form data, we're just checking the pagination.
		$paged = ( isset( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$offset = ( $paged - 1 ) * $per_page;
		$sites = isset( $preview_sites['preview'] ) ? $preview_sites['preview'] : [];
		$total_items = count( $sites );

		if ( ! empty( $sites ) ) {
			// Add an id parameter for each item in $preview_sites.
			foreach ( $sites as $key => $value ) {
				$sites[ $key ]['id'] = $key;
			}
		}

		$items = ! empty( $sites ) ? array_slice( $sites, $offset, $per_page ) : $sites;
		usort( $items, [ $this, 'usort_reorder' ] );
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page' => $per_page,
		] );
		$this->items = $items;
	}

	/**
	 * Return the columns that are sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() : array {
		return [
			'label' => [ 'label', true ],
			'preview_type' => [ 'preview_type', true ],
			'content_type' => [ 'content_type', true ],
		];
	}

	/**
	 * Sort the data.
	 *
	 * @param array $a First item to compare.
	 * @param array $b Second item to compare.
	 *
	 * @return int
	 */
	private function usort_reorder( $a, $b ) : int {
		// Similar to the above, I don't think we need to check the nonce here because we're not processing any information other than the sort order.
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'label'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		return ( 'asc' === $order ) ? $result : - $result;
	}

	/**
	 * Message for no items found.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No preview sites configured.', 'wp-decoupled-preview' );
	}

	/**
	 * Get the columns for the table.
	 *
	 * @return array
	 */
	public function get_columns() : array {
		return [
			'label' => __( 'Label', 'wp-decoupled-preview' ),
			'url' => __( 'URL', 'wp-decoupled-preview' ),
			'preview_type' => __( 'Preview Type', 'wp-decoupled-preview' ),
			'content_type' => __( 'Content Type', 'wp-decoupled-preview' ),
			'actions' => __( 'Actions', 'wp-decoupled-preview' ),
		];
	}

	/**
	 * Render a column value.
	 *
	 * @param object $item        The item to render.
	 * @param string $column_name The column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) : string {
		switch ( $column_name ) {
			case 'label':
			case 'url':
			case 'preview_type':
				return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
			case 'content_type':
				return isset( $item['content_type'] ) ? ucwords( implode( ', ', $item['content_type'] ) ) : __( 'Post, Page', 'wp-decoupled-preview' );
			case 'actions':
				return sprintf(
					'<a href="%s">%s</a>',
					wp_nonce_url( add_query_arg( [
						'page' => 'add_preview_sites',
						'action' => 'edit',
						'id' => $item['id'],
					], admin_url( 'options-general.php' ) ), 'edit-preview-site', 'nonce' ),
					esc_html__( 'Edit', 'wp-decoupled-preview' )
				);
			default:
				return '';
		}
	}
}
