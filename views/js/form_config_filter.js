var current_filter;

function removeCartRuleOption(item)
{
	$('#product_rule_select option:selected').remove().appendTo('#product_rule_unselect');
}

function addCartRuleOption()
{
    $('#product_rule_unselect option:selected').remove().appendTo('#product_rule_select');
}

function updateProductRuleShortDescription()
{
    var arrayValues = $.map($('#product_rule_select option'), function(e) { return e.value; });
    if (arrayValues.length === 0) {
        current_filter.find('.product_input input').val('');
        current_filter.find('.product_filter input').val('');
    } else  {
        current_filter.find('.product_filter input').val($.map($('#product_rule_select option'), function(e) { return e.text; }));
        current_filter.find('.product_input input').val(arrayValues.join(','));
    }
}

$(document).ready(function() { 
    $('#buttonAddProductRuleGroup').click(function() {
        if ($( "#product_rule_type option:selected").val() === '') {
            return;
        }
        var product_rule_line = $('#product_rule tr').clone();
        product_rule_line.attr('data-type', $( "#product_rule_type option:selected").val());
        product_rule_line.find(".product_input input:eq(0)")
                         .attr('name', 'product_rule_select[' + $("#product_rule_type option:selected").val() + '][]');
        product_rule_line.find('.type').html($( "#product_rule_type option:selected" ).text());
        $('#product_rule_table tbody').append(product_rule_line);
    });

    $('body').on('click', '#product_rule_select_add', function() {
        addCartRuleOption();
        updateProductRuleShortDescription();
    });

    $('body').on('click', '#product_rule_select_remove', function() {
        removeCartRuleOption(); 
        updateProductRuleShortDescription();
    });

    $('body').on('click', '.product_rule_choose_link', function(event) {
        event.preventDefault();
        current_filter = $(this).parents('tr');

        $.ajax({
            url: url_product_selection_form,
            type: 'POST',
            dataType: 'html',
            async: true,
            data: {
                ajax: true,
                action: 'ProductSelectionConfigForm',
                selected: current_filter.find('.product_input input').val(),
                product_rule_type: $(this).parents('tr').data('type'),
            },
            success: function (responseHtml) {
                $.fancybox(responseHtml, {
                    autoDimensions: false,
                    autoSize: false,
                    width: 600,
                    autoHeight: true,
                    helpers: {
                        overlay: {
                            locked: false       
                        }
                    },
                });
            }
        });
    });

    $('body').on('click', '.product_rule_remove', function() {
        $(this).parents('tr').remove();
    });

    $("#product_rule_table tr" ).each(function(index) {
        var current_line = $(this);
        $.ajax({
            url: url_product_selection_form,
            type: 'POST',
            dataType: 'html',
            async: true,
            data: {
                ajax: true,
                action: 'ProductSelectionConfigForm',
                selected: $(this).find('.product_input input').val(),
                product_rule_type: $(this).data('type'),
            },
            success: function (responseHtml) {
                var arrayValues = $.map($(responseHtml).find('#product_rule_select option'), function(e) { return e.text; });
                if (arrayValues.length > 0) {
                    current_line.find('.product_filter input').val(arrayValues.join(','));
                }
            }
        });
      });
});