<?php
add_action('wp_ajax_get_variation_id', 'get_variation_id');
add_action('wp_ajax_nopriv_get_variation_id', 'get_variation_id');

function get_variation_id() {
    if (isset($_POST['product_id']) && isset($_POST['attributes'])) {
        $product_id = intval($_POST['product_id']);
        $attributes = $_POST['attributes'];

        $product = wc_get_product($product_id);

        if ($product && $product->is_type('variable')) {
            
            $variation_id = find_matching_product_variation($product, $attributes);            

            if ($variation_id) {
                wp_send_json_success(['variation_id' => $variation_id]);
            } else {
                wp_send_json_error('No matching variation found.');
            }
        } else {
            wp_send_json_error('Invalid product.');
        }
    } else {
        wp_send_json_error('Invalid data.');
    }
}
function find_matching_product_variation($product, $attributes) {
    
    foreach ($product->get_available_variations() as $variation) {
        // echo '<pre>';print_r($variation);exit;
        $variation_attributes = $variation['attributes'];
        $match = true;

        foreach ($attributes as $key => $value) {
            if (!array_key_exists($key, $variation_attributes)) {
                $match = false;
                break;
            }

            // Handle empty string or 'no' for attributes like engraving
            if ($key === 'attribute_pa_engrave' && $value === 'no' && $variation_attributes[$key] === '') {
                continue;
            }

            if ($variation_attributes[$key] !== $value) {
                $match = false;
                break;
            }
        }

        if ($match) {
            return $variation['variation_id'];
        }
    }

    return false;
}
////////////////////////////////////////////////


add_action('wp_ajax_get_variation_images', 'get_variation_images');
add_action('wp_ajax_nopriv_get_variation_images', 'get_variation_images');

function get_variation_images() {
    if (!isset($_POST['variation_id'])) {
        wp_send_json_error('Invalid variation ID');
        return;
    }

    $variation_id = intval($_POST['variation_id']);
    $variation = wc_get_product($variation_id);

    if (!$variation) {
        wp_send_json_error('Variation not found');
        return;
    }

    $main_image_id = $variation->get_image_id();
    $gallery_image_ids = $variation->get_gallery_image_ids();

    $main_image_url = $main_image_id ? wp_get_attachment_url($main_image_id) : '';
    $gallery_image_urls = [];

    foreach ($gallery_image_ids as $image_id) {
        $gallery_image_urls[] = wp_get_attachment_url($image_id);
    }

    wp_send_json_success([
        'main_image' => $main_image_url,
        'gallery_images' => $gallery_image_urls,
    ]);
}


