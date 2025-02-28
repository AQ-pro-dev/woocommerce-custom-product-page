jQuery(document).ready(function($) {
    // Add a new color mapping row
    $('.add-color').on('click', function() {
        var row = `
            <tr>
                <td><input type="text" name="my_woocommerce_plugin_color_mappings[new][name][]" value="" /></td>
                <td><input type="text" name="my_woocommerce_plugin_color_mappings[new][code][]" value="" /></td>
                <td><button type="button" class="button remove-color">Remove</button></td>
            </tr>
        `;
        $('#color-mappings-table tbody').append(row);
    });

    // Remove a color mapping row
    $('#color-mappings-table').on('click', '.remove-color', function() {
        $(this).closest('tr').remove();
    });
});
