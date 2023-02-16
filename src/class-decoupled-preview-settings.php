<?php
/**
 * Create/Edit form.
 *
 * @file
 *
 * @package wp-decoupled-preview\Decoupled_Preview_Settings
 */

namespace Pantheon\DecoupledPreview;

if ( ! class_exists( __NAMESPACE__ . '\\Decoupled_Preview_Settings' ) ) {

	/**
	 * Class Decoupled_Preview_Settings
	 */
	class Decoupled_Preview_Settings {

		/**
		 * Decoupled_Preview_Settings constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', [ &$this, 'admin_init' ] );
			add_action( 'admin_menu', [ &$this, 'list_preview' ] );
			add_action( 'admin_menu', [ &$this, 'add_preview_subpage' ] );
			add_action( 'admin_menu', [ &$this, 'delete_preview_subpage' ] );
			add_action( 'admin_head', [ &$this, 'style_columns' ] );
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
				'',
				'', // No callback needed.
				'preview_sites'
			);

			add_settings_field(
				'plugin_text_label',
				esc_html__( 'Label', 'wp-decoupled-preview' ),
				[ &$this, 'setting_label_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field(
				'plugin_text_url',
				esc_html__( 'URL', 'wp-decoupled-preview' ),
				[ &$this, 'setting_url_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field(
				'plugin_text_secret',
				esc_html__( 'Secret', 'wp-decoupled-preview' ),
				[ &$this, 'setting_secret_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field(
				'plugin_drop_down',
				esc_html__( 'Preview Type', 'wp-decoupled-preview' ),
				[ &$this, 'setting_preview_type_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field(
				'plugin_checkbox',
				esc_html__( 'Content Type', 'wp-decoupled-preview' ),
				[ &$this, 'setting_content_type_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
		}

		/**
		 * Add preview form.
		 *
		 * @return void
		 */
		public function add_preview_subpage() {
			add_submenu_page(
				'options-general.php',
				'',
				'',
				'manage_options',
				'add_preview_sites',
				[ $this, 'preview_create_html' ]
			);
		}

		/**
		 * A delete subpage.
		 *
		 * @return void
		 */
		public function delete_preview_subpage() {
			add_submenu_page(
				'',
				esc_html__( 'Delete Preview Sites', 'wp-decoupled-preview' ),
				esc_html__( 'Preview Sites', 'wp-decoupled-preview' ),
				'manage_options',
				'delete_preview_site',
				[ $this, 'wp_decoupled_preview_delete_html' ]
			);
		}

		/**
		 * Style the columns.
		 *
		 * @return void
		 */
		public function style_columns() {
			$screen = get_current_screen();
			if ( $screen->id !== 'settings_page_preview_sites' ) {
				return;
			}

			// Give the URL column a little more room.
			echo '<style>
				.wp-list-table .column-url {
					width: 25%;
				}
			';
		}

		/**
		 * List all preview site.
		 *
		 * @return void
		 */
		public function list_preview() {
			add_options_page(
				__( 'Preview Sites configuration', 'wp-decoupled-preview' ),
				__( 'Preview Sites', 'wp-decoupled-preview' ),
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
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}

			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			if ( $edit_id ) {
				$action = 'options.php?edit=' . $edit_id;
			} else {
				$action = 'options.php';
			}
			?>
			<div class="wrap">
				<form action="<?php echo esc_url( $action ); ?>" method="post">
					<?php settings_fields( 'wp-decoupled-preview' ); ?>
					<?php do_settings_sections( 'preview_sites' ); ?>
					<p>
						<input name="<?php esc_attr_e( 'Submit', 'wp-decoupled-preview' ); ?>" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-decoupled-preview' ); ?>" />
						<?php
						if ( isset( $edit_id ) ) {
							$url = add_query_arg(
								[
									'delete' => $edit_id,
									'nonce'  => wp_create_nonce( 'delete' . $edit_id ),
								],
								'options-general.php?page=delete_preview_site'
							);
							?>
							<a id="delete-preview" class="button-secondary button-large" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Delete', 'wp-decoupled-preview' ); ?></a>
							<?php
						}
						?>
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * The delete subpage to handle the delete request.
		 *
		 * @return void
		 */
		public function wp_decoupled_preview_delete_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}
			$delete_id = $this->verify_nonce_get_action_id( 'delete' );

			if ( ! $delete_id && ! check_admin_referer( 'delete' ) ) {
				wp_die( esc_html__( 'Unable perform action: invalid nonce', 'wp-decoupled-preview' ) );
			}

			if ( $delete_id ) {
				$this->delete_preview_site( $delete_id );
				echo '<script type="text/javascript">window.location = "options-general.php?page=preview_sites"</script>';
				exit;
			}
		}

		/**
		 * HTML for list preview site.
		 *
		 * @return void
		 */
		public function wp_decoupled_preview_list_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}

			// Check if the List_Table class is available.
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}
			require_once plugin_dir_path( __FILE__ ) . 'class-list-table.php';
			?>
			<div class="wrap">
				<div>
					<span style="display: flex;">
						<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
						<a href="options-general.php?page=add_preview_site" class="button-primary" style="margin-left: auto; height: 1.2em; margin-top: 9px">+ <?php esc_html_e( 'Add Preview Site', 'wp-decoupled-preview' ); ?></a>
					</span>
			<?php
			$wp_list_table = new List_Table();
			$wp_list_table->prepare_items();
			$wp_list_table->display();
			?>
				</div>
			</div>
			<?php
		}

		/**
		 * Sanitize & save the sites values in correct format.
		 *
		 * @param array $input Input values from form.
		 *
		 * @return array|array[]|false|mixed
		 */
		public function sanitize_callback_preview( array $input ) {
			if ( $this->verify_nonce_get_action_id( 'delete' ) ) {
				return [ 'preview' => $input ];
			} else {
				$options = get_option( 'preview_sites' );
				// Set Content type in correct format.
				if ( isset( $input['content_type'] ) ) {
					foreach ( $input['content_type'] as $key => $type ) {
						$input['content_type'][ $key ] = strtolower( $type );
					}
				}

				$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.

				$last_key = array_key_last( $options['preview'] );
				if ( 1 === $last_key && null === $options['preview'][1]['label'] ) {
					return [ 'preview' => [ 1 => $input ] ];
				}
				if ( $options && isset( $edit_id ) ) {
					// Setting the old secret value if nothing is input when editing.
					if ( empty( $input['secret_string'] ) ) {
						$input['secret_string'] = $options['preview'][ $edit_id ]['secret_string'];
					}
					$options['preview'][ $edit_id ] = $input;
					return $options;
				} elseif ( $options && isset( $last_key ) ) {
					$options['preview'][ ++$last_key ] = $input;
					return $options;
				}
			}
		}

		/**
		 * Description for create form.
		 *
		 * @return void
		 */
		public function settings_section_wp_decoupled_preview() {
			esc_html_e( 'Create or Edit Preview Site', 'wp-decoupled-preview' );
		}

		/**
		 * Verify Nonce & return the action ID.
		 *
		 * @param string $action Action name.
		 *
		 * @return string|null
		 */
		public function verify_nonce_get_action_id( string $action ): ?string {
			$filtered_action = filter_input( INPUT_GET, $action, FILTER_SANITIZE_SPECIAL_CHARS );
			$filtered_nonce  = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS );
			// If action is set and nonce gets verified.
			if ( $filtered_action && $filtered_nonce && wp_verify_nonce( $filtered_nonce, $action . $filtered_action ) ) {
				return $filtered_action;
			} else {
				return null;
			}
		}

		/**
		 * Label Field.
		 *
		 * @return void
		 */
		public function setting_label_fn() {
			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			$site    = $this->get_preview_site( $edit_id );
			$value   = $edit_id ? $site['label'] : '';
			?>
			<input id="plugin_text_label" name="preview_sites[label]" size="60" type="text" value="<?php esc_attr( $value ); ?>" required /><br>
			<span class="description"><?php esc_html_e( '[Required] Label for the preview site.', 'wp-decoupled-preview' ); ?></span>
			<?php
		}

		/**
		 * URL Field.
		 *
		 * @return void
		 */
		public function setting_url_fn() {
			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			$site    = $this->get_preview_site( $edit_id );
			$value   = $edit_id ? $site['url'] : '';
			?>
			<input id="plugin_text_url" name="preview_sites[url]" size="60" type="url" value="<?php esc_attr( $value ); ?>" required /><br>
			<span class="description"><?php esc_html_e( '[Required] URL for the preview site.', 'wp-decoupled-preview' ); ?></span>
			<?php
		}

		/**
		 * Secret Field.
		 *
		 * @return void
		 */
		public function setting_secret_fn() {
			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			ob_start();
			if ( $edit_id ) {
				?>
				<input id="plugin_text_secret" name="preview_sites[secret_string]" size="40" type="password" /><br>
				<span class="description"><?php esc_html_e( 'Shared secret for the preview site. When editing, if kept empty the old value will be saved, otherwise it will be overwritten.', 'wp-decoupled-preview' ); ?></span>
				<?php
			} else {
				?>
				<input id="plugin_text_secret" name="preview_sites[secret_string]" size="40" type="password" required /><br>
				<span class="description"><?php esc_html_e( '[Required] Shared secret for the preview site.', 'wp-decoupled-preview' ); ?></span>
				<?php
			}
			$html = ob_get_clean();
			// Even though we don't necessarily need to use wp_kses here, because there's no input that's coming from an untrusted source, we'll do it anyway and not have to turn off the sniffs.
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
			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			$site    = $this->get_preview_site( $edit_id );
			$items   = [ __( 'Next.js', 'wp-decoupled-preview' ) ];
			?>
			<select id="preview_type" name="preview_sites[preview_type]" required>
				<?php
				foreach ( $items as $item ) {
					$selected = ( $site['preview_type'] === $item ) ? 'selected="selected"' : '';
					?>
					<option value="<?php echo esc_attr( $item ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $item ); ?></option>
					<?php
				}
				?>
			</select><br>
			<?php
			esc_html_e( '[Required] Preview type for the front-end.', 'wp-decoupled-preview' );
		}

		/**
		 * Content Type Field.
		 *
		 * @return void
		 */
		public function setting_content_type_fn() {
			$edit_id = isset( $_GET['edit'] ) ? esc_attr( $_GET['edit'] ) : false; // Add nonce.
			$items   = [ __( 'Post', 'wp-decoupled-preview' ), __( 'Page', 'wp-decoupled-preview' ) ];
			$site    = $this->get_preview_site( $edit_id );
			foreach ( $items as $item ) {
				if ( isset( $edit_id ) && isset( $site['content_type'] ) ) {
					$checked = ( in_array( strtolower( $item ), $site['content_type'], true ) ) ? ' checked="checked" ' : '';
					?>
					<label><input <?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $item ); ?>" name="preview_sites[content_type][]" type="checkbox" /><?php echo esc_html( $item ); ?></label><br />
					<?php
				} else {
					?>
					<label><input value="<?php echo esc_attr( $item ); ?>" name="preview_sites[content_type][]" type="checkbox" /><?php echo esc_html( $item ); ?></label><br />
					<?php
				}
			}
			esc_html_e( 'If no content types are specified, the preview site should display for all content types.', 'wp-decoupled-preview' );
		}

		/**
		 * Get the List of the configured sites or specific site.
		 *
		 * @param int|null $id (Optional) id for the preview config.
		 *
		 * @return array
		 *   Return a list of sites | Only a specific site.
		 */
		public function get_preview_site( int $id = null ): ?array {
			$preview_sites = get_option( 'preview_sites' );
			$last_key      = array_key_last( $preview_sites['preview'] );
			if ( $preview_sites && isset( $preview_sites['preview'][ $last_key ]['label'] ) ) {
				$preview_sites = array_shift( $preview_sites );
				if ( isset( $id ) ) {
					return $preview_sites[ $id ];
				} else {
					return $preview_sites;
				}
			}
			return null;
		}

		/**
		 * Delete preview site.
		 *
		 * @param int|null $site_id (Optional) site id.
		 *
		 * @return void
		 */
		public function delete_preview_site( int $site_id = null ) {
			$sites = $this->get_preview_site();
			delete_option( 'preview_sites' );
			if ( isset( $site_id ) ) {
				unset( $sites[ $site_id ] );
				if ( empty( $sites ) ) {
					$sites[1] = [
						'label'         => null,
						'url'           => null,
						'secret_string' => null,
					];
				}
				add_option( 'preview_sites', $sites );
			}
		}

		/**
		 * Get enabled preview site by post type.
		 *
		 * @param string $post_type Post type.
		 *
		 * @return array|null
		 */
		public function get_enabled_site_by_post_type( string $post_type ): ?array {
			$sites        = $this->get_preview_site();
			$enable_sites = [];
			if ( ! empty( $sites ) ) {
				foreach ( $sites as $site ) {
					if ( empty( $site['content_type'] ) || in_array( $post_type, $site['content_type'], true ) ) {
						$enable_sites[] = $site;
					}
				}
				return $enable_sites;
			}
			return null;
		}

	}
}
