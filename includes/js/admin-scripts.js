jQuery(document).ready(function($) {

    var rfmpNewFieldId = 0;

    $("#rfmp_fields tbody, #rfmp_priceoptions tbody").sortable({
        handle: ".sort",
        cursor: "move",
        axis:   "y",
        update: function() {
            rfmpRefreshCountryFields();
        }
    });

    $("#rfmp_fields").on('click', 'td .delete', function() {
        $(this).closest('tr').remove();
        rfmpRefreshCountryFields();
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
        rfmpRefreshCountryFields();
    });

    $("#rfmp_shipping_country_costs").on('click', 'td .delete', function() {
        $(this).closest('tr').remove();
    });

    $("#rfmp_add_shipping_country_cost").on('click', function() {
        $("#rfmp_shipping_country_costs tbody").append($("#rfmp_template_shipping_country_cost").html());
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
        var $value = $(this).closest('td').next('td').next('td').find(".rfmp_value");
        var $cell  = $value.closest('td');

        $cell.find(".rfmp_countries_button").remove();

        if ($(this).val() == 'dropdown' || $(this).val() == 'radio') {
            $value.attr('placeholder', 'value1|value2|value3').show();
        } else if ($(this).val() == 'confirm') {
            $value.attr('placeholder', rfmp_i18n.confirm_placeholder).show();
        } else if ($(this).val() == 'country') {
            // countries to exclude, the selection is stored in the value of the field
            $value.val('').hide();
            $cell.append($("#rfmp_template_field_countries").html());
            rfmpRefreshCountryButtons();
        } else {
            $value.val('').hide();
        }

        rfmpRefreshCountryFields();
    });

    /**
     * The countries excluded from a country field are stored in the value of
     * that field, separated by a pipe. They are edited in a shared modal.
     */
    var $countriesModal = $("#rfmp_countries_modal").appendTo("body");
    var $countriesInput = null;

    function rfmpExcludedCountries($input) {
        var value = $input && $input.val() ? $input.val() : '';

        return value ? value.split('|').filter(Boolean) : [];
    }

    function rfmpRefreshCountryButtons() {
        $("#rfmp_fields .rfmp_countries_button").each(function() {
            var excluded = rfmpExcludedCountries($(this).closest('td').find('[name="rfmp_fields_value[]"]'));

            var label = excluded.length === 1 ? rfmp_i18n.country_excluded : rfmp_i18n.countries_excluded;

            $(this).text(excluded.length ? label.replace('%d', excluded.length) : rfmp_i18n.exclude_countries);
        });
    }

    function rfmpCountriesChecked() {
        return $countriesModal.find(".rfmp-modal-list input:checked");
    }

    function rfmpRefreshCountriesCount() {
        $countriesModal.find(".rfmp-modal-count")
            .text(rfmp_i18n.countries_selected.replace('%d', rfmpCountriesChecked().length));
    }

    function rfmpCloseCountries() {
        $countriesModal.hide();
        $countriesInput = null;
    }

    $("#rfmp_fields").on('click', '.rfmp_countries_button', function() {
        $countriesInput = $(this).closest('td').find('[name="rfmp_fields_value[]"]');

        var excluded = rfmpExcludedCountries($countriesInput);
        $countriesModal.find(".rfmp-modal-list input").each(function() {
            this.checked = excluded.indexOf(this.value) !== -1;
        });

        $("#rfmp_countries_search").val('');
        $countriesModal.find(".rfmp-modal-country").show();
        $countriesModal.find(".rfmp-modal-empty").hide();
        rfmpRefreshCountriesCount();
        $countriesModal.show();
        $("#rfmp_countries_search").trigger('focus');
    });

    $countriesModal.on('click', '.rfmp_countries_save', function() {
        if ($countriesInput) {
            $countriesInput.val(rfmpCountriesChecked().map(function() {
                return this.value;
            }).get().join('|'));
        }

        rfmpRefreshCountryButtons();
        rfmpCloseCountries();
    });

    $countriesModal.on('click', '.rfmp_countries_cancel, .rfmp-modal-close, .rfmp-modal-backdrop', function() {
        rfmpCloseCountries();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $countriesModal.is(':visible')) {
            rfmpCloseCountries();
        }
    });

    $countriesModal.on('change', '.rfmp-modal-list input', function() {
        rfmpRefreshCountriesCount();
    });

    // select all and deselect all only apply to the countries the search shows
    $countriesModal.on('click', '.rfmp_countries_all, .rfmp_countries_none', function() {
        var checked = $(this).hasClass('rfmp_countries_all');

        $countriesModal.find(".rfmp-modal-country:visible input").prop('checked', checked);
        rfmpRefreshCountriesCount();
    });

    $countriesModal.on('input', '#rfmp_countries_search', function() {
        var search = $(this).val().toLowerCase();
        var found  = 0;

        $countriesModal.find(".rfmp-modal-country").each(function() {
            var match = $(this).text().toLowerCase().indexOf(search) !== -1;

            $(this).toggle(match);
            found += match ? 1 : 0;
        });

        $countriesModal.find(".rfmp-modal-empty").toggle(found === 0);
    });

    rfmpRefreshCountryButtons();

    $("#rfmp_fields").on('keyup', 'input[name="rfmp_fields_label[]"]', function() {
        rfmpRefreshCountryFields();
    });

    // the checkbox itself isn't posted, so that every field posts exactly one value
    // and the required fields keep matching their field after sorting
    $("#rfmp_fields").on('change', '.rfmp_required', function() {
        $(this).prev(".rfmp_required_value").val(this.checked ? '1' : '0');
    });

    /**
     * Get the field type of a row in the form fields table
     */
    function rfmpFieldType($row) {
        var $type = $row.find("select.rfmp_type");

        return $type.length ? $type.val() : $row.find('input[name="rfmp_fields_type[]"]').val();
    }

    /**
     * Fill the country field selector of the shipping costs with all
     * country fields that are currently in the form fields table
     */
    function rfmpRefreshCountryFields() {
        var $select = $("#rfmp_shipping_country_field");
        if (!$select.length) {
            return;
        }

        var selected = $select.val();
        $select.find("option:not(:first)").remove();

        $("#rfmp_fields tbody tr").each(function() {
            var $row = $(this);
            if (rfmpFieldType($row) !== 'country') {
                return;
            }

            var $id = $row.find('input[name="rfmp_fields_id[]"]');
            if (!$id.val()) {
                $id.val('n' + (++rfmpNewFieldId) + '_' + new Date().getTime());
            }

            $select.append($("<option>").val($id.val()).text($row.find('input[name="rfmp_fields_label[]"]').val() || rfmp_i18n.country));
        });

        $select.val(selected);
        if ($select.val() === null) {
            $select.val('');
        }
    }

    rfmpRefreshCountryFields();

    $('#rfmp_tabs').tabs();

});