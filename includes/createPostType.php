<?php
// Register Custom Post Type for QR Codes
function create_qrcode_post_type() {
    $labels = array(
        'name' => 'QR Codes',
        'singular_name' => 'QR Code',
        'menu_name' => 'QR Codes',
        'name_admin_bar' => 'QR Code',
        'archives' => 'QR Code Archives',
        'attributes' => 'QR Code Attributes',
        'parent_item_colon' => 'Parent QR Code:',
        'all_items' => 'All QR Codes',
        'add_new_item' => 'Add New QR Code',
        'add_new' => 'Add New',
        'new_item' => 'New QR Code',
        'edit_item' => 'Edit QR Code',
        'update_item' => 'Update QR Code',
        'view_item' => 'View QR Code',
        'view_items' => 'View QR Codes',
        'search_items' => 'Search QR Code',
        'not_found' => 'Not found',
        'not_found_in_trash' => 'Not found in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into QR Code',
        'uploaded_to_this_item' => 'Uploaded to this QR Code',
        'items_list' => 'QR Codes list',
        'items_list_navigation' => 'QR Codes list navigation',
        'filter_items_list' => 'Filter QR Codes list',
    );
    $args = array(
        'label' => 'QR Code',
        'description' => 'Post Type for managing QR Codes',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'custom-fields'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type('qrcode', $args);
}
add_action('init', 'create_qrcode_post_type', 0);