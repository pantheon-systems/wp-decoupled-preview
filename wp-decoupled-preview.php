
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
    add_options_page( 'Preview Site Configuration', 'Preview Site', 'manage_options', __FILE__, 'wp_decoupled_preview_html' );
}

function wp_decoupled_preview_html() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>Preview Site Configuration</h2>
        <form action="options.php" method="post">
            <?php settings_fields('plugin_options'); ?>
            <?php do_settings_sections(__FILE__); ?>
            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

// Register our settings. Add the settings section, and settings fields
function wp_decoupled_preview_init_fn(){
    register_setting('plugin_options', 'plugin_options' );
    add_settings_section('main_section', 'Main Settings', 'section_text_fn', __FILE__);
    add_settings_field('plugin_text_label', 'Label', 'setting_label_fn', __FILE__, 'main_section');
    add_settings_field('plugin_text_url', 'URL', 'setting_url_fn', __FILE__, 'main_section');
    add_settings_field('plugin_text_secret', 'Secret', 'setting_secret_fn', __FILE__, 'main_section');
    add_settings_field('plugin_drop_down', 'Preview Type', 'setting_preview_type_fn', __FILE__, 'main_section');
    add_settings_field('plugin_checkbox', 'Content Type', 'setting_content_type_fn', __FILE__, 'main_section');

    add_settings_section('listing_section', 'Listing all Configs', 'section_listing_fn', __FILE__);
    add_settings_field('plugin_table', 'Table', 'setting_table_fn', __FILE__, 'listing_section');
}

// Callback functions

function  section_listing_fn() {
    echo '<p>List of preview sites to extend site functionality.</p>';
}

function section_text_fn() {
    echo '<p>Fill the form below and create a new Preview config.</p>';
}

function  setting_preview_type_fn() {
    $options = get_option('plugin_options');
    $items = array("Next.js");
    echo "<select id='type_drop_down' name='plugin_options[type_drop_down]'>";
    foreach($items as $item) {
        $selected = ($options['dropdown']==$item) ? 'selected="selected"' : '';
        echo "<option value='$item' $selected>$item</option>";
    }
    echo "</select>";
    echo "<br>[Required] Preview type for the frontend.";
}

function setting_content_type_fn() {
    $options = get_option('plugin_options');
    $items = array("Post", "Page");
    foreach($items as $item) {
        $checked = ($options['content_type']==$item) ? ' checked="checked" ' : '';
        echo "<label><input ".$checked." value='$item' name='plugin_options[content_type]' type='checkbox' /> $item</label><br />";
    }
    echo "If no content types are specified, the preview site should display for all content types.";
}

function setting_label_fn() {
    $options = get_option('plugin_options');
    echo "<input id='plugin_text_lable' name='plugin_options[plugin_text_label]' size='60' type='text' value='{$options['plugin_text_label']}' required />";
    echo "<br>[Required] Label for the preview site.";
}

function setting_url_fn() {
    $options = get_option('plugin_options');
    echo "<input id='plugin_text_url' name='plugin_options[plugin_text_url]' size='60' type='text' value='{$options['plugin_text_url']}' required />";
    echo "<br>[Required] URL for the preview site.";
}

function setting_secret_fn() {
    $options = get_option('plugin_options');
    echo "<input id='plugin_text_secret' name='plugin_options[secret_string]' size='40' type='password' value='{$options['secret_string']}' required />";
    echo "<br>[Required] Shared secret for the preview site.";
}

function setting_table_fn() {
    $options = get_option('plugin_options');
    $listing_data['plugin_text_label'] = $options['plugin_text_label'];
    $listing_data['plugin_text_url'] = $options['plugin_text_url'];
    $listing_data['type_drop_down'] = $options['type_drop_down'];
    $listing_data['content_type'] = $options['content_type'];
    ?>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <td>Label</td>
            <td>Status</td>
            <td>Preview Type</td>
            <td>Content Type</td>
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
