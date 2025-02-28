<?php
// Enqueue custom styles and scripts
function my_woocommerce_plugin_enqueue_scripts($hook) {
    // Enqueue styles and scripts for the front end
    wp_enqueue_style('color-swatches-style', plugin_dir_url(__FILE__) . '../assets/css/color-swatches.css');
    wp_enqueue_script('color-swatches-script', plugin_dir_url(__FILE__) . '../assets/js/color-swatches.js', array('jquery'), null, true);

    // Enqueue styles and scripts for the admin settings page
    if ($hook == 'toplevel_page_custom-color-map-settings') {
        wp_enqueue_style('custom-color-map-settings-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
        wp_enqueue_script('custom-color-map-settings-admin-script', plugin_dir_url(__FILE__) . '../assets/js/admin-script.js', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'my_woocommerce_plugin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'my_woocommerce_plugin_enqueue_scripts');

// Add color swatches to the product page
function my_woocommerce_plugin_display_color_swatches() {
    global $product;

    if ($product->is_type('variable')) {
        $attributes = $product->get_variation_attributes();
        $colors = isset($attributes['pa_color'])?$attributes['pa_color']:array();

        $color_mappings = my_woocommerce_plugin_get_color_mappings();

        echo '<div class="color-swatches">';
        foreach ($colors as $color) {
            $class = sanitize_title($color); // Create a class name from the color name
            $style = isset($color_mappings[$color]) ? 'style="background-color:' . esc_attr($color_mappings[$color]) . ';"' : '';
            echo '<div class="swatch ' . esc_attr($class) . '" data-color="' . esc_attr($color) . '" ' . $style . '></div>';
        }
        echo '</div>';
    }
}
add_action('woocommerce_before_single_variation', 'my_woocommerce_plugin_display_color_swatches');

// Fetch custom color mappings
function my_woocommerce_plugin_get_color_mappings() {
    $color_mappings = get_option('my_woocommerce_plugin_color_mappings', array());
    return is_array($color_mappings) ? $color_mappings : array();
}

// Add settings page
function my_woocommerce_plugin_add_settings_page() {
    add_menu_page(
        'Color Swatches Settings',
        'Color Swatches',
        'manage_options',
        'custom-color-map-settings',
        'my_woocommerce_plugin_render_settings_page'
    );
}
add_action('admin_menu', 'my_woocommerce_plugin_add_settings_page');

function my_woocommerce_plugin_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Color Swatches Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_woocommerce_plugin_settings');
            do_settings_sections('custom-color-map-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function my_woocommerce_plugin_register_settings() {
    register_setting('my_woocommerce_plugin_settings', 'my_woocommerce_plugin_color_mappings', 'my_woocommerce_plugin_sanitize_color_mappings');

    add_settings_section(
        'my_woocommerce_plugin_settings_section',
        'Color Mappings',
        'my_woocommerce_plugin_settings_section_callback',
        'custom-color-map-settings'
    );

    add_settings_field(
        'my_woocommerce_plugin_color_mappings',
        'Color Mappings',
        'my_woocommerce_plugin_color_mappings_callback',
        'custom-color-map-settings',
        'my_woocommerce_plugin_settings_section'
    );
}
add_action('admin_init', 'my_woocommerce_plugin_register_settings');

function my_woocommerce_plugin_settings_section_callback() {
    echo '<p>Define custom color mappings for your product variations.</p>';
}

function my_woocommerce_plugin_sanitize_color_mappings($input) {
    // Sanitize each color mapping entry
    $sanitized_input = array();
    if (isset($input['new'])) {
        foreach ($input['new']['name'] as $index => $color_name) {
            $color_code = $input['new']['code'][$index];
            if (!empty($color_name) && !empty($color_code)) {
                $sanitized_input[sanitize_text_field($color_name)] = sanitize_text_field($color_code);
            }
        }
    }

    if (isset($input['existing'])) {
        foreach ($input['existing'] as $color_name => $color_code) {
            if (!empty($color_name) && !empty($color_code)) {
                $sanitized_input[sanitize_text_field($color_name)] = sanitize_text_field($color_code);
            }
        }
    }

    return $sanitized_input;
}

function my_woocommerce_plugin_color_mappings_callback() {
    $color_mappings = my_woocommerce_plugin_get_color_mappings();
    ?>
    <table id="color-mappings-table">
        <thead>
            <tr>
                <th>Color Name</th>
                <th>Color Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($color_mappings) && is_array($color_mappings)) {
                foreach ($color_mappings as $color => $code) {
                    ?>
                    <tr>
                        <td><input type="text" name="my_woocommerce_plugin_color_mappings[existing][<?php echo esc_attr($color); ?>]" value="<?php echo esc_attr($color); ?>" /></td>
                        <td><input type="text" name="my_woocommerce_plugin_color_mappings[existing][<?php echo esc_attr($color); ?>]" value="<?php echo esc_attr($code); ?>" /></td>
                        <td><button type="button" class="button remove-color">Remove</button></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
    <button type="button" class="button add-color">Add Color</button>
    <?php
}
?>
