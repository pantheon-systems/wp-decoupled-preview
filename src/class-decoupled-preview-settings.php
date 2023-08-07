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
			add_action( 'admin_head', [ &$this, 'override_styles' ] );
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
			add_settings_field(
				'plugin_dropdown_user',
				esc_html__( 'Associated User', 'wp-decoupled-preview' ),
				[ &$this, 'setting_associated_user_fn' ],
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field(
				'plugin_hidden',
				'',
				[ &$this, 'setting_hidden_fn' ],
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
				[ $this, 'preview_delete_html' ]
			);
		}

		/**
		 * Add some CSS overrides.
		 *
		 * @return void
		 */
		public function override_styles() {
			$screen = get_current_screen();
			$css = '<style>';
			// Hide the empty submenu item.
			$css .= '#adminmenu .wp-submenu li a[href="options-general.php?page=add_preview_sites"] {
				display: none;
			}';

			if ( $screen->id === 'settings_page_preview_sites' ) {
				// Give the URL column a little more room.
				$css .= '.wp-list-table .column-url {
					width: 25%;
				}';
			}

			$css .= '</style>';
			// We can ignore escaping this output because it only contains code that we've hard coded with no possibility of user input.
			echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * List all preview site.
		 *
		 * @return void
		 */
		public function list_preview() {
			add_options_page(
				__( 'Preview Sites Configuration', 'wp-decoupled-preview' ),
				__( 'Preview Sites', 'wp-decoupled-preview' ),
				'manage_options',
				'preview_sites',
				[ &$this, 'preview_list_html' ]
			);
		}

		/**
		 * HTML for create from.
		 *
		 * @return void
		 */
		public function preview_create_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}

			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			if ( $edit_id ) {
				$action = 'options.php?edit=' . $edit_id;
			} else {
				$action = 'options.php';
			}

			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Create or Edit Preview Site', 'wp-decoupled-preview' ); ?></h1>
				<p><a href="<?php echo esc_url( add_query_arg( 'page', 'preview_sites', admin_url( 'options-general.php' ) ) ); ?>">&larr; <?php esc_html_e( 'Back to Preview Sites Configuration', 'wp-decoupled-preview' ); ?></a></p>
				<form action="<?php echo esc_url( $action ); ?>" method="post">
					<?php
					settings_fields( 'wp-decoupled-preview' );
					do_settings_sections( 'preview_sites' );
					?>
					<?php wp_nonce_field( 'edit-preview-site', 'nonce' ); ?>
					<?php submit_button(); ?>
					<?php
					if ( $edit_id ) {
						$site_label = $this->get_preview_site( $edit_id )['label'];
						$url = wp_nonce_url(
							add_query_arg( [
								'page' => 'delete_preview_site',
								'id' => $edit_id,
							], admin_url( 'options-general.php' ) ),
							'edit-preview-site',
							'nonce'
						);
						?>
						<a id="delete-preview" class="button-secondary button-large" href="<?php echo esc_url( $url ); ?>">
							<?php
							echo esc_html(
								// Translators: %s is the preview site label.
								sprintf( __( 'Delete %s', 'wp-decoupled-preview' ), $site_label )
							);
							?>
						</a>
						<?php
					}
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * The delete subpage to handle the delete request.
		 *
		 * @return void
		 */
		public function preview_delete_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}

			check_admin_referer( 'edit-preview-site', 'nonce' );
			$delete_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;

			if ( ! $delete_id ) {
				wp_die( esc_html__( 'Unable perform action: Site not found.', 'wp-decoupled-preview' ) );
			}

			$this->delete_preview_site( $delete_id );
			echo '<script type="text/javascript">window.location = "options-general.php?page=preview_sites"</script>';
		}

		/**
		 * HTML for list preview sites settings page.
		 *
		 * @return void
		 */
		public function preview_list_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-decoupled-preview' ) );
			}

			// Check if the List_Table class is available.
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}
			require_once plugin_dir_path( __FILE__ ) . 'class-list-table.php';
			$add_site_url = wp_nonce_url(
				add_query_arg( [
					'page' => 'add_preview_sites',
				], admin_url( 'options-general.php' ) ),
				'edit-preview-site',
				'nonce'
			);
			?>
			<div class="wrap">
				<div>
					<span style="display: flex;">
						<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
						<a href="<?php echo esc_url_raw( $add_site_url ); ?>" class="button-primary" style="margin-left: auto; height: 1.2em; margin-top: 9px">+ <?php esc_html_e( 'Add Preview Site', 'wp-decoupled-preview' ); ?></a>
					</span>
			<?php
			wp_create_nonce( 'preview-site-list' );
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
			check_admin_referer( 'edit-preview-site', 'nonce' );
			// Bail early if no input.
			if ( empty( $input ) ) {
				return [];
			}

			// Switch to preview sites sanitization if we're deleting a site.
			if ( isset( $_GET['page'] ) && 'delete_preview_site' === $_GET['page'] && isset( $input['preview'] ) ) {
				// Already santized.
				return $input;
			}

			// Bail early if we are missing required data.
			if ( in_array( '', [ $input['label'], $input['url'], $input['preview_type'], $input['secret_string'] ], true ) ) {
				return [];
			}
			$options = get_option( 'preview_sites' );
			$sanitized_input = [];
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : null;
			// Set Content type in correct format.
			if ( isset( $input['content_type'] ) ) {
				foreach ( $input['content_type'] as $key => $type ) {
					$type = strtolower( $type );
					if ( in_array( $type, $this->get_allowed_post_types(), true ) ) {
						$sanitized_input['content_type'][ $key ] = sanitize_text_field( $type );
					}
				}
			}

			if ( $options && isset( $edit_id ) ) {
				// If we're editing an existing site, check to make sure there's an ID set.
				if ( empty( $input['id'] ) ) {
					$sanitized_input['id'] = $this->validate_preview_id( $edit_id, $options );
				}
			}

			$input['id'] = isset( $input['id'] ) ? absint( $input['id'] ) : 0;
			// If an input id was passed, just sanitize the ID.
			$sanitized_input['id'] = isset( $sanitized_input['id'] ) ? absint( $sanitized_input['id'] ) : $this->validate_preview_id( $input['id'], $options );
			$sanitized_input['label'] = sanitize_text_field( $input['label'] );
			$sanitized_input['url'] = esc_url_raw( $input['url'] );
			$sanitized_input['preview_type'] = $this->sanitize_preview_type( $input['preview_type'] );
			// Sanitize the secret string if it was added.
			if ( isset( $input['secret_string'] ) ) {
				$sanitized_input['secret_string'] = sanitize_text_field( $input['secret_string'] );
			}
			if ( empty( $input['associated_user'] ) ) {
				$sanitized_input['associated_user'] = '';
			} else {
				$sanitized_input['associated_user'] = sanitize_text_field( $input['associated_user'] );
			}
			$edit_id = ! isset( $edit_id ) || $edit_id !== $sanitized_input['id'] ? $sanitized_input['id'] : $edit_id;

			$options['preview'][ $edit_id ] = $sanitized_input;
			return $options;
		}

		/**
		 * Return an array of allowed post types.
		 *
		 * TODO: This function should be refactored to pull a list of public
		 * post types to add support for custom post types.
		 *
		 * @return array
		 */
		public function get_allowed_post_types() : array {
			/**
			 * Allow the allowable post types to be filtered.
			 *
			 * Usage:
			 * add_filter( 'pantheon.dp.allowed_post_types', function( $allowed_types ) {
			 *   $allowed_types[] = 'my_custom_post_type';
			 *  return $allowed_types;
			 * } );
			 */
			return apply_filters( 'pantheon.dp.allowed_post_types', [ 'post', 'page' ] );
		}

		/**
		 * Sanitize the preview type.
		 *
		 * Currently only Next.js is supported but additional types can be
		 * added via the pantheon.db.allowed_preview_types filter.
		 *
		 * @param string $type The preview type to sanitize.
		 *
		 * @return string
		 */
		public function sanitize_preview_type( string $type ) : string {
			/**
			 * Allow the allowable preview types to be filtered.
			 *
			 * Usage:
			 * add_filter( 'pantheon.dp.allowed_preview_types', function( $allowed_types ) {
			 *    $allowed_types[] = 'My Custom Type';
			 *   return $allowed_types;
			 * } );
			 *
			 * @param array $allowed_types Array of allowed preview types.
			 *
			 * @return array
			 */
			$allowed_types = apply_filters( 'pantheon.dp.allowed_preview_types', [ 'Next.js' ] );

			if ( in_array( $type, $allowed_types, true ) ) {
				return sanitize_text_field( $type );
			}

			return '';
		}

		/**
		 * Validate the preview id.
		 *
		 * Ensures a valid ID is set for the preview site. If a non-zero value
		 * is passed, it will be returned. Otherwise, we check for the
		 * existence of other sites and attempt to assign a valid ID.
		 *
		 * @param int $edit_id The ID of the site to validate.
		 * @param string|array $options The array of saved sites. Optional but recommended.
		 *
		 * @return int
		 */
		public function validate_preview_id( int $edit_id, $options = [] ) : int {
			if ( empty( $options ) ) {
				$options = get_option( 'preview_sites' );
			}

			// If we don't have saved sites, and we're adding a new site, set the ID.
			if ( ! $options && $edit_id === 0 ) {
				$edit_id = 1;
			}

			// If we're adding a new site and have options, set the ID to one higher than the highest existing ID.
			if ( $options && $edit_id < 1 ) {
				$edit_id = absint( max( wp_list_pluck( $options['preview'], 'id' ) ) ) + 1;
			}

			return $edit_id;
		}

		/**
		 * Label Field.
		 *
		 * @return void
		 */
		public function setting_label_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;
			$site = $this->get_preview_site( $edit_id );
			$value = $edit_id ? $site['label'] : '';
			?>
			<input id="plugin_text_label" name="preview_sites[label]" size="60" type="text" value="<?php echo esc_attr( $value ); ?>" required /><br />
			<span class="description"><?php esc_html_e( '(Required) Label for the preview site.', 'wp-decoupled-preview' ); ?></span>
			<?php
		}

		/**
		 * URL Field.
		 *
		 * @return void
		 */
		public function setting_url_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			$site = $this->get_preview_site( $edit_id );
			$value = $edit_id ? $site['url'] : '';
			?>
			<input id="plugin_text_url" name="preview_sites[url]" size="60" type="url" value="<?php echo esc_url_raw( $value ); ?>" required /><br />
			<span class="description"><?php esc_html_e( '(Required) URL for the preview site.', 'wp-decoupled-preview' ); ?></span>
			<?php
		}

		/**
		 * Secret Field.
		 *
		 * @return void
		 */
		public function setting_secret_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			$site = $this->get_preview_site( $edit_id );
			$value = $edit_id ? $site['secret_string'] : '';
			ob_start();
			if ( $edit_id ) {
				?>
				<input id="plugin_text_secret" name="preview_sites[secret_string]" size="40" type="password" value="<?php echo esc_attr( $value ); ?>" required /><br />
				<span class="description"><?php esc_html_e( '(Required) Shared secret for the preview site.', 'wp-decoupled-preview' ); ?></span>
				<?php
			} else {
				?>
				<input id="plugin_text_secret" name="preview_sites[secret_string]" size="40" type="password" required /><br />
				<span class="description"><?php esc_html_e( '(Required) Shared secret for the preview site.', 'wp-decoupled-preview' ); ?></span>
				<?php
			}
			$html = ob_get_clean();
			// Even though we don't necessarily need to use wp_kses here, because there's no input that's coming from an untrusted source, we'll do it anyway and not have to turn off the sniffs.
			echo wp_kses(
				$html,
				[
					'input' => [
						'id' => [],
						'name' => [],
						'size' => [],
						'type' => [],
						'value' => [],
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
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			$site = $this->get_preview_site( $edit_id );
			$items = [ __( 'Next.js', 'wp-decoupled-preview' ) ];
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
			</select><br />
			<?php
			esc_html_e( '(Required) Preview type for the front-end.', 'wp-decoupled-preview' );
		}

		/**
		 * Content Type Field.
		 *
		 * @return void
		 */
		public function setting_content_type_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			$items = [ __( 'Post', 'wp-decoupled-preview' ), __( 'Page', 'wp-decoupled-preview' ) ];
			$site = $this->get_preview_site( $edit_id );
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
		 * Hidden Field.
		 *
		 * @return void
		 */
		public function setting_hidden_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			?>
			<input id="plugin_hidden" name="preview_sites[id]" size="40" type="hidden" value="<?php echo $edit_id ? absint( $edit_id ) : ''; ?>" />
			<?php
		}

		/**
		 * Associated User
		 * 
		 * @return void
		 */
		public function setting_associated_user_fn() {
			check_admin_referer( 'edit-preview-site', 'nonce' );
			$edit_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : false;
			$site = $this->get_preview_site( $edit_id );
			$users = get_users( [ 'fields' => [ 'display_name' ] ] );
			if ( isset( $edit_id ) ) {
				?>
				<label for="associated_user">Choose an associated user:</label>
				<select id="associated_user" name="preview_sites[associated_user]" autocomplete="username">
					<option value="" selected="<?php $site['associated_user'] === ''; ?>">--None--</option>
					<?php
					foreach ( $users as $user ) {
						$selected = ( $site['associated_user'] === $user->display_name ) ? 'selected="selected"' : '';
						?>
						<option value="<?php echo esc_attr( $user->display_name ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $user->display_name ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}
		}

		/**
		 * Get the List of the configured sites or specific site.
		 *
		 * @param int|null $id (Optional) id for the preview config.
		 *
		 * @return array
		 *   Return a list of sites | Only a specific site.
		 */
		public function get_preview_site( int $id = null ): array {
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$preview_sites = get_option( 'preview_sites' );

			if ( ! $preview_sites || ! isset( $preview_sites['preview'] ) ) {
				return [];
			}

			if ( $id ) {
				if ( ! isset( $preview_sites['preview'][ $id ] ) ) {
					return [];
				}

				return $preview_sites['preview'][ $id ];
			}


			return $preview_sites;
		}

		/**
		 * Delete preview site.
		 *
		 * Wraps around remove_site_from_list and actually updates the option.
		 *
		 * @param int|null $site_id (Optional) site id.
		 *
		 * @return void
		 */
		public function delete_preview_site( int $site_id = null ) {
			update_option( 'preview_sites', $this->remove_site_from_list( $site_id ) );
		}

		/**
		 * Handles the logic for removing a site from a saved list of preview sites.
		 *
		 * @param int|null $site_id (Optional) site id.
		 *
		 * @return array
		 */
		public function remove_site_from_list( int $site_id = null ) : array {
			$site = $this->get_preview_site( $site_id );
			$sites = get_option( 'preview_sites' );
			$preview_sites = $sites['preview'];

			// Check if the site we're deleting exists in the list of sites.
			$preview_sites = $this->filter_preview_sites( $preview_sites );

			foreach ( $preview_sites as $site_key => $site_to_check ) {
				// Check for an ID. This should be a more direct match but it was added later so it might not exist.
				if (
					( isset( $site['id'] ) && isset( $site_to_check['id'] ) ) &&
					! in_array( '', [ $site_to_check['id'], $site['id'] ], true )
				) {
					if ( $site_to_check['id'] === $site['id'] ) {
						unset( $preview_sites[ $site_key ] );
					}
				} elseif ( isset( $site_to_check['id'] ) && '' === $site_to_check['id'] ) {
					// If there wasn't an ID, save one.
					$preview_sites[ $site_key ]['id'] = $site_key;
				}

				// If we didn't find a key, we'll go by label. It should just be the next best thing.
				if (
					( isset( $site_to_check['label'] ) && isset( $site['label'] ) ) &&
					$site_to_check['label'] === $site['label']
				) {
					unset( $preview_sites[ $site_key ] );
				}
			}

			// Unset the old list of sites and set the new one.
			unset( $sites['preview'] );
			sort( $preview_sites );
			$sites['preview'] = $preview_sites;
			return $sites;
		}

		/**
		 * Check preview sites array for bad data.
		 * Remove offending data from the array.
		 *
		 * @param array $preview_sites The array of preview sites.
		 *
		 * @return array The updated array of preview sites.
		 */
		public function filter_preview_sites( array $preview_sites ) : array {
			if ( count( $preview_sites ) < 1 ) {
				return $preview_sites;
			}

			$updated_preview_sites = $preview_sites;
			foreach ( $updated_preview_sites as $site_key => $site_to_check ) {
				// Check for a label. If one does not exist, remove the site.
				if ( ! isset( $site_to_check['label'] ) ) {
					unset( $updated_preview_sites[ $site_key ] );
				}

				// Check for corrupted data. These are records that were stored mistakenly.
				if ( isset( $site_to_check['preview'] ) ) {
					unset( $updated_preview_sites[ $site_key ] );
				}

				// Check if the site we're on or the site we're checking is missing required data. If so, remove them.
				if (
					empty( $site_to_check ) ||
					empty( $site_to_check['label'] )
				) {
					unset( $updated_preview_sites[ $site_key ] );
				}
			}

			return $updated_preview_sites;
		}

		/**
		 * Get enabled preview site by post type.
		 *
		 * @param string $post_type Post type.
		 *
		 * @return array|null
		 */
		public function get_enabled_site_by_post_type( string $post_type ): ?array {
			$sites = $this->get_preview_site();
			$enable_sites = [];
			if ( ! empty( $sites ) ) {
				foreach ( $sites['preview'] as $site ) {
					if ( ! isset( $site['content_type'] ) || isset( $site['content_type'] ) && in_array( $post_type, $site['content_type'], true ) ) {
						$enable_sites[] = $site;
					}
				}
				return $enable_sites;
			}
			return null;
		}
	}
}
