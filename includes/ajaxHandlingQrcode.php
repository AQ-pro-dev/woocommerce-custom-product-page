<?php 
add_action('wp_ajax_save_qr_code', 'save_qr_code');
add_action('wp_ajax_nopriv_save_qr_code', 'save_qr_code');

function save_qr_code() {
    $loged_in_user_id = get_current_user_id();
    
    if (isset($_POST['qr_code_image']) && isset($_POST['product_id']) && isset($_POST['qr_code_data'])) {
        if(!is_user_logged_in()){
            wp_send_json_success(['logged_in' => false]);
            exit;
        } else {
            $user_id = get_current_user_id();
        }

        $qr_code_svg = $_POST['qr_code_image']; // Expecting SVG string data here
        $qr_code_svg = stripslashes($qr_code_svg); 
        $product_id  = intval($_POST['product_id']);
        $upload_dir  = wp_upload_dir();
        $strtotimenow = strtotime('now');
        $filename    = "qr_code_{$strtotimenow}.svg"; // Save as SVG file
        $file_path   = $upload_dir['path'] . '/' . $filename;

        // Ensure the SVG starts with <svg> tag
        if (strpos($qr_code_svg, '<svg') !== false) {
            // Save the SVG string as a file
            if (file_put_contents($file_path, $qr_code_svg)) {
                // Get the URL for the saved SVG file
                $upload_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
                $qrcode_data = base64_decode($_POST['qr_code_data']);
                $qrcode_data = explode(',', $qrcode_data);
                
                if (isset($qrcode_data[1])) {
                    $timestamp = date('Y-m-d H:i:s', $qrcode_data[1]);
                } else {
                    $timestamp = date('Y-m-d H:i:s');
                }

                $data = array(
                    'product_id'     => $product_id,
                    'user_id'        => $user_id,
                    'timestamp'      => $timestamp,
                    'qrcode_enabled' => 1,
                    'qr_code_url'    => $upload_url
                );

                // Save the data in transient
                set_transient("qrcode_posttype_data", $data);
                
                // Return the URL to the SVG file
                wp_send_json_success(['url' => $upload_url]);
            } else {
                wp_send_json_error('Failed to save QR code SVG.');
            }
        } else {
            wp_send_json_error('Invalid SVG data.');
        }
    } else {
        wp_send_json_error('Invalid data.');
    }
}


// function save_qr_code() {
//     //echo '<pre>';print_r($_POST);exit;
//     if (isset($_POST['qr_code_image']) && isset($_POST['product_id'])) {
//         $user_id = get_current_user_id();
//         if (!$user_id) {
//             wp_send_json_error('User not logged in.');
//         }

//         $qr_code_image = $_POST['qr_code_image'];
//         $product_id = intval($_POST['product_id']);
//         $upload_dir = wp_upload_dir();
//         $img_data = str_replace('data:image/png;base64,', '', $qr_code_image);
//         $img_data = str_replace(' ', '+', $img_data);
//         $decoded_img = base64_decode($img_data);

//         $filename = "qr_code_{$user_id}_{$product_id}.png";
//         $file_path = $upload_dir['path'] . '/' . $filename;
//         //file_put_contents($file_path, $decoded_img);
//         if (file_put_contents($upload_path, $decoded_img)) {
//             $upload_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $upload_path);
//             $qrcode_data = base64_decode($_POST['qr_code_data']);
//             $qrcode_data = explode(',', $qrcode_data);
//             $upload_url = $upload_dir['url'] . '/' . $upload_url;
//             $data = array(
//                 'product_id'     => $product_id,
//                 'user_id'        => $user_id,
//                 'timestamp'      => date('Y-m-d H:i:s',$qrcode_data[1]),
//                 'qrcode_enabled' => 'Enabled',
//                 'qr_code_url'    => $upload_url
//             );
//             // Setting a value
//             set_transient( 'qrcode_posttype_data', $data );
//             echo 'hello<pre>';print_r(get_transient('qrcode_posttype_data'));exit;
            
//             // $file_url = $upload_dir['url'] . '/' . $filename;
//             // update_user_meta($user_id, "qr_code_url_{$product_id}", $file_url);

//             wp_send_json_success(['url' => $file_url]);

//         } else {
//             wp_send_json_error('Invalid data.');
//         }
//     }
// }

// add_action('woocommerce_add_cart_item_data', 'custom_product_add_to_cart', 10, 2);
// function custom_product_add_to_cart($cart_item_data, $product_id) {
//     if (isset($_POST['qr_code_url'])) {
//         $cart_item_data['qr_code_url'] = sanitize_text_field($_POST['qr_code_url']);
//     }
//     return $cart_item_data;
// }

// add_action('woocommerce_checkout_create_order_line_item', 'custom_order_line_item_meta', 10, 4);
// function custom_order_line_item_meta($item, $cart_item_key, $values, $order) {
//     if (isset($values['qr_code_url'])) {
//         $item->add_meta_data('QR Code URL', $values['qr_code_url'], true);
//     }
// }

// add_action('woocommerce_order_item_meta_end', 'display_qr_code_in_order', 10, 4);
// function display_qr_code_in_order($item_id, $item, $order, $plain_text) {
//     $qr_code_url = wc_get_order_item_meta($item_id, 'QR Code URL', true);
//     if ($qr_code_url) {
//         echo '<p><strong>QR Code:</strong><br><img src="' . esc_url($qr_code_url) . '" alt="QR Code" style="max-width:150px;"></p>';
//     }
// }

add_action('wp_ajax_nopriv_ajax_login', 'ajax_login');
function ajax_login() {
    // check_ajax_referer('ajax-login-nonce', 'security');
    $encoded_password = $_POST['password'];
    // Decode the Base64-encoded password
    $decoded_password = base64_decode($encoded_password);
    $creds = array(
        'user_login' => $_POST['email'],
        'user_password' => $decoded_password,
        'remember' => true,
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        echo wp_send_json(array('success' => false, 'data' => array('message' => $user->get_error_message()) ));
    } else {
        echo wp_send_json(array('success' => true, 'data' => array('message' => 'Ya has iniciado sesión.')));//You are loged in now.
    }
    wp_die();
}

add_action('wp_ajax_nopriv_ajax_signup', 'ajax_signup');
add_action('wp_ajax_ajax_signup', 'ajax_signup');

function ajax_signup() {
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $encoded_password = $_POST['password'];

    // Check if email is empty
    if (empty($email)) {
        echo wp_send_json(array('success' => false, 'data_msg' => array('message' => 'Correo electronico es requerido.')));//Email is required.
        wp_die();
    }

    // Decode the Base64-encoded password
    $decoded_password = base64_decode($encoded_password);

    // Check if password is empty
    if (empty($decoded_password)) {
        echo wp_send_json(array('success' => false, 'data_msg' => array('message' => 'Se requiere contraseña.')));//Password is required.
        wp_die();
    }

    // Hash the decoded password before storing
    // $hashed_password = wp_hash_password($decoded_password);
    // echo $hashed_password;exit;
    if (username_exists($email) || email_exists($email)) {
        echo wp_send_json(array('success' => false, 'data_msg' => array('message' => 'La usuario ya existe.')));//User already exists.
    } else {
        $user_id = wp_create_user($email, $decoded_password, $email);

        if (is_wp_error($user_id)) {
            echo wp_send_json(array('success' => false, 'data_msg' => array('message' => $user_id->get_error_message())));
        } else {
            wp_update_user(array('ID' => $user_id, 'display_name' => $name));

             // Log the user in and create session
            $creds = array(
                'user_login'    => $email,
                'user_password' => $decoded_password,
                'remember'      => true,
            );
            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                echo wp_send_json(array('success' => false, 'data_msg' => array('message' => $user->get_error_message())));
            } else {
                wp_set_auth_cookie($user->ID, true);
                wp_set_current_user($user->ID);
                check_login_status();
                echo wp_send_json(array('success' => true));
            }
            
            
            echo wp_send_json(array('success' => true));
        }
    }
    wp_die();
}


//password reset functionality 13-aug-2024
add_action('wp_ajax_ajax_password_reset', 'ajax_password_reset_function');
add_action('wp_ajax_nopriv_ajax_password_reset', 'ajax_password_reset_function');

function ajax_password_reset_function() {
    if (!isset($_POST['email'])) {
        wp_send_json_error(array('message' => 'No se proporcionó ninguna dirección de correo electrónico.'));
    }

    $user_email = sanitize_email($_POST['email']);
    
    if (!is_email($user_email) || !email_exists($user_email)) {
        wp_send_json_error(array('message' => 'No se encontró ningún usuario con esa dirección de correo electrónico.'));
    }

    $user = get_user_by('email', $user_email);
    $user_login = $user->user_login;

    // Generate password reset key
    $reset_key = get_password_reset_key($user);
    $reset_url = wp_login_url() . "?action=rp&key=$reset_key&login=" . rawurlencode($user_login);

    // Prepare email
    $to = $user_email;
    $subject = 'Password Reset Request';
    $message = 'Click the following link to reset your password: ' . esc_url($reset_url);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Send email
    if (wp_mail($to, $subject, $message, $headers)) {
        wp_send_json_success(array('message' => 'Se ha enviado un enlace para restablecer la contraseña a tu correo electrónico.'));
    } else {
        wp_send_json_error(array('message' => 'No se pudo enviar el correo electrónico.'));
    }
    wp_die();
}

function check_login_status(){
    if(is_user_logged_in()){
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_email .= ',' . strtotime('now');
        wp_send_json_success(['logged_in' => true,'paz_ue' => base64_encode($user_email),'userID' => $current_user->ID ]);
        // wp_send_json_success(['logged_in' => true]);
    } else {
        //echo '<pre>';print_r('Else');exit;
        // wp_send_json_success(['logged_in' => false]);
        wp_send_json_success(['logged_in' => false, 'paz_ue' => false]);
    }
    wp_die();
}
add_action('wp_ajax_check_login_status', 'check_login_status');
add_action('wp_ajax_nopriv_check_login_status', 'check_login_status');


// update engrave acf_field
add_action('wp_ajax_update_acf_field', 'update_acf_field');
add_action('wp_ajax_nopriv_update_acf_field', 'update_acf_field'); // If needed for non-logged-in users

function update_acf_field() {
    // Check if necessary data is present
    if (!isset($_POST['product_id'], $_POST['field_name'], $_POST['new_value'])) {
        wp_send_json_error('Missing parameters');
        wp_die();
    }

    // Get the data from the AJAX request
    $product_id = intval($_POST['product_id']);
    $field_name = sanitize_text_field($_POST['field_name']);
    $new_value = sanitize_text_field($_POST['new_value']); // Sanitize input

    // Update the ACF field for the product
    if (update_field($field_name, $new_value, $product_id)) {
        wp_send_json_success('Field updated successfully');
    } else {
        wp_send_json_error('Failed to update field');
    }

    wp_die();
}
