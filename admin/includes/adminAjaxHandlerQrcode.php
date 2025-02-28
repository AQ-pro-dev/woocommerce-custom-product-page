<?php
add_action('wp_ajax_load_qr_codes', 'load_qr_codes');
function load_qr_codes() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    $args = array(
        'meta_query' => array(
            'relation' => 'AND',
        ),
    );

    if ($product_id) {
        $args['meta_query'][] = array(
            'key' => 'product_id',
            'value' => $product_id,
            'compare' => '='
        );
    }

    if ($user_id) {
        $args['meta_query'][] = array(
            'key' => 'user_id',
            'value' => $user_id,
            'compare' => '='
        );
    }

    if ($order_id) {
        $args['meta_query'][] = array(
            'key' => 'order_id',
            'value' => $order_id,
            'compare' => '='
        );
    }

    $qr_codes = get_posts($args); // Assume QR codes are stored as custom posts or similar

    if ($qr_codes) {
        $data = array();
        foreach ($qr_codes as $qr_code) {
            $data[] = array(
                'user_id' => get_post_meta($qr_code->ID, 'user_id', true),
                'product_id' => get_post_meta($qr_code->ID, 'product_id', true),
                'order_id' => get_post_meta($qr_code->ID, 'order_id', true),
                'qr_code_url' => get_post_meta($qr_code->ID, 'qr_code_url', true),
                'enabled' => get_post_meta($qr_code->ID, 'enabled', true)
            );
        }
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No QR codes found.');
    }
}

add_action('wp_ajax_toggle_qr_code', 'toggle_qr_code_admin');
function toggle_qr_code_admin() {
    if (isset($_POST['user_id']) && isset($_POST['product_id']) && isset($_POST['enabled'])) {
        $user_id = intval($_POST['user_id']);
        $product_id = intval($_POST['product_id']);
        $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);

        update_user_meta($user_id, "qr_code_enabled_{$product_id}", $enabled);

        wp_send_json_success('QR code state updated.');
    } else {
        wp_send_json_error('Invalid data.');
    }
}
