<?php 

// Enqueue custom styles and scripts
function wcpp_custom_styles() {
    if (is_product()) {
        wp_enqueue_style('wcpp-custom-styles', plugin_dir_url(__FILE__) . '../assets/css/custom-styles.css', array(), (rand(1,5)).'.'.(rand(1,9)).'.'.(rand(1,9)) );
        wp_enqueue_style('wcpp-sign-inup-style', plugin_dir_url(__FILE__) . '../assets/css/sign-inup-scripts.css');

        wp_enqueue_script('jquery-js', 'https://code.jquery.com/jquery-3.6.4.min.js', array('jquery'), null, true);
        wp_enqueue_script('swiper-bundle', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array('jquery'), null, true);
        wp_enqueue_script('wcpp-custom-scripts', plugin_dir_url(__FILE__) . '../assets/js/custom-scripts.js', array('jquery'), (rand(1,5)).'.'.(rand(1,9)).'.'.(rand(1,9)), true);
        // wp_enqueue_script('wcpp-sign-inup-scripts', plugin_dir_url(__FILE__) . '../assets/js/sign-inup-scripts.js', array('jquery'), '1.0.0', true);

        wp_localize_script('wcpp-custom-scripts', 'ajax_login_signup_data', array(
            'ajax_sign_inup_url' => admin_url('admin-ajax.php'),
            'is_logged_in' => is_user_logged_in() ? '1' : '0',
            // ,'security' => wp_create_nonce('ajax-login-nonce')
            // Localize script to pass user data
        ));

        // wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array(), null, true);
        
        // Enqueue qrcode-generator library cdn
        wp_enqueue_script('qrcode-generator', 'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js', array(), null, true);
        wp_localize_script('wcpp-custom-scripts', 'ajax_object_for_Qrcode', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));

        // Localize script for AJAX requests
        wp_localize_script('qr-code-styling-custom-scripts', 'ajax_object_for_Qrcode', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));


        wp_localize_script('wcpp-custom-scripts', 'ajax_object_for_listing', array(
            'ajax_for_listing' => admin_url('admin-ajax.php')
        ));
    }

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_email .= ',' . strtotime('now');
    } else {
        $user_email = '';
    }

    // Pass user email to the script
    wp_localize_script('wcpp-custom-scripts', 'user_data', array(
        'email' => $user_email
    ));
}
       
add_action('wp_enqueue_scripts', 'wcpp_custom_styles');


// function enqueue_qrcode_script() {
//     //if (has_shortcode(get_post()->post_content, 'custom_step_form')) {
//     if ( is_product() ) {
//    wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array(), null, true);
//     }
// }
// add_action('wp_enqueue_scripts', 'enqueue_qrcode_script');

function enqueue_html2canvas() {
    wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_html2canvas');