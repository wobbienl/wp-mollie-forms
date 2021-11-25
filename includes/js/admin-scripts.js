jQuery(document).ready(function($) {

    $("#rfmp_fields tbody, #rfmp_priceoptions tbody").sortable({
        handle: ".sort",
        cursor: "move",
        axis:   "y"
    });

    $("#rfmp_fields").on('click', 'td .delete', function() {
        $(this).closest('tr').remove();
    });

    $("#rfmp_priceoptions").on('click', 'td .delete', function() {
        $(this).closest('tr').hide();
        $(this).closest('td').find('.rfmp_priceoptions_new').val("-1");
    });

    $("#rfmp_discountcodes").on('click', 'td .delete', function() {
        $(this).closest('tr').hide();
        $(this).closest('tr').find('td:nth-child(2)').find('[name^="rfmp_discount_code"]').val('');
    });

    $("#rfmp_add_field").on('click', function() {
        $("#rfmp_fields tbody").prepend($("#rfmp_template_field").html());
    });

    $("#rfmp_add_priceoption").on('click', function() {
        $("#rfmp_priceoptions tbody").append($("#rfmp_template_priceoption").html());
    });

    $("#rfmp_add_discountcode").on('click', function() {
        $("#rfmp_discountcodes tbody").append($("#rfmp_template_discountcode").html());
    });

    $("body").on('change', '.rfmp_frequency', function() {
        if ($(this).val() != 'once')
        {
            $(this).prev("input").show();
            $(this).closest('td').next('td').find("input").show();
        }
        else
        {
            $(this).prev("input").hide();
            $(this).closest('td').next('td').find("input").hide();
        }
    });

    $("body").on('change', '.rfmp_pricetype', function() {
        var input = $(this).next("input");
        if ($(this).val() != 'open')
            input.attr('placeholder', input.data('ph-fixed'));
        else
            input.attr('placeholder', input.data('ph-open'));
    });

    $("body").on('change', '[name=rfmp_after_payment]', function() {
        if ($(this).val() == 'redirect')
        {
            $('.rfmp_after_payment_redirect').show();
            $('.rfmp_after_payment_message').hide();
        }
        else
        {
            $('.rfmp_after_payment_redirect').hide();
            $('.rfmp_after_payment_message').show();
        }
    });

    $("body").on('change', '.rfmp_type', function() {
        if ($(this).val() == 'dropdown' || $(this).val() == 'radio')
            $(this).closest('td').next('td').next('td').find(".rfmp_value").show();
        else
            $(this).closest('td').next('td').next('td').find(".rfmp_value").val('').hide();
    });

    $('#rfmp_tabs').tabs();

});