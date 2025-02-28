<?php
// Function to check if a URL is a valid image URL
function is_image_url($url) {
    // Check if it's a valid URL and has a common image extension
    return filter_var($url, FILTER_VALIDATE_URL) && preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $url);
}
add_action('wp_ajax_process_engrave_fields', 'process_engrave_fields');
add_action('wp_ajax_nopriv_process_engrave_fields', 'process_engrave_fields');

function process_engrave_fields() {

    $formdata = $_POST['formdata'];
    parse_str($formdata, $formdataArray);
    
    // Check if the last element is empty and remove it
    if(isset($_POST['fieldValues'])){
    
        if (empty(end($_POST['fieldValues']))) {
            array_pop($_POST['fieldValues']);
        }
        // Check each element to see if it's an image URL and remove the last empty element
        $engrave_field_values = array();
        foreach ($_POST['fieldValues'] as $key => $value) {
            if (!is_image_url($value)) {
                array_push($engrave_field_values, $value);
            }
        }

        if($_POST['attribute_engrave'] == 'on'){
            // set_transient('engrave_field_values', $_POST['fieldValues'] );
            set_transient('engrave_field_values', $engrave_field_values );
        }
    }
    $product_id = $formdataArray['product_id'];
    $variation_id = $formdataArray['variation_id'];
    $quantity = $formdataArray['quantity'];
    //echo 'product_id = '.$product_id.' variation_id = '.$variation_id.' quantity'.$quantity;exit;
    add_variable_product_to_cart($product_id, $variation_id, $quantity);   

    $response = array(
        'success' => true,
        'redirecturl' => wc_get_checkout_url()
    );

    wp_send_json_success($response);
    
    exit;
}

    function add_variable_product_to_cart($product_id, $variation_id, $quantity) { 
        // Get the product
        $product = wc_get_product($product_id);
    
        // Check if the product exists and is a variable product
        if ($product && $product->is_type('variable')) {
            // Add to cart
            WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        }
    }

add_action('wp_ajax_swap_engraving_fields', 'swap_engraving_fields');
add_action('wp_ajax_nopriv_swap_engraving_fields', 'swap_engraving_fields');

function swap_engraving_fields() {
    // print_r($_POST);exit;
    if (isset($_POST['product_id']) && isset($_POST['engraving']) && isset($_POST['qrCodeOption'])) {
        $product_id = intval($_POST['product_id']);

        $product = wc_get_product($product_id);

        if ($product && $product->is_type('variable')) {
            $fields_content = '';
            if($_POST['qrCodeOption'] == 'front-side') {
                // if qr code print on Front Side the engrave should be on Back, the field of front we will use
                $engrave_back = get_field('engrave_back', $product_id);
                if ($engrave_back && is_array($engrave_back)) {
                    $counter = 1;
                    $fields_content .= '<div class="engrave-fields">';
                    foreach ($engrave_back as $key => $value) {
                        $maxlength = isset($value['subengrave_back']) ? $value['subengrave_back'] : '';
                        $fields_content .= '<div class="input-group"><input type="text" class="input-field" id="engrave'.$counter.'" name="engrave[]" minlength="0" maxlength="'.$maxlength.'" oninput="updateCanvas()"><span class="character-count">'.$maxlength.'</span></div>';
                        $counter++;
                    }
                    $fields_content .= '</div>';
                }

                $live_preview_back = get_field('live_preview_back', $product_id);
                $back_preview_text_padding_top = (get_field('back_preview_text_padding_top', $product_id)??'1');
                $back_preview_text_padding_bottom = (get_field('back_preview_text_padding_bottom', $product_id)??'1');
                $back_preview_text_padding_left = (get_field('back_preview_text_padding_left', $product_id)??'1');
                $back_preview_text_padding_right = (get_field('back_preview_text_padding_right', $product_id)??'1');
                $back_preview_text_font_size = (get_field('back_preview_text_font_size', $product_id)??'10');
                $back_preview_text_font_style = (get_field('back_preview_text_font_style', $product_id)??'Arial');
                $back_preview_text_line_spacing = (get_field('back_preview_text_line_spacing', $product_id)??'15');
                $back_preview_text_color = (get_field('back_preview_text_color', $product_id)??'black');
                $back_preview_text_orientation = (get_field('back_preview_text_orientation', $product_id)??'horizontal');
                // print_r($live_preview_front);exit;
                if ($live_preview_back) {
                    $fields_content .= "<script>jQuery('#engraved_img_src').val('".$live_preview_back."');";
                    $fields_content .= "jQuery('#padding-top').val('".$back_preview_text_padding_top."');";
                    $fields_content .= "jQuery('#padding-bottom').val('".$back_preview_text_padding_bottom."');";
                    $fields_content .= "jQuery('#padding-left').val('".$back_preview_text_padding_left."');";
                    $fields_content .= "jQuery('#padding-right').val('".$back_preview_text_padding_right."');";
                    $fields_content .= "jQuery('#font-size').val('".$back_preview_text_font_size."');";
                    $fields_content .= "jQuery('#font-style').val('".$back_preview_text_font_style."');";
                    $fields_content .= "jQuery('#line-spacing').val('".$back_preview_text_line_spacing."');";
                    $fields_content .= "jQuery('#text-color').val('".$back_preview_text_color."');";
                    $fields_content .= 'jQuery(\'input[name="orientation"]\').val("' . $back_preview_text_orientation . '");loadImageAndUpdateCanvas();</script>';
                }
            } elseif($_POST['qrCodeOption'] == 'back-side'){
                // if qr code print on Back Side the engrave should be on front, the field of front we will use

                $engrave = get_field('engrave', $product_id);
                // print_r($engrave);exit;
                if ($engrave && is_array($engrave)) {
                    $counter = 1;
                    $fields_content .= '<div class="engrave-fields">';
                    foreach ($engrave as $key => $value) {
                        $maxlength = isset($value['subengrave']) ? $value['subengrave'] : '';
                        $fields_content .= '<div class="input-group"><input type="text" class="input-field" id="engrave'.$counter.'" name="engrave[]" oninput="updateCanvas()" minlength="0" maxlength="'.$maxlength.'"><span class="character-count">'.$maxlength.'</span></div>';
                        $counter++;
                    }
                    $fields_content .= '</div>';
                }
                $live_preview_front = get_field('live_preview_front', $product_id);
                $preview_text_padding_top = (get_field('preview_text_padding_top', $product_id)??'1');
                $preview_text_padding_bottom = (get_field('preview_text_padding_bottom', $product_id)??'1');
                $preview_text_padding_left = (get_field('preview_text_padding_left', $product_id)??'1');
                $preview_text_padding_right = (get_field('preview_text_padding_right', $product_id)??'1');
                $preview_text_font_size = (get_field('preview_text_font_size', $product_id)??'10');
                $preview_text_font_style = (get_field('preview_text_font_style', $product_id)??'Arial');
                $preview_text_line_spacing = (get_field('preview_text_line_spacing', $product_id)??'15');
                $preview_text_color = (get_field('preview_text_color', $product_id)??'black');
                $preview_text_orientation = (get_field('preview_text_orientation', $product_id)??'horizontal');
                // print_r($live_preview_front);exit;
                if ($live_preview_front) {
                    $fields_content .= "<script>jQuery('#engraved_img_src').val('".$live_preview_front."');";
                    $fields_content .= "jQuery('#padding-top').val('".$preview_text_padding_top."');";
                    $fields_content .= "jQuery('#padding-bottom').val('".$preview_text_padding_bottom."');";
                    $fields_content .= "jQuery('#padding-left').val('".$preview_text_padding_left."');";
                    $fields_content .= "jQuery('#padding-right').val('".$preview_text_padding_right."');";
                    $fields_content .= "jQuery('#font-size').val('".$preview_text_font_size."');";
                    $fields_content .= "jQuery('#font-style').val('".$preview_text_font_style."');";
                    $fields_content .= "jQuery('#line-spacing').val('".$preview_text_line_spacing."');";
                    $fields_content .= "jQuery('#text-color').val('".$preview_text_color."');";
                    $fields_content .= 'jQuery(\'input[name="orientation"]\').val("' . $preview_text_orientation . '");loadImageAndUpdateCanvas();</script>';
               }
            }

            if ($fields_content != '') {
                wp_send_json_success(['fields_content' => $fields_content]);
            } else {
                wp_send_json_error('No fields content found.');
            }
        } else {
            wp_send_json_error('Invalid product.');
        }
    } else {
        wp_send_json_error('Invalid data.');
    }
}