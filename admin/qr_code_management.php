<?php
add_filter('manage_qrcode_posts_columns', 'custom_qrcode_columns');

function custom_qrcode_columns($columns) {
    $columns['product_id'] = 'Product ID';
    $columns['user_id'] = 'User Name';
    $columns['qrcode_url'] = 'QR Code URL';
    $columns['timestamp'] = 'Timestamp';
    $columns['order_id'] = 'Order ID';
    $columns['qrcode_status'] = 'Status';
    return $columns;
}

add_action('manage_qrcode_posts_custom_column', 'custom_qrcode_column_content', 10, 2);

function custom_qrcode_column_content($column, $post_id) {
    switch ($column) {
        case 'product_id':
            echo get_post_meta($post_id, 'product_id', true);
            break;
        case 'user_id':
            $user_id =  get_post_meta($post_id, 'user_id', true);
            $user_info = get_userdata($user_id);

            // Check if user data exists
            if ($user_info) {
                // Get the first name and last name
                $first_name = $user_info->first_name;
                $last_name = $user_info->last_name;

                // Display the full name
                echo $first_name . ' ' . $last_name;
            } 
            break;
        case 'qrcode_url':
            echo '<a href="' . get_post_meta($post_id, 'qr_code_url', true) . '" target="_blank">View QR Code</a>';
            break;
        case 'timestamp':
            echo get_post_meta($post_id, 'timestamp', true);
            break;
        case 'order_id':
            echo get_post_meta($post_id, 'order_id', true);
            break;
        case 'qrcode_status':
            $enabled = get_post_meta($post_id, 'qrcode_enabled', true);
            echo $enabled ? 'Enabled' : 'Disabled';
            break;
    }
}

// Add custom filters for QR code status
add_action('restrict_manage_posts', 'custom_qrcode_filters');

function custom_qrcode_filters() {
    global $typenow;

    if ($typenow == 'qrcode') {
        $selected_product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
        $selected_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $selected_status = isset($_GET['status']) ? $_GET['status'] : '';

        $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
        $users = get_users();

        echo '<select name="product_id">';
        echo '<option value="">Filter by Product ID</option>';
        foreach ($products as $product) {
            echo '<option value="' . $product->ID . '"' . selected($selected_product_id, $product->ID, false) . '>' . $product->ID . '</option>';
        }
        echo '</select>';

        echo '<select name="user_id">';
        echo '<option value="">Filter by User ID</option>';
        foreach ($users as $user) {
            echo '<option value="' . $user->ID . '"' . selected($selected_user_id, $user->ID, false) . '>' . $user->ID . '</option>';
        }
        echo '</select>';

        echo '<select name="status">';
        echo '<option value="">Filter by Status</option>';
        echo '<option value="enabled"' . selected($selected_status, 'enabled', false) . '>Enabled</option>';
        echo '<option value="disabled"' . selected($selected_status, 'disabled', false) . '>Disabled</option>';
        echo '</select>';
    }
}

add_filter('parse_query', 'custom_qrcode_filter_query');

function custom_qrcode_filter_query($query) {
    global $pagenow;
    $post_type = 'qrcode';

    if (is_admin() && $pagenow == 'edit.php' && $query->query['post_type'] == $post_type) {
        $meta_query = array();

        if (!empty($_GET['product_id'])) {
            $meta_query[] = array(
                'key'     => 'product_id',
                'value'   => $_GET['product_id'],
                'compare' => '=',
            );
        }

        if (!empty($_GET['user_id'])) {
            $meta_query[] = array(
                'key'     => 'user_id',
                'value'   => $_GET['user_id'],
                'compare' => '=',
            );
        }

        if (!empty($_GET['status'])) {
            $meta_query[] = array(
                'key'     => 'qrcode_enabled',
                'value'   => $_GET['status'] === 'enabled' ? 1 : 0,
                'compare' => '=',
            );
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
