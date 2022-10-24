<?php

/**
 * @file
 * Create/Edit form.
 */

if ( ! class_exists( 'Decoupled_Preview_Settings' ) ) {

	/**
	 * Class Decoupled_Preview_Settings
	 */
	class Decoupled_Preview_Settings {

		/**
		 * Decoupled_Preview_Settings constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'list_preview' ) );
			add_action( 'admin_menu', array( &$this, 'add_preview' ) );
		}

		/**
		 * Init all the required form settings & fields.
		 *
		 * @return void
		 */
		public function admin_init() {
			register_setting( 'wp-decoupled-preview', 'preview_sites', [ $this, 'sanitize_callback_preview' ] );

			add_settings_section(
				'wp-decoupled-preview-section',
				'Create/Edit Preview Sites',
				[ &$this, 'settings_section_wp_decoupled_preview' ],
				'preview_sites'
			);

			add_settings_field(
				'plugin_text_label',
				'Label',
				[ &$this, 'setting_label_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field( 'plugin_text_url', 'URL', [ &$this, 'setting_url_fn' ], 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_text_secret', 'Secret', [ &$this, 'setting_secret_fn' ], 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_drop_down', 'Preview Type', [ &$this, 'setting_preview_type_fn' ], 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_checkbox', 'Content Type', [ &$this, 'setting_content_type_fn' ], 'preview_sites', 'wp-decoupled-preview-section' );

		}

		/**
		 * Add preview form.
		 *
		 * @return void
		 */
		public function add_preview() {
			add_submenu_page(
				'',
				__( 'Preview Sites', 'wp-decoupled-preview' ),
				__( 'Settings', 'wp-graphql' ),
				'manage_options',
				'add_preview_site',
				[ $this, 'wp_decoupled_preview_create_html' ]
			);
		}

		/**
		 * List all preview site.
		 *
		 * @return void
		 */
		public function list_preview() {
			add_options_page(
				'Preview Sites configuration',
				'Preview Sites',
				'manage_options',
				'preview_sites',
				[ &$this, 'wp_decoupled_preview_list_html' ]
			);
		}

		/**
		 * HTML for create from.
		 *
		 * @return void
		 */
		public function wp_decoupled_preview_create_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( 'You do not have sufficient permissions to access this page.' ) );
			}
			$edit_id = $this->get_edit_id();
			if ( isset( $edit_id ) ) {
				$action = 'options.php?edit=' . $this->get_edit_id();
			} else {
				$action = 'options.php';
			}

			?>
			<div class="wrap">
				<form action="<?php echo esc_html( $action ); ?>" method="post">
					<?php settings_fields( 'wp-decoupled-preview' ); ?>
					<?php do_settings_sections( 'preview_sites' ); ?>
					<p>
						<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * HTML for list preview site.
		 *
		 * @return void
		 */
		public function wp_decoupled_preview_list_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( 'You do not have sufficient permissions to access this page.' ) );
			}
			$options = get_option( 'preview_sites' );
			if ( isset( $options['preview'][1]['label'] ) ) {
				?>
				<div style="display: flex; padding: 1rem 1rem 1rem 0">
					<span style="font-weight: bold; font-size: 1.5rem">Preview Site Configuration</span>
					<a href="options-general.php?page=add_preview_site" class="button-primary" style="margin-left: auto">+ ADD PREVIEW SITE</a>
				</div>
				<div style="padding-right: 1rem ">
					<table class="wp-list-table widefat fixed striped table-view-list">
						<thead>
						<tr>
							<td>Label</td>
							<td>Status</td>
							<td>Preview Type</td>
							<td>Content Type</td>
							<td>Operations</td>
						</tr>
						</thead>
						<?php
						$options = array_shift( $options );
						?>
						<tbody>
						<?php
						foreach ( $options as $id => $option ) {
							$listing_data['label']        = $option['label'];
							$listing_data['url']          = $option['url'];
							$listing_data['preview_type'] = $option['preview_type'];
							if ( isset( $option['content_type'] ) ) {
								$listing_data['content_type'] = implode( ', ', $option['content_type'] );
							} else {
								$listing_data['content_type'] = 'Post, Page';
							}
							$listing_data['edit'] = "<a href='/wp/wp-admin/options-general.php?page=add_preview_site&edit={$id}'>Edit</a>";
							?>

							<tr>
								<?php
								foreach ( $listing_data as $data ) {
									?>
									<td>
									<?php
									echo wp_kses(
										$data,
										[
											'a' => [
												'href'  => [],
												'title' => [],
											],
										]
									);
									?>
										</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<?php
			} else {
				?>
				<div style="text-align: center">
					<h3>NO PREVIEW SITE CONFIGURATION FOUND</h3>
					<a href="options-general.php?page=add_preview_site" class="button-primary">+ ADD PREVIEW SITE</a>
				</div>
				<?php
			}
		}

		/**
		 * Sanitize & save the sites values in correct format.
		 *
		 * @param array $input Input values from form.
		 *
		 * @return array|array[]|false|mixed
		 */
		public function sanitize_callback_preview( $input ) {

			$options = get_option( 'preview_sites' );
			$edit_id = $this->get_edit_id();

			// Setting the old secret value if nothing is input when editing.
			if ( empty( $input['secret_string'] ) ) {
				$input['secret_string'] = $options['preview'][ $edit_id ]['secret_string'];
			}

			if ( $options && $input && ! isset( $options['preview'][1]['label'] ) ) {
				return [ 'preview' => [ 1 => $input ] ];
			} elseif ( $options && isset( $edit_id ) ) {
				$options['preview'][ $edit_id ] = $input;
				return $options;
			} else {
				$last_key                          = array_key_last( $options['preview'] );
				$options['preview'][ ++$last_key ] = $input;
				return $options;
			}
		}

		/**
		 * Description for create form.
		 *
		 * @return void
		 */
		public function settings_section_wp_decoupled_preview() {
			echo 'Create or Edit Preview Site';
		}

		/**
		 * Get the edit id from the URL parameters.
		 *
		 * @return mixed|null
		 */
		public function get_edit_id() {
			// TODO: Processing form data with nonce verification.
			if ( $_GET['edit'] ) {
				return $_GET['edit'];
			}
			return null;
		}

		/**
		 * Label Field.
		 *
		 * @return void
		 */
		public function setting_label_fn() {
			$edit_id = $this->get_edit_id();
			$site    = $this->get_preview_site( $edit_id );
			$value   = isset( $edit_id ) ? $site['label'] : '';
			echo wp_kses(
				"<input id='plugin_text_lable' name='preview_sites[label]' size='60' type='text'  value='{$value}' required /><br>[Required] Label for the preview site.",
				[
					'input' => [
						'id'       => [],
						'name'     => [],
						'size'     => [],
						'type'     => [],
						'value'    => [],
						'required' => [],
					],
					'br'    => [],
				]
			);
		}

		/**
		 * URL Field.
		 *
		 * @return void
		 */
		public function setting_url_fn() {
			$edit_id = $this->get_edit_id();
			$site    = $this->get_preview_site( $edit_id );
			$value   = isset( $edit_id ) ? $site['url'] : '';
			echo wp_kses(
				"<input id='plugin_text_url' name='preview_sites[url]' size='60' type='url' value='{$value}' required /><br>[Required] URL for the preview site.",
				[
					'input' => [
						'id'       => [],
						'name'     => [],
						'size'     => [],
						'type'     => [],
						'value'    => [],
						'required' => [],
					],
					'br'    => [],
				]
			);
		}

		/**
		 * Secret Field.
		 *
		 * @return void
		 */
		public function setting_secret_fn() {
			$edit_id = $this->get_edit_id();
			$html    = isset( $edit_id ) ? "<input id='plugin_text_secret' name='preview_sites[secret_string]' size='40' type='password' /><br>Shared secret for the preview site, when editing if kept empty old value will be saved else will be overwritten." : "<input id='plugin_text_secret' name='preview_sites[secret_string]' size='40' type='password' required /><br>[Required] Shared secret for the preview site.";
			echo wp_kses(
				$html,
				[
					'input' => [
						'id'       => [],
						'name'     => [],
						'size'     => [],
						'type'     => [],
						'value'    => [],
						'required' => [],
					],
					'br'    => [],
				]
			);
		}

		/**
		 * Preview Type Field.
		 *
		 * @return void
		 */
		public function setting_preview_type_fn() {
			$edit_id = $this->get_edit_id();
			$site    = $this->get_preview_site( $edit_id );
			$items   = [ 'Next.js' ];
			echo wp_kses(
				"<select id='preview_type' name='preview_sites[preview_type]' required>",
				[
					'select' => [
						'id'       => [],
						'name'     => [],
						'required' => [],
					],
				]
			);
			foreach ( $items as $item ) {
				$selected = ( $site['preview_type'] === $item ) ? 'selected="selected"' : '';
				echo wp_kses(
					"<option value='$item' $selected>$item</option>",
					[
						'option' => [
							'value' => [],
						],
					]
				);
			}
			echo wp_kses(
				'</select>',
				[
					'select' => [],
				]
			);
			echo wp_kses(
				'<br>[Required] Preview type for the front-end.',
				[
					'br' => [],
				]
			);
		}

		/**
		 * Content Type Field.
		 *
		 * @return void
		 */
		public function setting_content_type_fn() {
			$edit_id = $this->get_edit_id();
			$items   = [ 'Post', 'Page' ];
			$site    = $this->get_preview_site( $edit_id );
			foreach ( $items as $item ) {
				if ( isset( $edit_id ) && isset( $site['content_type'] ) ) {
					$checked = ( in_array( $item, $site['content_type'], true ) ) ? ' checked="checked" ' : '';
					echo wp_kses(
						'<label> <input ' . $checked . " value='$item' name='preview_sites[content_type][]' type='checkbox' /> $item </label><br />",
						[
							'input' => [
								'id'      => [],
								'checked' => [],
								'name'    => [],
								'size'    => [],
								'type'    => [],
								'value'   => [],
							],
							'br'    => [],
							'label' => [],
						]
					);
				} else {
					echo wp_kses(
						"<label> <input value='$item' name='preview_sites[content_type][]' type='checkbox' /> $item </label><br />",
						[
							'input' => [
								'id'      => [],
								'checked' => [],
								'name'    => [],
								'size'    => [],
								'type'    => [],
								'value'   => [],
							],
							'br'    => [],
							'label' => [],
						]
					);
				}
			}
			echo 'If no content types are specified, the preview site should display for all content types.';
		}

		/**
		 * Get the List of the configured sites or specific site.
		 *
		 * @param int|null $id (Optional) id for the preview config.
		 *
		 * @return array
		 *   Return a list of sites | Only a specific site.
		 */
		public function get_preview_site( int $id = NULL ): ?array {
			$preview_sites = get_option( 'preview_sites' );
			if ( $preview_sites && isset( $preview_sites['preview'][1]['label'] ) ) {
				$preview_sites = array_shift( $preview_sites );
				if ( isset( $id ) ) {
					return $preview_sites[ $id ];
				} else {
					return $preview_sites;
				}
			}
			return null;
		}

	}
}


