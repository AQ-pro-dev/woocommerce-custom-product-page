jQuery(document).ready(function($) {
    $('.color-swatches .swatch').on('click', function() {
        var color = $(this).data('color');
        $('select[name="attribute_pa_color"]').val(color).trigger('change');
    });
});
