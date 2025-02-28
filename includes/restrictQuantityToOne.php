<?php
add_action('woocommerce_before_add_to_cart_button', 'restrict_qunatity_to_one');

function restrict_qunatity_to_one(){
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.input-qty').attr(max, 1).val('1').prop('readonly', true);

            $('.input-qty').on('keydown keyup change', function(e){
                e.preventDefault();
                console.log('It is working');
            });
            // Also, ensure that the value is 1 if the user manually changes it
            $('.input-qty').on('input', function() {
                if ($(this).val() > 1) {
                    $(this).val('1');
                }
            });

            $('.qty').on('keydown keyup change', function(e){
                e.preventDefault();
                console.log('It is working');
            });
            // Also, ensure that the value is 1 if the user manually changes it
            $('.qty').on('input', function() {
                if ($(this).val() > 1) {
                    $(this).val('1');
                }
            });
        });
    </script>
<?php
}