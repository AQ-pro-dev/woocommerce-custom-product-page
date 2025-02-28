<?php
/*
Plugin Name: WooCommerce Custom Product Page
Plugin URI: http://example.com/
Description: A plugin to customize the WooCommerce product detail page.
Version: 1.0.0
Author: Abdul Qadeer
Author URI: http://example.com/
License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Activation hook
register_activation_hook(__FILE__, 'check_acf_pro_on_activation');

function check_acf_pro_on_activation() {
    // Check if ACF Pro is not defined or ACF version is less than pro version
    if ( !defined('ACF_PRO') || !class_exists('ACF') ) {
        // Deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        // Throw an error in the WordPress admin
        wp_die(
            __('Your Plugin Name requires ACF Pro to be installed and activated.', 'your-plugin-textdomain'),
            __('Plugin Activation Error', 'your-plugin-textdomain'),
            array('back_link' => true)
        );
    }
}

// Check if ACF Pro is active (for other plugin operations)
function check_acf_pro_active() {
    if ( !defined('ACF_PRO') || !class_exists('ACF') ) {
        // Add an admin notice if ACF Pro is not active
        add_action('admin_notices', 'acf_pro_required_notice');
    }
}
add_action('admin_init', 'check_acf_pro_active');

// Admin notice to inform the user
function acf_pro_required_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('Your Plugin Name requires ACF Pro to be installed and activated.', 'your-plugin-textdomain'); ?></p>
    </div>
    <?php
}

$qrcoderHandler = plugin_dir_path( __FILE__ ) .'includes/ajaxHandlingQrcode.php';
if ( file_exists( $qrcoderHandler ) ) {
    require ($qrcoderHandler);
}

$enqueue_css_js = plugin_dir_path( __FILE__ ) .'includes/enqueue_css_js.php';
if ( file_exists( $enqueue_css_js ) ) {
    require ($enqueue_css_js);
}
//variationHandling.php

$variationHandling = plugin_dir_path( __FILE__ ) .'includes/variationHandling.php';
if ( file_exists( $variationHandling ) ) {
    require ($variationHandling);
}
//creating custom posttype for QR code
$createPostType = plugin_dir_path( __FILE__ ) .'includes/createPostType.php';
if ( file_exists( $createPostType ) ) {
    require ($createPostType);
}



$process_engrave_fields = plugin_dir_path( __FILE__ ) .'includes/process_engrave_fields.php';
if ( file_exists( $process_engrave_fields ) ) {
    require ($process_engrave_fields);
}

$addACF = plugin_dir_path( __FILE__ ) .'includes/addACF.php';
if ( file_exists( $addACF ) ) {
    require ($addACF);
}

$order_update_hook = plugin_dir_path( __FILE__ ) .'includes/order_update_hook.php';
if ( file_exists( $order_update_hook ) ) {
    require ($order_update_hook);
}

$customColorMappings = plugin_dir_path( __FILE__ ) .'includes/customColorMappings.php';
if ( file_exists( $customColorMappings ) ) {
    require ($customColorMappings);
}

if(is_admin()){
    //qr_code_management.php
    $adminsidelogic = plugin_dir_path( __FILE__ ) .'admin/qr_code_management.php';
    if ( file_exists( $adminsidelogic ) ) {
        require ($adminsidelogic);
    }
}


// Load custom single product template

function wcpp_custom_wc_get_template_part( $template, $slug, $name ) {
    // if ( $slug === 'content' && $name === 'single-product' ) {
    if ( is_product() ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-product.php';
        if ( file_exists( $plugin_template ) ) {
            error_log( 'WooCommerce custom template loaded: ' . $plugin_template ); // Add this line for debugging
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'wc_get_template_part', 'wcpp_custom_wc_get_template_part', 10, 3 );

function show_default_custom_fields() {
    // Make sure to check the screen options to display custom fields
    add_filter('acf/settings/remove_wp_meta_box', '__return_false');
}
add_action('acf/init', 'show_default_custom_fields');

// Enable only one item in cart //

add_filter('woocommerce_add_to_cart_validation', 'restrict_to_single_product_type', 10, 3);
add_filter('woocommerce_add_cart_item_data', 'remove_existing_items_before_add', 10, 3);

function restrict_to_single_product_type($passed, $product_id, $quantity) {
    $cart = WC()->cart->get_cart();

    if (count($cart) > 0) {
        $cart_product_id = reset($cart)['product_id'];
        if ($cart_product_id != $product_id) {
            wc_add_notice(__('You can only add one product type to the cart at a time.'), 'error');
            return false;
        }
    }

    return $passed;
}

function remove_existing_items_before_add($cart_item_data, $product_id, $variation_id) {
    $cart = WC()->cart->get_cart();

    foreach ($cart as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] != $product_id) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }

    return $cart_item_data;
}

///////////////////////////////////////////////////////////////////////////

// function redirect_non_logged_in_users() {
//     if ( is_product() && !is_user_logged_in() ) {
//         wp_redirect( get_permalink( 32 )  );
//         exit;
//     }
// }
// add_action( 'template_redirect', 'redirect_non_logged_in_users' );

// function add_login_to_see_details_notice() {
//     if ( is_product() && !is_user_logged_in() ) {
//         wc_add_notice( __( 'Login to see product details.', 'your-text-domain' ), 'notice' );
//     }
// }
// add_action( 'woocommerce_before_single_product', 'add_login_to_see_details_notice' );


add_filter('woocommerce_checkout_cart_item_quantity', 'add_custom_message_to_variation', 10, 3);

function add_custom_message_to_variation($quantity, $cart_item, $cart_item_key) {
    if (isset($cart_item['variation_id']) && $cart_item['variation_id']) {

        $engrave_field_values = get_transient('engrave_field_values');
        $custom_message = '';
        if ($engrave_field_values) {
            $count_eng = 1;
            $custom_message .= '<br><br><b>Engraving Text:</b></br>';
            foreach ($engrave_field_values as $value) {
                $meta_key = 'engrave'.$count_eng;
                $custom_message .= '<span>'.$meta_key.': </span><span class="custom-variation-message">'.$value.'</span><br>';
                // update_post_meta($order_id, $meta_key, $value);
                $count_eng = $count_eng +1;
            }
            $custom_message .= '<br>';
        }
        return $quantity . $custom_message;
    }

    return $quantity;
}