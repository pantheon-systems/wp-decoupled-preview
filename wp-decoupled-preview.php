<?php
/**
 * Plugin Name:     Pantheon Decoupled Preview
 * Plugin URI:      https://pantheon.io/
 * Description:     Preview WordPress content on Front-end sites including Next.js
 * Version:         0.1.0
 * Author:          Pantheon
 * Author URI:      https://pantheon.io/
 * Text Domain:     wp-decoupled-preview
 * Domain Path:     /languages
 *
 * @package         Pantheon_Decoupled
 */

add_action('admin_init', 'wp_decoupled_preview_init_fn' );
add_action( 'admin_menu', 'wp_decoupled_preview_menu' );


function wp_decoupled_preview_menu() {
    add_options_page( 'Create Preview Site', 'Create Preview Site', 'manage_options', 'wp_decoupled_preview_create', 'wp_decoupled_preview_create_html' );
    add_options_page( 'Preview Site Configuration', 'Preview Site Configuration', 'manage_options', 'wp_decoupled_preview_list', 'wp_decoupled_preview_list_html' );
}

function wp_decoupled_preview_list_html() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    add_settings_section('listing_section', 'Listing all Configs', 'section_listing_fn', __FILE__);
    $options = get_option('preview');
    $listing_data['label'] = $options['label'];
    $listing_data['url'] = $options['url'];
    $listing_data['preview_type'] = $options['preview_type'];
    $listing_data['content_type'] = empty($options['content_type']) ? 'ALL': $options['content_type'];
    $listing_data['edit'] = "<a href='/wp/wp-admin/options-general.php?page=wp_decoupled_preview_create'>Edit</a";
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
        <tr>
            <?php
            foreach ($listing_data as $data) {
                ?>
                <td><?php echo $data ?></td>
                <?php
            }
            ?>
        </tr>
        </thead>
    </table>
    <?php
}

function wp_decoupled_preview_create_html() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
        <h2>Create Preview Site Configuration</h2>
        <form action="options.php" method="post">
            <?php settings_fields('preview_configurations'); ?>
            <?php do_settings_sections(__FILE__); ?>
            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

// Register our settings. Add the settings section, and settings fields
function wp_decoupled_preview_init_fn() {
    register_setting('preview_configurations', 'preview');
    add_settings_section('main_section', 'Main Settings', 'section_text_fn', __FILE__);
    add_settings_field('plugin_text_label', 'Label', 'setting_label_fn', __FILE__, 'main_section');
    add_settings_field('plugin_text_url', 'URL', 'setting_url_fn', __FILE__, 'main_section');
    add_settings_field('plugin_text_secret', 'Secret', 'setting_secret_fn', __FILE__, 'main_section');
    add_settings_field('plugin_drop_down', 'Preview Type', 'setting_preview_type_fn', __FILE__, 'main_section');
    add_settings_field('plugin_checkbox', 'Content Type', 'setting_content_type_fn', __FILE__, 'main_section');
}

// Callback functions

function  section_listing_fn() {
    echo '<p>List of preview sites to extend site functionality.</p>';
}

function section_text_fn() {
    echo '<p>Fill the form below and create a new Preview config.</p>';
}

function  setting_preview_type_fn() {
    $options = get_option('preview');
    $items = array("Next.js");
    echo "<select id='preview_type' name='preview[preview_type]'>";
    foreach($items as $item) {
        $selected = ($options['dropdown']==$item) ? 'selected="selected"' : '';
        echo "<option value='$item' $selected>$item</option>";
    }
    echo "</select>";
    echo "<br>[Required] Preview type for the frontend.";
}

function setting_content_type_fn() {
    $options = get_option('preview');
    $items = array("Post", "Page");
    foreach($items as $item) {
        $checked = ($options['content_type']==$item) ? ' checked="checked" ' : '';
        echo "<label><input ".$checked." value='$item' name='preview[content_type]' type='checkbox' /> $item</label><br />";
    }
    echo "If no content types are specified, the preview site should display for all content types.";
}

function setting_label_fn() {
    $options = get_option('preview');
    echo "<input id='plugin_text_lable' name='preview[label]' size='60' type='text' value='{$options['label']}' required />";
    echo "<br>[Required] Label for the preview site.";
}

function setting_url_fn() {
    $options = get_option('preview');
    echo "<input id='plugin_text_url' name='preview[url]' size='60' type='text' value='{$options['url']}' required />";
    echo "<br>[Required] URL for the preview site.";
}

function setting_secret_fn() {
    $options = get_option('preview');
    echo "<input id='plugin_text_secret' name='preview[secret_string]' size='40' type='password' value='{$options['secret_string']}' required />";
    echo "<br>[Required] Shared secret for the preview site.";
}
