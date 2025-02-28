<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); 
// Ensure that the global $product is set
global $product;
if ( empty( $product ) || ! $product instanceof WC_Product ) {
    $product = wc_get_product( get_the_ID() );
}

//function custom_step_form_shortcode() {
// Get the product price label
$product_price_label = __( 'Price', 'woocommerce' ); // Translate "Price" label using WooCommerce text domain

// Get the product price
$product_price = $product->get_price_html();
$user_id = get_current_user_id();
$product_id = $product->get_id();
$user_email = wp_get_current_user()->user_email;
$attributes = $product->get_attributes();
//echo '<pre>'; print_r($attributes);exit;
// Check if $attributes is an array
if (is_array($attributes)) {
    // Check if 'engrave' key exists
    if (array_key_exists('pa_engrave', $attributes)) {
        // Proceed with your logic here
        $engrave_value = true;
    } else {
        // Handle case where 'engrave' does not exist
        $engrave_value = false;
    }
} else {
    // Handle case where $attributes is not an array
    $engrave_value = false;
}
//echo '<pre>'; print_r($attributes['pa_engrave']);exit;
?>
<section class="product-detail-wrapper">
    <div class="custom-container">
        <div class="inner">
            <div class="col">
                <div class="product-image-slidshow">
                    <div class="swiper product-image-slider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="image">
                                    <?php
                                        if ( has_post_thumbnail() ) {
                                            $attachment_id = get_post_thumbnail_id();
                                            echo wp_get_attachment_image( $attachment_id, 'full' );
                                        }
                                    ?>
                                </div>
                            </div>
                                <?php
                                $attachment_ids = $product->get_gallery_image_ids();
                                if ( $attachment_ids ) {
                                    foreach ( $attachment_ids as $attachment_id ) {
                                        echo '<div class="swiper-slide">
                                        <div class="image">';
                                        echo wp_get_attachment_image( $attachment_id, 'full' );
                                        echo '</div>
                                        </div>';
                                    }
                                }?> 
                        </div>
                    </div>
                    <div class="product-thumb-slider-wrapper">
                        <div class="swiper-button-prev pi-nav-btn"></div>
                        <div thumbsSlider="" class="swiper product-thumb-slider">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="image">
                                        <?php
                                            if ( has_post_thumbnail() ) {
                                                $attachment_id = get_post_thumbnail_id();
                                                echo wp_get_attachment_image( $attachment_id, 'thumbnail' );
                                            }
                                        ?>
                                    </div>
                                </div>
                                    <?php
                                    $attachment_ids = $product->get_gallery_image_ids();
                                    if ( $attachment_ids ) {
                                        foreach ( $attachment_ids as $attachment_id ) {
                                            echo '<div class="swiper-slide">
                                            <div class="image">';
                                            echo wp_get_attachment_image( $attachment_id, 'thumbnail' );
                                            echo '</div>
                                            </div>';
                                        }
                                    }?> 
                                </div>                           
                        </div>
                        <div class="swiper-button-next pi-nav-btn"></div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="product-description">
                    <h2 class="product-title"><?php the_title();?></h2>
                    <div class="product-price">
                        <span class="pd-label"><?php echo esc_html( $product_price_label );?></span>
                        <span class="price-text"><?php echo wp_kses_post( $product_price );?></span>
                    </div>
                    <div class="product-customization-steps">
                        <ul class="steps-label-wrapper">
                            <li data-step="1">Paso-1 Productos</li>
                            <li data-step="2">Paso-2 QR MiRPRE®</li>
                            <?php
                                if ($engrave_value) {
                            ?>
                            <li data-step="3">Paso-3 Grabado</li>
                            <?php }?>
                        </ul>
                        <div id="step-loader">
                            <img src="<?php echo wp_upload_dir()['baseurl'];?>/2024/11/paz-loeader.gif" alt="">
                        </div>
                        <form id="cart" action="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart" method="post" enctype='multipart/form-data'>
                            <div class="steps-content-wrapper">
                                <div class="step" data-step="1">
                                    <div class="short-description">
                                        <p><?php echo $product->get_short_description(); ?></p>
                                    </div>
                                    <?php //echo "<pre>"; print_r($attributes);
                                        if(isset($attributes['pa_length'])){
                                            $countLength = count($attributes['pa_length']->get_options());
                                    ?>
                                    <div class="input-group length-field <?php echo ($countLength == 1) ? 'hide' : '';?>">
                                        <label><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_length');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'Length'; 
                                                echo esc_html($attribute_title); 
                                                ?>: 
                                        </label>
                                        <select id="product_length" name="attribute_length" data-attribute_name="attribute_length">
                                            <?php foreach ($attributes['pa_length']->get_options() as $length): ?>
                                                <?php 
                                                $term = get_term_by('id', $length, 'pa_length'); 
                                                if ($term) : ?>
                                                    <option <?php echo ($countLength == 1) ? 'selected="selected"' : '';?> value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php } ?>
                                    <?php //echo "<pre>"; print_r($attributes);
                                        if(isset($attributes['pa_medical-alert-devices'])){
                                            $countMAD = count($attributes['pa_medical-alert-devices']->get_options());
                                    ?>
                                    <div class="input-group medical-alert-devices-field <?php echo ($countMAD == 1) ? 'hide' : '';?>">
                                        <label><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_medical-alert-devices');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'Medical alert devices'; 
                                                echo esc_html($attribute_title); 
                                                ?>: 
                                        </label>
                                        <!-- <label><?php //echo $attributes['pa_medical-alert-devices']['name']; ?>:</label> -->
                                        <select id="att_medical_alert_devices" name="attribute_medical-alert-devices" data-attribute_name="attribute_medical-alert-devices">
                                            <?php foreach ($attributes['pa_medical-alert-devices']->get_options() as $medical_alert_devices): ?>
                                                <?php 
                                                $term_mad = get_term_by('id', $medical_alert_devices, 'pa_medical-alert-devices'); 
                                                if ($term_mad) : ?>
                                                    <option <?php echo ($countMAD == 1) ? 'selected="selected"' : '';?> value="<?php echo esc_attr($term_mad->slug); ?>"><?php echo esc_html($term_mad->name); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php } ?>

                                    <?php //echo "<pre>"; print_r($attributes);
                                        if(isset($attributes['pa_medical-identifications'])){
                                            $countMI = count($attributes['pa_medical-identifications']->get_options());
                                            $unisex = false;
                                            foreach ($attributes['pa_medical-identifications']->get_options() as $medical_identifications):                                                
                                                $term_mi = get_term_by('id', $medical_identifications, 'pa_medical-identifications');                                                 
                                                if($term_mi->slug == 'unisex'){
                                                    $unisex = true;
                                                }
                                            endforeach;
                                    ?>
                                    <div class="input-group medical-identifications-field <?php echo ($countMI == 1 || $unisex == true) ? 'hide' : '';?>">
                                        <label><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_medical-identifications');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'Medical identifications'; 
                                                echo esc_html($attribute_title); 
                                                ?>: 
                                        </label>
                                        <!-- <label><?php //echo $attributes['pa_medical-identifications']['name']; ?>:</label> -->
                                        <select id="att_medical_identifications" name="attribute_medical-identifications" data-attribute_name="attribute_medical-identifications">
                                            <?php foreach ($attributes['pa_medical-identifications']->get_options() as $medical_identifications): 
                                                
                                                $term_mi = get_term_by('id', $medical_identifications, 'pa_medical-identifications'); 
                                                if ($term_mi) : ?>
                                                    <option <?php echo ($countMI == 1) ? 'selected="selected"' : '';?> value="<?php echo esc_attr($term_mi->slug); ?>"><?php echo esc_html($term_mi->name); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php } ?>

                                    <?php //echo "<pre>"; print_r($attributes);
                                        if(isset($attributes['pa_metal-type'])){
                                            $countMetalType = count($attributes['pa_metal-type']->get_options());
                                    ?>
                                    <div class="input-group metal-type-field  <?php echo ($countMetalType == 1) ? 'hide' : '';?>">
                                        <label><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_metal-type');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'Metal type'; 
                                                echo esc_html($attribute_title); 
                                                ?>: 
                                        </label>
                                        <!-- <label><?php //echo $attributes['pa_metal-type']['name']; ?>:</label> -->
                                        <select id="att_metal_type" name="attribute_metal-type" data-attribute_name="attribute_metal-type">
                                            <?php foreach ($attributes['pa_metal-type']->get_options() as $metal_type): ?>
                                                <?php 
                                                $term_mt = get_term_by('id', $metal_type, 'pa_metal-type'); 
                                                if ($term_mt) : ?>
                                                    <option <?php echo ($countMetalType == 1) ? 'selected="selected"' : '';?> value="<?php echo esc_attr($term_mt->slug); ?>"><?php echo esc_html($term_mt->name); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php } ?>

                                    <?php
                                    if(isset($attributes['pa_color'])){
                                        $countColor = count($attributes['pa_color']->get_options());
                                    // Fetch custom color mappings from the plugin settings
                                    $color_mappings = my_woocommerce_plugin_get_color_mappings();
                                    
                                    ?>
                                    <div class="input-group color-selector field-wrapped">
                                        <label><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_color');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'Color'; 
                                                echo esc_html($attribute_title); 
                                                ?>:&nbsp;&nbsp;
                                        </label>
                                        <!-- <label class="pd-label"><?php //echo $attributes['pa_color']['name']; ?>:</label> -->
                                        <div class="color-radio-wrapper">
                                            <?php  foreach ($attributes['pa_color']->get_options() as $key => $color):
                                                $key = $key + 1;
                                                // Get the custom color or fallback to the color name
                                               
                                                $term_color = get_term_by('id', $color, 'pa_color');
                                                //  print_r($term_color) ;
                                                if($term_color){
                                                    // echo $term_color->name;
                                                    // print_r($color_mappings[$term_color->name]);
                                                $color_code = isset($color_mappings[$term_color->name]) ?$color_mappings[$term_color->name]: $term_color->name;
                                                // echo $color_code;
                                            ?>
                                                <div class="color-radio">
                                                    <input <?php echo ($countColor == 1) ? 'checked="checked"' : '';?>  id="color_<?php echo $key; ?>" name="attribute_color" data-attribute_name="attribute_color" type="radio" value="<?php echo $term_color->slug; ?>">
                                                    <label for="color_<?php echo $key; ?>" style="background-color: <?php echo $color_code; ?>"></label>
                                                </div>
                                            <?php } 
                                                  endforeach; ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="product-quantity field-wrapped" style="display: none;">
                                        <label class="pd-label">Cantidad:</label>
                                        <div class="quantity-counter">
                                            <button type="button" class="counter-btn qty-btn-minus">-</button>
                                            <input class="input-field input-qty" id="quantity" type="number" name="quantity" min="1" max="1" value="1">
                                            <button type="button" class="counter-btn qty-btn-plus">+</button>
                                        </div>
                                    </div>                                               
                                    <div class="cta-btn-wrapper">
                                        <button type="button" class="cta-btn cta-next">
                                            <span class="cta-text">Próxima</span>
                                        </button>
                                    </div>
                                </div>                                          
                                <div class="step" data-step="2">
                                    <div class="fieldset">
                                        <p class="product-note"><?php the_field('personal_medical_emergency');?></p>
                                    </div>
                                    <?php if(isset($attributes['pa_qr-code-preferences'])){ ?>
                                    <div class="fieldset qr-code-preference">
                                        <strong class="fieldset-title"><?php // Get the attribute taxonomy object
                                                $taxonomy = get_taxonomy('pa_qr-code-preferences');
                                                // Get the attribute title (label)
                                                $attribute_title = $taxonomy ? $taxonomy->labels->singular_name : 'QR Code Preferences'; 
                                                echo esc_html($attribute_title); 
                                                ?>: 
                                        </strong>
                                        <!-- <strong class="fieldset-title"><?php //echo $attributes['pa_qr-code-preferences']['name'];?>:</strong> -->
                                        <?php foreach ($attributes['pa_qr-code-preferences']->get_options() as  $key => $qr_code):
                                            $key = $key + 1;     
                                            $term_qrc = get_term_by('id', $qr_code, 'pa_qr-code-preferences'); 
                                            if ($term_qrc) : ?>
                                        <div class="input-radio">
                                            <input id="qr_<?php echo $key;?>" class="generate-qrcode" type="radio" name="attribute_qr-code-preferences" data-attribute_name="attribute_qr-code-preferences" value="<?php echo $term_qrc->slug;?>">
                                            <label for="qr_<?php echo $key;?>"><?php echo $term_qrc->name;?></label>
                                        </div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>                                        
                                    </div>
                                    <?php } ?>
                                    <p><?php the_field('qr_code_on_front_engraving_back');?></p>
                                    <div class="fieldset">
                                        <strong><?php the_field('great_for_heading');?></strong>
                                        <ul>
                                        <?php 
                                            $great_for_bullets = get_field('great_for_bullets');

                                            if ($great_for_bullets && is_array($great_for_bullets)) {

                                                foreach ($great_for_bullets as $key => $value) {
                                                    $bullets = isset($value['bullets']) ? $value['bullets'] : '';

                                                    echo <<<HTML
                                                            <li>{$bullets}</li>
                                                    HTML;
                                                }
                                            }
                                        ?>
                                        </ul>
                                    </div>
                                    <?php
                                        if (get_field('qr_code_sample_text', 'option')) {
                                           echo '<div style="color:red;font-size:15px;font-weight: 800;padding-bottom: 10px;"><span>'.get_field('qr_code_sample_text', 'option').'</span></div>';
                                        }
                                    ?>
                                        <div id="qrcode-main">
                                            <div id="qrcode-container" style="position: relative; width: 225px; height: 267px; background-color: black; color: white; text-align: center; padding: 10px; border-radius: 10px; border: 3px solid black;">
                                                <div id="qrcode-text" style="padding: 10px; font-size: 16px;"><?php echo (get_field('qr_code_heading_text', 'option')??'MiRPRE®');?></div>
                                                <div id="qrcode" style="position: relative; width: 203px; height: 203px; background-color: white; margin: 0 auto;padding:7px;border-radius:10px;"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="qr_code_url" name="qr_code_url">
                                        <input type="hidden" id="qr_code_scanning_link" name="qr_code_scanning_link" value="<?php echo (get_field('qr_code_scanning_link', 'option')['url']??'');?>">
                                        <input type="hidden" id="qr_code_center_logo" name="qr_code_center_logo" value="<?php echo (get_field('qr_code_center_logo', 'option')??'');?>">
                                        <input type="hidden" id="dummy_qr_code" name="dummy_qr_code" value="<?php echo (get_field('dummy_qr_code', 'option')??'');?>">

                                    <div class="fieldset">
                                        <strong><?php the_field('also_includes_heading');?></strong>
                                        <p><?php the_field('also_includes_text');?></p>
                                        <ul>
                                        <?php 
                                            $also_includes_bullets = get_field('also_includes_bullets');

                                            if ($also_includes_bullets && is_array($also_includes_bullets)) {

                                                foreach ($also_includes_bullets as $key => $value) {
                                                    $include_bullets = isset($value['include_bullets']) ? $value['include_bullets'] : '';

                                                    echo <<<HTML
                                                            <li>{$include_bullets}</li>
                                                    HTML;
                                                }
                                            }
                                        ?>
                                        </ul>
                                    </div>
                                    <div class="cta-btn-wrapper">
                                        <button type="button" class="cta-btn cta-bordered cta-back">
                                            <span class="cta-text">Atrás</span>
                                        </button>
                                        <?php
                                            if ($engrave_value) {
                                        ?>
                                        <button type="button" class="cta-btn cta-next" id="second_step_next">
                                            <span class="cta-text">Próxima</span>
                                        </button>
                                    <?php 
                                        }else{
                                    ?>
                                        <button type="button" class="cta-btn cta-next" id="checkout">
                                            <span class="cta-text">verificar</span>
                                        </button> 
                                    <?php }?>
                                    </div>
                                </div>
                                <?php
                                    if ($engrave_value) {
                                ?>
                                <div class="step" data-step="3">
                                    <div class="fieldset">
                                        <div class="input-radio">
                                            <input id="is_engraving" type="checkbox" name="attribute_engrave" data-attribute_name="attribute_engrave" checked>
                                            <label for="is_engraving">¿Quieres grabar algo en esta identificación?</label>
                                        </div>
                                    </div>
                                    <div class="fieldset" id="show-engraving" style="display: none;">
                                        <h4 id="engravingTxt">Grabado (frente)</h4>
                                        <div class="all-engrave-content" style="gap: 30px;display:inline-flex;">
                                            <div class="multi-input-fields" id="engravingFields">
                                            <?php
                                            /* $engrave = get_field('engrave');

                                                if ($engrave && is_array($engrave)) {
                                                    $counter = 1;

                                                    foreach ($engrave as $key => $value) {
                                                        $maxlength = isset($value['subengrave']) ? $value['subengrave'] : '';
                                                        echo <<<HTML
                                                            <div class="input-group">
                                                                <input class="input-field" id="engrave{$counter}" name="engrave[]" minlength="0" maxlength="{$maxlength}" type="text">
                                                                <span class="character-count">{$maxlength}</span>
                                                            </div>
                                                        HTML;
                                                        $counter++;
                                                    }
                                                }*/
                                            ?>

                                            </div>
                                            <div id="canvas-container">
                                                <canvas id="engravedCanvas" width="<?php echo (get_field('live_preview_engrave_image_width', $product_id)??'400')?>" ></canvas>
                                            </div>
                                        </div>
                                    <div class="options-container">
                                        <?php if (current_user_can('administrator')) { ?>
                                            <hr id="dp-eng-css-fields">
                                        <?php } ?>
                                        <span class="hdn-inp-lbl">Spacing Top</span>
                                        <input type="hidden" id="padding-top" class="acf-input" data-field="preview_text_padding_top" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_padding_top', $product_id)??'1')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Spacing Bottom</span>
                                        <input type="hidden" id="padding-bottom" class="acf-input" data-field="preview_text_padding_bottom" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_padding_bottom', $product_id)??'1')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Spacing Left</span>
                                        <input type="hidden" id="padding-left" class="acf-input" data-field="preview_text_padding_left" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_padding_left', $product_id)??'1')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Spacing Right</span>
                                        <input type="hidden" id="padding-right" class="acf-input" data-field="preview_text_padding_right" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_padding_right', $product_id)??'1')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Text Size</span>
                                        <input type="hidden" id="font-size" class="acf-input" data-field="preview_text_font_size" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_font_size', $product_id)??'10')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Text Style</span>
                                        <input type="hidden" id="font-style" class="acf-input" data-field="preview_text_font_style" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_font_style', $product_id)??'Arial')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Line Spacing</span>
                                        <input type="hidden" id="line-spacing" class="acf-input" data-field="preview_text_line_spacing" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_line_spacing', $product_id)??'15')?>" onchange="updateCanvas()">
                                        
                                        <span class="hdn-inp-lbl">Text Color</span>
                                        <input type="hidden" id="text-color" class="acf-input" data-field="preview_text_color" data-pid="<?php echo $product_id;?>" value="<?php echo (get_field('preview_text_color', $product_id)??'black')?>" onchange="updateCanvas()">
                                        <input type="hidden" name="orientation" value="<?php echo (get_field('preview_text_orientation', $product_id)??'horizontal')?>" checked onchange="updateCanvas()">
                                        <?php
                                            $live_preview_front = get_field('live_preview_front', $product_id);
                                            $live_preview_back = get_field('live_preview_back', $product_id);
                                            // print_r($live_preview_front);exit;
                                            if ($live_preview_front) {
                                                echo '<input type="hidden" id="engraved_img_src" value="'.$live_preview_front.'">';
                                            } else {
                                                echo '<input type="hidden" id="engraved_img_src" value="'.$live_preview_back.'">';
                                            }
                                        ?>
                                    </div>


                                </div>
                                <div class="cta-btn-wrapper">
                                        <button type="button" class="cta-btn cta-bordered cta-back">
                                            <span class="cta-text">Atrás</span>
                                        </button>
                                        
                                        <button type="button" class="cta-btn cta-next" id="checkout">
                                            <span class="cta-text">verificar</span>
                                        </button>
                                </div>
                                <?php }?>
                            </div>
                            <input type="hidden" name="add-to-cart" value="<?php echo $product_id?>">
                            <input type="hidden" id="product_id" name="product_id" value="<?php echo $product_id?>">
                            <input type="hidden" id="variation_id" name="variation_id" value="">
                        </form>
                        <!-- Login/Signup Modal -->
                        <div id="loginSignupModal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <div id="modalTabs">
                                    <button type="button" id="loginTab" class="active">Acceso</button>
                                    <button type="button" id="signupTab">Inscribirse</button>
                                </div>
                                <div id="messageSec" class="form-content" style="display: none;">
                                    <div id="messageText"></div>
                                </div>
                                <div id="loginForm" class="form-content active">
                                    <h4>Acceso</h4>
                                    <form id="login-form" method="post">
                                        <input type="email" name="email" placeholder="Correo electrónico" required>
                                        <input type="password" name="password" placeholder="Contraseña" required>
                                        <button type="submit">Acceso</button>
                                        <div class="open-lost-pass"><a id="lost-pass-link">Contraseña perdida</a></div>
                                    </form>
                                </div>

                                <!-- <div id="signupForm" class="form-content" style="display: none;">
                                    <h4>Sign Up</h4>
                                    <form id="signup-form" method="post">
                                        <input type="text" name="name" placeholder="Name" required>
                                        <input type="email" name="email" placeholder="Email" required>
                                        <input type="password" name="password" placeholder="Password" required>
                                        <button type="submit">Sign Up</button>
                                    </form>
                                </div> -->

                                <!-- Password reset form  -->
                                <div id="password-reset-form" class="password-reset-form" style="display: none;">
                                    
                                    <form method="post" id="password-reset" class="woocommerce-ResetPassword lost_reset_password">
                                        <p><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

                                        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                                            <label for="user_login"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                                            <input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" aria-required="true"  required/>
                                        </p>

                                        <div class="clear"></div>
                                        <p class="woocommerce-form-row form-row">
                                            <input type="hidden" name="wc_reset_password" value="true" />
                                            <button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>"><?php esc_html_e( 'Reset password', 'woocommerce' ); ?></button>
                                        </p>
                                    </form>
                                </div>
                                <div id="signupForm" class="form-content" style="display: none;">
                                    <h4>Inscribirse</h4>
                                    <form id="signup-form" method="post">
                                        <input type="text" name="name" placeholder="Nombre" required>
                                        <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
                                        <input type="email" id="confirm-email" name="confirm_email" placeholder="Confirmar correo electrónico" required>
                                        <div id="email-match-message" style="color: red; display: none;">Los correos electrónicos coinciden</div>
                                        <input type="password" id="password" name="password" placeholder="Contraseña" required>
                                        <input type="password" id="confirm-password" name="confirm_password" placeholder="confirmar Contraseña" required>
                                        <div id="password-match-message" style="color: red; display: none;">-</div>
                                        <button type="submit">Inscribirse</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div></div>
</section>

<?php
//Woocommerce description   
echo do_shortcode('[elementor-template id="2379"]');

//Woocommerce Related Products
echo do_shortcode('[elementor-template id="2383"]');    
                                   
//Pre Footer                                   
echo do_shortcode('[elementor-template id="276"]'); 
                                   
get_footer( 'shop' );