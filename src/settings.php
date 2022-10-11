<?php
if (!class_exists('Decoupled_Preview_Settings')) {

    class Decoupled_Preview_Settings {
        public function __construct() {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'list_preview'));
	        add_action('admin_menu', array(&$this, 'add_preview'));
        }

        public function admin_init() {
	        register_setting('wp-decoupled-preview', 'preview_sites', [$this, 'sanitize_callback_preview']);

            add_settings_section(
                    'wp-decoupled-preview-section',
                    'Create/Edit Preview Sites',
                    [&$this, 'settings_section_wp_decoupled_preview'],
                    'preview_sites'
            );

	        add_settings_field('plugin_text_label', 'Label', [&$this, 'setting_label_fn'], 'preview_sites', 'wp-decoupled-preview-section');
	        add_settings_field('plugin_text_url', 'URL', [&$this, 'setting_url_fn'], 'preview_sites', 'wp-decoupled-preview-section');
	        add_settings_field('plugin_text_secret', 'Secret', [&$this, 'setting_secret_fn'], 'preview_sites', 'wp-decoupled-preview-section');
	        add_settings_field('plugin_drop_down', 'Preview Type', [&$this, 'setting_preview_type_fn'], 'preview_sites', 'wp-decoupled-preview-section');
	        add_settings_field('plugin_checkbox', 'Content Type', [&$this, 'setting_content_type_fn'], 'preview_sites', 'wp-decoupled-preview-section');

        }

		public function add_preview() {
			add_options_page( 'Preview Site', 'Create Preview Site', 'manage_options', 'add_preview_site', [&$this, 'wp_decoupled_preview_create_html'] );
		}
	    public function list_preview() {
		    add_options_page( 'List Decoupled Preview', 'Decoupled Preview', 'manage_options', 'preview_sites', [&$this, 'wp_decoupled_preview_list_html'] );
	    }

	    public function wp_decoupled_preview_create_html() {
		    if ( !current_user_can( 'manage_options' ) )  {
			    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		    }
            if ($this->getEditId()) {
	            $action = 'options.php?edit=' . $this->getEditId();
            }
            else {
	            $action = 'options.php';
            }

		    ?>
		    <div class="wrap">
			    <h2>Create Preview Site Configuration</h2>
			    <form action="<?php echo $action ?>" method="post">
				    <?php settings_fields('wp-decoupled-preview'); ?>
				    <?php do_settings_sections('preview_sites'); ?>
				    <p class="submit">
					    <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
				    </p>
			    </form>
		    </div>
		    <?php
	    }

        public function wp_decoupled_preview_list_html() {
            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
	        $options = get_option( 'preview_sites' );
	        if (!empty($options)) {
		        ?>
                <h2>Preview Site Configuration</h2>
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
                $options = array_shift($options);
                ?>
                <tbody>
                <?php
		        foreach ($options as $id => $option) {
			        $listing_data['label'] = $option['label'];
			        $listing_data['url'] = $option['url'];
			        $listing_data['preview_type'] = $option['preview_type'];
			        $listing_data['content_type'] = empty($option['content_type']) ? 'ALL' : $option['content_type'];
			        $listing_data['edit'] = "<a href='/wp/wp-admin/options-general.php?page=add_preview_site&edit={$id}'>Edit</a";
			        ?>

                    <tr>
				        <?php
				        foreach ($listing_data as $data) {
					        ?>
                            <td><?php echo $data ?></td>
					        <?php
				        }
				        ?>
                    </tr>
			        <?php
		        }
                ?>
                </tbody>
                </table>
                <?php
	        }
	        else {
		        echo 'NO PREVIEW SITE CONFIGURATION FOUND';
	        }
        }

	    public function sanitize_callback_preview($input) {
		    $options = get_option( 'preview_sites' );
            $editId = $this->getEditId();
		    if (!$options) {
			    $data[0] = [
				    'label' => $input['label'],
				    'url' => $input['url'],
				    'secret_string' => $input['secret_string'],
				    'preview_type' => $input['preview_type'],
				    'content_type' => $input['content_type'],
			    ];
			    return ['preview' => $data];
		    }
            if ($editId) {
	            $options['preview'][$editId] = $input;
	            return $options;
            }
		    $last_key = array_key_last( $options['preview'] );
		    $options['preview'][++$last_key] = $input;
		    return $options;
	    }

		public function settings_section_wp_decoupled_preview() {
			echo 'These settings do things for the WP Plugin Template.';
		}

        public function getEditId() {
	        if (isset($_GET['edit'])) {
		        return $_GET['edit'];
	        }
	        return NULL;
        }

	    public function setting_label_fn() {
		    $options = get_option( 'preview_sites' );
            $editId = $this->getEditId();
		    $value = $editId ? $options['preview'][$editId]['label'] : '';
		    echo "<input id='plugin_text_lable' name='preview_sites[label]' size='60' type='text'  value='{$value}' required />";
		    echo "<br>[Required] Label for the preview site.";
	    }

	    public function setting_url_fn() {
		    $options = get_option('preview_sites');
		    $editId = $this->getEditId();
		    $value = $editId ? $options['preview'][$editId]['url'] : '';
		    echo "<input id='plugin_text_url' name='preview_sites[url]' size='60' type='text' value='{$value}' required />";
		    echo "<br>[Required] URL for the preview site.";
	    }

	    public function setting_secret_fn() {
		    $options = get_option('preview_sites');
		    $editId = $this->getEditId();
		    $value = $editId ? $options['preview'][$editId]['secret_string'] : '';
		    echo "<input id='plugin_text_secret' name='preview_sites[secret_string]' size='40' type='password' value='{$value}' required />";
		    echo "<br>[Required] Shared secret for the preview site.";
	    }

	    public function setting_preview_type_fn() {
		    $options = get_option('preview_sites');
		    $editId = $this->getEditId();
		    $options && $options = array_shift($options);
		    $items = ["Next.js"];
		    echo "<select id='preview_type' name='preview_sites[preview_type]'>";
		    foreach($items as $item) {
			    $selected = ($options[$editId]['preview_type']==$item) ? 'selected="selected"' : '';
			    echo "<option value='$item' $selected>$item</option>";
		    }
		    echo "</select>";
		    echo "<br>[Required] Preview type for the frontend.";
	    }

	    public function setting_content_type_fn() {
		    $options = get_option('preview_sites');
		    $editId = $this->getEditId();
		    $options && $options = array_shift($options);
		    $items = ["Post", "Page"];
		    foreach($items as $item) {
			    $checked = ($options[$editId]['content_type']==$item) ? ' checked="checked" ' : '';
			    echo "<label><input ".$checked." value='$item' name='preview_sites[content_type]' type='checkbox' /> $item</label><br />";
		    }
		    echo "If no content types are specified, the preview site should display for all content types.";
	    }

    }
}


