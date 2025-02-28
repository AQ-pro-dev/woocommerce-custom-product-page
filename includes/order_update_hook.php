<?php 
add_action('woocommerce_checkout_order_processed', 'update_qr_code_with_order_id');

function update_qr_code_with_order_id($order_id) {
    $loged_in_user_id = get_current_user_id();
    $qrcode_posttype_data = get_transient("qrcode_posttype_data");
    //print_r($qrcode_posttype_data); exit;

    $engrave_field_values = get_transient('engrave_field_values');

    if ($engrave_field_values) {
        $count = 1;
        foreach ($engrave_field_values as $value) {
            $meta_key = 'engrave'.$count;
            //add_or_update_order_meta($order_id, $meta_key, $value);
            update_post_meta($order_id, $meta_key, $value);
            $count = $count +1;
        }
        delete_transient('engrave_field_values');
    }
    if(isset($qrcode_posttype_data) && $qrcode_posttype_data['user_id'] == $loged_in_user_id){
        // Prepare post data for the new QR code post
        $post_data = array(
            'post_title'    => 'QR Code for Product ' . $qrcode_posttype_data['product_id'] . ' And User ' . $qrcode_posttype_data['user_id'],
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => $qrcode_posttype_data['user_id'],
            'post_type'     => 'qrcode'
        );

        // Insert the new QR code post
        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            // Save additional meta data
            $meta_data = array(
                'product_id'     => $qrcode_posttype_data['product_id'],
                'user_id'        => $qrcode_posttype_data['user_id'],
                'qr_code_url'    => $qrcode_posttype_data['qr_code_url'],
                'timestamp'      => $qrcode_posttype_data['timestamp'],
                'qrcode_enabled' => 1,
                'order_id'       => $order_id
            );

            foreach ($meta_data as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
            //engrave_data
            update_field("qrcode_post_id", $post_id , $order_id);
            // Delete the transient as the post was successfully created
            delete_transient("qrcode_posttype_data");
        }

    }
}



// Display the Booking information on the order page in admin under General Information
add_action('woocommerce_admin_order_data_after_order_details', 'display_engrave_info');

function display_engrave_info($order) {    
    for ($i = 1; $i < 9; $i++) {
        if(get_post_meta($order->get_id(), 'engrave'.$i, true)) {
            $engrave =  get_post_meta($order->get_id(), 'engrave'.$i, true) ;
            if($i == 1){
                echo '<h3 style="display: inline-block;margin-top: 15px;">Engraving Information</h3>';
            }
            echo '<p><strong>Engrave'.$i.': </strong> ' . $engrave . '</p>';
        }
    }
    if(get_post_meta($order->get_id(), 'qrcode_post_id', true)) {
        $qrcode_id = get_post_meta($order->get_id(), 'qrcode_post_id', true);
        $admin_page_url = admin_url( 'post.php?post=' . $qrcode_id ) . '&action=edit';
        echo '<h3 style="display: inline-block;margin-top: 15px;"><a href="'.$admin_page_url.'">Click here</a> for QR Code information. </h3>';
    }
}

add_action( 'woocommerce_before_checkout_form', 'check_condition_before_checkout_form' );

function check_condition_before_checkout_form() {
    
    $loged_in_user_id = get_current_user_id();

    $qrcode_posttype_data = get_transient("qrcode_posttype_data");
    
/////////////checking for non qr code products //////////////////
    $cart = WC()->cart->get_cart();
    $categories_to_check = array('cadena-de-pulsera', 'cadenas-colgantes', 'tiras-de-pulseras');
    $tag_to_check = 'special-tag'; // Replace 'special-tag' with your desired tag slug

    $found_category = false;

    // Loop through the cart items
    foreach ($cart as $cart_item_key => $cart_item) {
        // Get the product ID from the cart item
        $product_id = $cart_item['product_id'];

        // Check if the product has the tag
        if (has_term($categories_to_check, 'product_cat', $product_id)) {
            $found_category = true;
            break; // If one product is in the specified categories, stop checking further
        }
    }
/////////////checking for non qr code products //////////////////
    if(!$found_category){
        if(!isset($qrcode_posttype_data) || $qrcode_posttype_data['user_id'] !== $loged_in_user_id) {
            wc_add_notice( 'Your QR Code is not generated please shop again.', 'error' );
            
            $referer = wp_get_referer();
            WC()->cart->empty_cart();
            wp_safe_redirect( $referer );
            exit;
        }
    }
    
}

//This hook will hide quantity field from cart
add_filter('woocommerce_cart_item_quantity', 'custom_remove_cart_quantity_input', 10, 3);

function custom_remove_cart_quantity_input($quantity, $cart_item_key, $cart_item) {
    return '<span class="quantity">1</span>';
}





