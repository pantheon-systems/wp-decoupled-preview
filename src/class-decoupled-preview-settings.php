<?php
/**
 * Create/Edit form.
 *
 * @file
 *
 * @package wp-decoupled-preview\Decoupled_Preview_Settings
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
			add_action( 'admin_menu', array( &$this, 'add_preview_subpage' ) );
			add_action( 'admin_menu', array( &$this, 'delete_preview_subpage' ) );
		}

		/**
		 * Init all the required form settings & fields.
		 *
		 * @return void
		 */
		public function admin_init() {
			register_setting( 'wp-decoupled-preview', 'preview_sites', array( $this, 'sanitize_callback_preview' ) );

			add_settings_section(
				'wp-decoupled-preview-section',
				'Create/Edit Preview Sites',
				array( &$this, 'settings_section_wp_decoupled_preview' ),
				'preview_sites'
			);

			add_settings_field(
				'plugin_text_label',
				'Label',
				array( &$this, 'setting_label_fn' ),
				'preview_sites',
				'wp-decoupled-preview-section'
			);
			add_settings_field( 'plugin_text_url', 'URL', array( &$this, 'setting_url_fn' ), 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_text_secret', 'Secret', array( &$this, 'setting_secret_fn' ), 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_drop_down', 'Preview Type', array( &$this, 'setting_preview_type_fn' ), 'preview_sites', 'wp-decoupled-preview-section' );
			add_settings_field( 'plugin_checkbox', 'Content Type', array( &$this, 'setting_content_type_fn' ), 'preview_sites', 'wp-decoupled-preview-section' );

		}

		/**
		 * Add preview form.
		 *
		 * @return void
		 */
		public function add_preview_subpage() {
			add_submenu_page(
				'',
				__( 'Preview Sites', 'wp-decoupled-preview' ),
				__( 'Preview Sites', 'wp-decoupled-preview' ),
				'manage_options',
				'add_preview_site',
				array( $this, 'wp_decoupled_preview_create_html' )
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
				__( 'Delete Preview Sites', 'wp-decoupled-preview' ),
				__( 'Preview Sites', 'wp-decoupled-preview' ),
				'manage_options',
				'delete_preview_site',
				[ $this, 'wp_decoupled_preview_delete_html' ]
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
				array( &$this, 'wp_decoupled_preview_list_html' )
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
				$action = 'options.php?edit=' . $edit_id;
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
						<?php
						if ( isset( $edit_id ) ) {
							$href = 'options-general.php?page=delete_preview_site&edit=' . $edit_id . '&delete=true';
							?>
							<a id="delete-preview" class="button-secondary button-large" href="<?php echo esc_html( $href ); ?>"><?php esc_attr_e( 'Delete' ); ?></a>
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
				wp_die( esc_html( 'You do not have sufficient permissions to access this page.' ) );
			}
			$edit_id = $this->get_edit_id();
			if ( $edit_id ) {
				$this->delete_preview_site( $edit_id );
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
				wp_die( esc_html( 'You do not have sufficient permissions to access this page.' ) );
			}
			$options  = get_option( 'preview_sites' );
			$last_key = array_key_last( $options['preview'] );
			if ( isset( $options['preview'][ $last_key ]['label'] ) ) {
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
							<td>URL</td>
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
								$listing_data['content_type'] = ucwords( implode( ', ', $option['content_type'] ) );
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
										array(
											'a' => array(
												'href'  => array(),
												'title' => array(),
											),
										)
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
		public function sanitize_callback_preview( array $input ) {
			// TODO: Processing form data with nonce verification.
			if ( $_GET['delete'] ) {
				return [ 'preview' => $input ];
			} else {
				$options = get_option( 'preview_sites' );
				// Set Content type in correct format.
				if ( isset( $input['content_type'] ) ) {
					foreach ( $input['content_type'] as $key => $type ) {
						$input['content_type'][ $key ] = strtolower( $type );
					}
				}

				$edit_id  = $this->get_edit_id();
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
			echo 'Create or Edit Preview Site';
		}

		/**
		 * Get the edit id from the URL parameters.
		 *
		 * @return mixed|null
		 */
		public function get_edit_id() {
			// TODO: Processing form data with nonce verification.
			if ( isset( $_GET['edit'] ) && sanitize_text_field( wp_unslash( $_GET['edit'] ) ) ) {
				return sanitize_text_field( wp_unslash( $_GET['edit'] ) );
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
				array(
					'input' => array(
						'id'       => array(),
						'name'     => array(),
						'size'     => array(),
						'type'     => array(),
						'value'    => array(),
						'required' => array(),
					),
					'br'    => array(),
				)
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
				array(
					'input' => array(
						'id'       => array(),
						'name'     => array(),
						'size'     => array(),
						'type'     => array(),
						'value'    => array(),
						'required' => array(),
					),
					'br'    => array(),
				)
			);
		}

		/**
		 * Secret Field.
		 *
		 * @return void
		 */
		public function setting_secret_fn() {
			$edit_id = $this->get_edit_id();
			$html    = isset( $edit_id ) ? "<input id='plugin_text_secret' name='preview_sites[secret_string]' size='40' type='password' /><br>Shared secret for the preview site. When editing, if kept empty the old value will be saved, otherwise it will be overwritten." : "<input id='plugin_text_secret' name='preview_sites[secret_string]' size='40' type='password' required /><br>[Required] Shared secret for the preview site.";
			echo wp_kses(
				$html,
				array(
					'input' => array(
						'id'       => array(),
						'name'     => array(),
						'size'     => array(),
						'type'     => array(),
						'value'    => array(),
						'required' => array(),
					),
					'br'    => array(),
				)
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
			$items   = array( 'Next.js' );
			echo wp_kses(
				"<select id='preview_type' name='preview_sites[preview_type]' required>",
				array(
					'select' => array(
						'id'       => array(),
						'name'     => array(),
						'required' => array(),
					),
				)
			);
			foreach ( $items as $item ) {
				$selected = ( $site['preview_type'] === $item ) ? 'selected="selected"' : '';
				echo wp_kses(
					"<option value='$item' $selected>$item</option>",
					array(
						'option' => array(
							'value' => array(),
						),
					)
				);
			}
			echo wp_kses(
				'</select>',
				array(
					'select' => array(),
				)
			);
			echo wp_kses(
				'<br>[Required] Preview type for the front-end.',
				array(
					'br' => array(),
				)
			);
		}

		/**
		 * Content Type Field.
		 *
		 * @return void
		 */
		public function setting_content_type_fn() {
			$edit_id = $this->get_edit_id();
			$items   = array( 'Post', 'Page' );
			$site    = $this->get_preview_site( $edit_id );
			foreach ( $items as $item ) {
				if ( isset( $edit_id ) && isset( $site['content_type'] ) ) {
					$checked = ( in_array( strtolower( $item ), $site['content_type'], true ) ) ? ' checked="checked" ' : '';
					echo wp_kses(
						'<label> <input ' . $checked . " value='$item' name='preview_sites[content_type][]' type='checkbox' /> $item </label><br />",
						array(
							'input' => array(
								'id'      => array(),
								'checked' => array(),
								'name'    => array(),
								'size'    => array(),
								'type'    => array(),
								'value'   => array(),
							),
							'br'    => array(),
							'label' => array(),
						)
					);
				} else {
					echo wp_kses(
						"<label> <input value='$item' name='preview_sites[content_type][]' type='checkbox' /> $item </label><br />",
						array(
							'input' => array(
								'id'      => array(),
								'checked' => array(),
								'name'    => array(),
								'size'    => array(),
								'type'    => array(),
								'value'   => array(),
							),
							'br'    => array(),
							'label' => array(),
						)
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
			$enable_sites = array();
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


