$(document).ready(function(e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var $customer = $('#gymcustomer');
    $customer.change(function(e) {
        localStorage.setItem('gymcustomer', $(this).val());
        if (site.settings.customer_group_discount == 1) {
            setOrderDiscountByCustomerGroup($(this).val());
        }
        //$('#gymcustomer_id').val($(this).val());
    });
    var $saleman_by = $('#gymsaleman_by');
        $saleman_by.change(function(e) {
        localStorage.setItem('gymsaleman_by', $(this).val());
    });
    if ((gymcustomer = localStorage.getItem('gymcustomer'))) {
        $customer.val(gymcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function(element, callback) {
                $.ajax({
                    type: 'get',
                    async: false,
                    url: site.base_url + 'customers/getCustomer/' + $(element).val(),
                    dataType: 'json',
                    success: function(data) {
                        callback(data[0]);
                    },
                });
            },
            ajax: {
                url: site.base_url + 'customers/suggestions',
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: '', text: 'No Match Found' }] };
                    }
                },
            },
        });
    } else {
        nsCustomer();
    }
    if (site.settings.customer_group_discount == 1) {
        if ($('#gymcustomer').val() != '' && $('#gymcustomer').val() != null) {
            // setOrderDiscountByCustomerGroup($('#gymcustomer').val());
        }
    }
    // Order level shipping and discount localStorage
    if ((gymdiscount = localStorage.getItem('gymdiscount'))) {
        $('#gymdiscount').val(gymdiscount);
    }
    
    $('#gymtax2').change(function(e) {
        localStorage.setItem('gymtax2', $(this).val());
        $('#gymtax2').val($(this).val());
    });
    if ((gymtax2 = localStorage.getItem('gymtax2'))) {
        $('#gymtax2').select2('val', gymtax2);
    }
    $('#gymsale_status').change(function(e) {
        localStorage.setItem('gymsale_status', $(this).val());
    });
    if ((gymsale_status = localStorage.getItem('gymsale_status'))) {
        $('#gymsale_status').select2('val', gymsale_status);
    }
    $('#gympayment_status').change(function(e) {
        var ps = $(this).val();
        localStorage.setItem('gympayment_status', ps);
        if (ps == 'booking' || ps == 'partial' || ps == 'paid') {
            if (ps == 'paid') {
                $('#amount_1').val(formatDecimal(parseFloat(total + invoice_tax - order_discount + shipping)));
                $('#payment_expired').hide();
            } else {
                $('#payment_expired').show();
            }
            $('#payments').slideDown();
            $('#pcc_no_1').focus();
        } else {
            $('#payments').slideUp();
        }
    });
    if ((gympayment_status = localStorage.getItem('gympayment_status'))) {
        $('#gympayment_status').select2('val', gympayment_status);
        var ps = gympayment_status;
        if (ps == 'booking' || ps == 'partial' || ps == 'paid') {
            $('#payments').slideDown();
            $('#pcc_no_1').focus();
        } else {
            $('#payments').slideUp();
        }
    }

    $(document).on('change', '.paid_by', function() {
        var p_val = $(this).val();
        localStorage.setItem('paid_by', p_val);
        $('#rpaidby').val(p_val);
        if (p_val == 'cash' || p_val == 'other') {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').show();
            $('#payment_note_1').focus();
        } else if (p_val == 'CC') {
            $('.pcheque_1').hide();
            $('.pcash_1').hide();
            $('.pcc_1').show();
            $('#pcc_no_1').focus();
        } else if (p_val == 'Cheque') {
            $('.pcc_1').hide();
            $('.pcash_1').hide();
            $('.pcheque_1').show();
            $('#cheque_no_1').focus();
        } else {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').hide();
        }
        if (p_val == 'gift_card') {
            $('.gc').show();
            $('.ngc').hide();
            $('#gift_card_no').focus();
        } else {
            $('.ngc').show();
            $('.gc').hide();
            $('#gc_details').html('');
        }
    });

    if ((paid_by = localStorage.getItem('paid_by'))) {
        var p_val = paid_by;
        $('.paid_by').select2('val', paid_by);
        $('#rpaidby').val(p_val);
        if (p_val == 'cash' || p_val == 'other') {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').show();
            $('#payment_note_1').focus();
        } else if (p_val == 'CC') {
            $('.pcheque_1').hide();
            $('.pcash_1').hide();
            $('.pcc_1').show();
            $('#pcc_no_1').focus();
        } else if (p_val == 'Cheque') {
            $('.pcc_1').hide();
            $('.pcash_1').hide();
            $('.pcheque_1').show();
            $('#cheque_no_1').focus();
        } else {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').hide();
        }
        if (p_val == 'gift_card') {
            $('.gc').show();
            $('.ngc').hide();
            $('#gift_card_no').focus();
        } else {
            $('.ngc').show();
            $('.gc').hide();
            $('#gc_details').html('');
        }
    }

    if ((gift_card_no = localStorage.getItem('gift_card_no'))) {
        $('#gift_card_no').val(gift_card_no);
    }
    $('#gift_card_no').change(function(e) {
        localStorage.setItem('gift_card_no', $(this).val());
    });

    if ((amount_1 = localStorage.getItem('amount_1'))) {
        $('#amount_1').val(amount_1);
    }
    $('#amount_1').change(function(e) {
        localStorage.setItem('amount_1', $(this).val());
    });

    if ((paid_by_1 = localStorage.getItem('paid_by_1'))) {
        $('#paid_by_1').val(paid_by_1);
    }
    $('#paid_by_1').change(function(e) {
        localStorage.setItem('paid_by_1', $(this).val());
    });

    if ((pcc_holder_1 = localStorage.getItem('pcc_holder_1'))) {
        $('#pcc_holder_1').val(pcc_holder_1);
    }
    $('#pcc_holder_1').change(function(e) {
        localStorage.setItem('pcc_holder_1', $(this).val());
    });

    if ((pcc_type_1 = localStorage.getItem('pcc_type_1'))) {
        $('#pcc_type_1').select2('val', pcc_type_1);
    }
    $('#pcc_type_1').change(function(e) {
        localStorage.setItem('pcc_type_1', $(this).val());
    });

    if ((pcc_month_1 = localStorage.getItem('pcc_month_1'))) {
        $('#pcc_month_1').val(pcc_month_1);
    }
    $('#pcc_month_1').change(function(e) {
        localStorage.setItem('pcc_month_1', $(this).val());
    });

    if ((pcc_year_1 = localStorage.getItem('pcc_year_1'))) {
        $('#pcc_year_1').val(pcc_year_1);
    }
    $('#pcc_year_1').change(function(e) {
        localStorage.setItem('pcc_year_1', $(this).val());
    });

    if ((pcc_no_1 = localStorage.getItem('pcc_no_1'))) {
        $('#pcc_no_1').val(pcc_no_1);
    }
    $('#pcc_no_1').change(function(e) {
        var pcc_no = $(this).val();
        localStorage.setItem('pcc_no_1', pcc_no);
        var CardType = null;
        var ccn1 = pcc_no.charAt(0);
        if (ccn1 == 4) CardType = 'Visa';
        else if (ccn1 == 5) CardType = 'MasterCard';
        else if (ccn1 == 3) CardType = 'Amex';
        else if (ccn1 == 6) CardType = 'Discover';
        else CardType = 'Visa';

        $('#pcc_type_1').select2('val', CardType);
    });

    if ((cheque_no_1 = localStorage.getItem('cheque_no_1'))) {
        $('#cheque_no_1').val(cheque_no_1);
    }
    $('#cheque_no_1').change(function(e) {
        localStorage.setItem('cheque_no_1', $(this).val());
    });

    if ((payment_note_1 = localStorage.getItem('payment_note_1'))) {
        $('#payment_note_1').redactor('set', payment_note_1);
    }
    $('#payment_note_1').redactor('destroy');
    $('#payment_note_1').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('payment_note_1', v);
        },
    });

    var old_payment_term;
    $('#gympayment_term')
        .focus(function() {
            old_payment_term = $(this).val();
        })
        .change(function(e) {
            var new_payment_term = $(this).val() ? parseFloat($(this).val()) : 0;
            if (!is_numeric($(this).val())) {
                $(this).val(old_payment_term);
                bootbox.alert(lang.unexpected_value);
                return;
            } else {
                localStorage.setItem('gympayment_term', new_payment_term);
                $('#gympayment_term').val(new_payment_term);
            }
        });
    if ((gympayment_term = localStorage.getItem('gympayment_term'))) {
        $('#gympayment_term').val(gympayment_term);
    }

    var old_shipping;
    $('#gymshipping')
        .focus(function() {
            old_shipping = $(this).val();
        })
        .change(function() {
            var gymsh = $(this).val() ? $(this).val() : 0;
            if (!is_numeric(gymsh)) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            shipping = parseFloat(gymsh);
            localStorage.setItem('gymshipping', shipping);
            var gtotal = total + invoice_tax - order_discount + shipping;
            $('#gtotal').text(formatMoney(gtotal));
            $('#tship').text(formatMoney(shipping));
        });
    if ((gymshipping = localStorage.getItem('gymshipping'))) {
        shipping = parseFloat(gymshipping);
        $('#gymshipping').val(shipping);
    } else {
        shipping = 0;
    }
    $('#add_sale, #edit_sale').attr('disabled', true);
    $(document).on('change', '.start_time', function() {
        var item_id = $(this)
            .closest('tr')
            .attr('data-item-id');
        gymitems[item_id].row.start_time = $(this).val();
        localStorage.setItem('gymitems', JSON.stringify(gymitems));
    });

    $(document).on('change', '.end_time', function() {
        var item_id = $(this)
            .closest('tr')
            .attr('data-item-id');
        gymitems[item_id].row.end_time = $(this).val();
        localStorage.setItem('end_time', JSON.stringify(gymitems));
    });
   
    // If there is any item in localStorage
    if (localStorage.getItem('gymitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function(e) {
        bootbox.confirm(lang.r_u_sure, function(result) {
            if (result) {
                if (localStorage.getItem('gymitems')) {
                    localStorage.removeItem('gymitems');
                }
                if (localStorage.getItem('gymdiscount')) {
                    localStorage.removeItem('gymdiscount');
                }
                if (localStorage.getItem('gymtax2')) {
                    localStorage.removeItem('gymtax2');
                }
                if (localStorage.getItem('gymshipping')) {
                    localStorage.removeItem('gymshipping');
                }
                if (localStorage.getItem('gymref')) {
                    localStorage.removeItem('gymref');
                }
                if (localStorage.getItem('gymwarehouse')) {
                    localStorage.removeItem('gymwarehouse');
                }
                if (localStorage.getItem('gymoverselling')) {
                    localStorage.removeItem('gymoverselling');
                }
                if (localStorage.getItem('gymnote')) {
                    localStorage.removeItem('gymnote');
                }
                if (localStorage.getItem('gyminnote')) {
                    localStorage.removeItem('gyminnote');
                }
                if (localStorage.getItem('gymcustomer')) {
                    localStorage.removeItem('gymcustomer');
                }
                if (localStorage.getItem('gymcurrency')) {
                    localStorage.removeItem('gymcurrency');
                }
                if (localStorage.getItem('gymdate')) {
                    localStorage.removeItem('gymdate');
                }
                if (localStorage.getItem('gymstatus')) {
                    localStorage.removeItem('gymstatus');
                }
                if (localStorage.getItem('gymbiller')) {
                    localStorage.removeItem('gymbiller');
                }
                if (localStorage.getItem('gift_card_no')) {
                    localStorage.removeItem('gift_card_no');
                }
                if (localStorage.getItem('gymsaleman_by')) {
                    localStorage.removeItem('gymsaleman_by');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage

    $('#gymref').change(function(e) {
        localStorage.setItem('gymref', $(this).val());
    });
    if ((gymref = localStorage.getItem('gymref'))) {
        $('#gymref').val(gymref);
    } 


    if ($('#gymwarehouse').val()) {
        var wh_id = $('#gymwarehouse').val();
        $.ajax({
            url: site.base_url + 'sales/getWarehouseByID_Ajax',
            type: 'GET',
            dataType: 'Json',
            data: {'warehouse_id':wh_id},
            success: function(data) {
                if(data != null) {
                    localStorage.setItem('gymoverselling', data.overselling);
                }
            }
        });
    }

    $('#gymwarehouse').change(function(e) {
        localStorage.setItem('gymwarehouse', $(this).val());
        $.ajax({
            url: site.base_url + 'sales/getWarehouseByID_Ajax',
            type: 'GET',
            dataType: 'Json',
            data: {'warehouse_id': $(this).val()},
            success: function(data) {
                if(data != null) {
                    localStorage.setItem('gymoverselling', data.overselling);
                }
            }
        });
    });
    if ((gymwarehouse = localStorage.getItem('gymwarehouse'))) {
        $('#gymwarehouse').select2('val', gymwarehouse);  
    }

    $('#gymnote').redactor('destroy');
    $('#gymnote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('gymnote', v);
        },
    });
    if ((gymnote = localStorage.getItem('gymnote'))) {
        $('#gymnote').redactor('set', gymnote);
    }
    $('#gyminnote').redactor('destroy');
    $('#gyminnote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('gyminnote', v);
        },
    });
    if ((gyminnote = localStorage.getItem('gyminnote'))) {
        $('#gyminnote').redactor('set', gyminnote);
    }

    // prevent default action usln enter
    $('body').bind('keypress', function(e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Order tax calculation
    if (site.settings.tax2 != 0) {
        $('#gymtax2').change(function() {
            localStorage.setItem('gymtax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calculation
    var old_gymdiscount;
    $('#gymdiscount')
        .focus(function() {
            old_gymdiscount = $(this).val();
        })
        .change(function() {
            var new_discount = $(this).val() ? $(this).val() : '0';
            if (is_valid_discount(new_discount)) {
                localStorage.removeItem('gymdiscount');
                localStorage.setItem('gymdiscount', new_discount);
                loadItems();
                return;
            } else {
                $(this).val(old_gymdiscount);
                bootbox.alert(lang.unexpected_value);
                return;
            }
        });

    /* ----------------------
     * Delete Row Method
     * ---------------------- */
    $(document).on('click', '.gymdel', function() {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete gymitems[item_id];
        row.remove();
        if (gymitems.hasOwnProperty(item_id)) {} else {
            localStorage.setItem('gymitems', JSON.stringify(gymitems));
            loadItems();
            return;
        }
    });
    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
    $(document).on('click', '.edit', function() {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = gymitems[item_id];
        var qty = row
            .children()
            .children('.rquantity')
            .val(),
            product_option = row
            .children()
            .children('.roption')
            .val(),
            unit_price = formatDecimal(
                row
                .children()
                .children('.ruprice')
                .val()
            ),
            discount = row
            .children()
            .children('.rdiscount')
            .val();
        if (item.options !== false) {
            $.each(item.options, function() {
                if (this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price) + parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1 && 0) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0,
                ds = discount ? discount : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat((unit_price * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate,
                pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function() {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {
                            if (gymitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }
                        } else if (this.type == 2) {
                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;
                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(
                row
                .children()
                .children('.rserial')
                .val()
            );
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options !== false) {
            var o = 1;
            opt = $('<select id="poption" name="poption" class="form-control select" />');
            $.each(item.options, function() {
                if (o == 1) {
                    if (product_option == '') {
                        product_variant = this.id;
                    } else {
                        product_variant = product_option;
                    }
                }
                $('<option />', { value: this.id, text: this.name }).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }
        uopt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.units) {
            uopt = $('<select id="punit" name="punit" class="form-control select" />');
            $.each(item.units, function() {
                if (this.id == item.row.unit) {
                    $('<option />', { value: this.id, text: this.name, selected: true }).appendTo(uopt);
                } else {
                    $('<option />', { value: this.id, text: this.name }).appendTo(uopt);
                }
            });
        }
        //--------product option-----------
        if (site.settings.product_option !== 0) {
            var popt = '<p style="margin: 12px 0 0 0;">n/a</p>';
            if (site.settings.product_option !== 0) {
                if (item.product_options) {
                    popt = $('<select id="popt" name="popt" class="form-control select" />');
                    $.each(item.product_options, function() {
                        if (this.id == item.row.option) {
                            $('<option />', { value: this.option_id, text: this.name, selected: true }).appendTo(popt);
                        } else {
                            $('<option />', { value: this.option_id, text: this.name }).appendTo(popt);
                        }
                    });
                }
            }
            $('#poptions-div_1').html(popt);
        }
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(
            row
            .children()
            .children('.rserial')
            .val()
        );
        $('#pdiscount').select2('val', discount);
        $('#pdiscount').val(discount);
        $('#padiscount').val('');
        $('#psubt').val(row.find('.ssubtotal').text());
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal')
            .appendTo('body')
            .modal('show');
        $('#pdescription').val(item.row.details);
    }); 
    $('#prModal').on('shown.bs.modal', function(e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    }); 
    $(document).on('change', '#pprice, #ptax, #pdiscount', function() {
        var row         = $('#' + $('#row_id').val());
        var item_id     = row.attr('data-item-id');
        var unit_price  = parseFloat($('#pprice').val());
        var item        = gymitems[item_id];
        var ds          = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(),
            item_tax_method = item.row.tax_method;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function() {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    }); 
    $(document).on('change', '#punit', function() {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = gymitems[item_id]; 
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        } 
        var opt             = $('#poption').val(),
            unit            = $('#punit').val(),
            base_quantity   = $('#pquantity').val(),
            aprice          = 0; 
        if (item.options !== false) {
            $.each(item.options, function() {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        } 
        if (site.settings.select_price == 1 && item.set_price != "") {
            if (item.set_price) {
                $.each(item.set_price, function () {
                    if (this.id == unit) {
                        base_quantity = unitToBaseQty($('#pquantity').val(), this);
                        $('#pprice').val(formatDecimal((parseFloat(this.price)), 4)).change();
                    }
                });
            } else {
                $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
            }
        } else { 
            if (item.units && unit != gymitems[item_id].row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == unit) {
                        base_quantity = unitToBaseQty($('#pquantity').val(), this);
                        // $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4)).change();
                        $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4)).change();
                    }
                });
            } else {
                $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
            }
        } 
        // if (item.units && unit != gymitems[item_id].row.base_unit) {
        //     $.each(item.units, function() {
        //         if (this.id == unit) {
        //             base_quantity = unitToBaseQty($('#pquantity').val(), this);
        //             $('#pprice').val(formatDecimal(parseFloat(item.row.base_unit_price + aprice) * unitToBaseQty(1, this), 4)).change();
        //         }
        //     });
        // } else {
        //     $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
        // } 
    }); 
    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function() {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'),
            new_pr_tax = $('#ptax').val(),
            new_pr_tax_rate = false; 
        if (new_pr_tax) {
            $.each(tax_rates, function() {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != gymitems[item_id].row.base_unit) {
            $.each(gymitems[item_id].units, function() {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        if (item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function() {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price - parseFloat(this.price);
                    // price = price - parseFloat(this.price) * parseFloat(base_quantity);
                }
            });
        } 
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if (!is_valid_discount($('#pdiscount').val()) || ($('#pdiscount').val() != 0 && $('#pdiscount').val() > price)) {
                bootbox.alert(lang.unexpected_value);
                $("#pdiscount").val($('#pdiscount').attr('data'));
                return false;
            }else{
                $('#pdiscount').attr('data', $('#pdiscount').val());
            }
        } 
        var discount = $('#pdiscount').val() ? $('#pdiscount').val() : '';
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        } 
        var quantity = parseFloat($('#pquantity').val());
        // if (site.settings.product_discount == 1 && $('#padiscount').val()) {
        //     if (!is_numeric($('#padiscount').val()) || $('#padiscount').val() > price * quantity) {
        //         bootbox.alert(lang.unexpected_value);
        //         return false;
        //     }
        //     discount = formatDecimal(parseFloat($('#padiscount').val()) / quantity, 4);
        // }
        // console.log(discount);
        if (site.settings.product_option !== 0) {
            var option_id = $('#popt').val() ? $('#popt').val() : '';
            if (option_id != '') {
                $.ajax({
                    url: site.base_url + 'sales/option_number',
                    type: 'GET',
                    dataType: 'Json',
                    data: {'option_id':option_id},
                    success: function(data) {
                        gymitems[item_id].row.serial_no = data.options.last_no ? data.options.last_no : 0;
                        gymitems[item_id].row.max_serial = data.options.last_no;
                        gymitems[item_id].row.option_name = data.options.name;
                        localStorage.setItem('gymitems', JSON.stringify(gymitems));
                        loadItems();
                    }
                });
            }
        }
        $('.rserial').prop('readonly', true);

        if($('#poption').val()){
            var poption = $('#poption').val();
        }else{
            var poption = $('#popt').val() ? $('#popt').val() : '';
        }
        gymitems[item_id].row.fup = 1;
        gymitems[item_id].row.quantity = quantity;
        gymitems[item_id].row.base_quantity = parseFloat(base_quantity);
        gymitems[item_id].row.price = price;
        gymitems[item_id].row.unit = unit;
        gymitems[item_id].row.tax_rate = new_pr_tax;
        gymitems[item_id].tax_rate = new_pr_tax_rate;
        gymitems[item_id].row.discount = discount;
        gymitems[item_id].row.option = poption; //$('#poption').val() ? $('#poption').val() : '';
        gymitems[item_id].row.serial = $('#pserial').val();
        gymitems[item_id].row.details = $('#pdescription').val();
        localStorage.setItem('gymitems', JSON.stringify(gymitems));
        $('#prModal').modal('hide'); 
        loadItems();
        return;
    });
    $(document).on('change', '#padiscount', function() {
        if (site.settings.product_discount == 1 && $(this).val()) {
            var row = $('#' + $('#row_id').val());
            var item_id = row.attr('data-item-id'),
                new_pr_tax = $('#ptax').val(),
                new_pr_tax_rate = false;
            var item = gymitems[item_id];
            if (new_pr_tax) {
                $.each(tax_rates, function() {
                    if (this.id == new_pr_tax) {
                        new_pr_tax_rate = this;
                    }
                });
            }
            var quantity = parseFloat($('#pquantity').val());
            var price = parseFloat($('#pprice').val());
            var pr_tax = new_pr_tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            var total_tax = 0;
            if (site.settings.tax1 == 1 && 0) {
                if (pr_tax !== false && pr_tax != 0 && pr_tax.rate != 0) {
                    if (pr_tax.type == 1) {
                        if (item.row.tax_method == 0) {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            price = formatDecimal(price - parseFloat(pr_tax_val), 4);
                        } else {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / 100, 4);
                            price = formatDecimal(price + parseFloat(pr_tax_val), 4);
                        }
                    } else if (pr_tax.type == 2) {
                        price =
                            item.row.tax_method == 0 ?
                            formatDecimal(price - parseFloat(pr_tax.rate), 4) :
                            formatDecimal(price + parseFloat(pr_tax.rate), 4);
                    }
                }
            }
            var total = formatDecimal((price + parseFloat(pr_tax_val)) * quantity, 4);
            var expected_total = parseFloat($(this).val());
            var expected_discount = formatDecimal(((total - expected_total) / total) * 100, 4);
            $('#pdiscount').val(expected_discount + '%');
        }
    });

    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function() {
        var row = $('#' + $('#row_id').val()),
            opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = gymitems[item_id];
        var unit = $('#punit').val(),
            base_quantity = parseFloat($('#pquantity').val()),
            base_unit_price = item.row.base_unit_price;
        if (unit != gymitems[item_id].row.base_unit) {
            $.each(gymitems[item_id].units, function() {
                if (this.id == unit) {
                    base_unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice')
            .val(parseFloat(base_unit_price))
            .trigger('change');
        if (item.options !== false) {
            $.each(item.options, function() {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice')
                        .val(parseFloat(base_unit_price) + parseFloat(this.price))
                        .trigger('change');
                    // .val(parseFloat(base_unit_price) + parseFloat(this.price) * parseFloat(base_quantity))
                }
            });
        }
    });

    /* ------------------------------
     * Sell Gift Card modal
     ------------------------------- */
    $(document).on('click', '#sellGiftCard', function(e) {
        if (count == 1) {
            gymitems = {};
            if ($('#gymcustomer').val()) {
                $('#gymcustomer').select2('readonly', true);
                $('#gymwarehouse').select2('readonly', true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#gcModal')
            .appendTo('body')
            .modal('show');
        return false;
    });

    $(document).on('click', '#addGiftCard', function(e) {
        var mid = new Date().getTime(),
            gccode = $('#gccard_no').val(),
            gcname = $('#gcname').val(),
            gcvalue = $('#gcvalue').val(),
            gccustomer = $('#gccustomer').val(),
            gcexpiry = $('#gcexpiry').val() ? $('#gcexpiry').val() : '',
            gcprice = parseFloat($('#gcprice').val());
        if (gccode == '' || gcvalue == '' || gcprice == '' || gcvalue == 0 || gcprice == 0) {
            $('#gcerror').text('Please fill the required fields');
            $('.gcerror-con').show();
            return false;
        }

        var gc_data = new Array();
        gc_data[0] = gccode;
        gc_data[1] = gcvalue;
        gc_data[2] = gccustomer;
        gc_data[3] = gcexpiry;
        //if (typeof gymitems === "undefined") {
        //    var gymitems = {};
        //}

        $.ajax({
            type: 'get',
            url: site.base_url + 'sales/sell_gift_card',
            dataType: 'json',
            data: { gcdata: gc_data },
            success: function(data) {
                if (data.result === 'success') {
                    gymitems[mid] = {
                        id: mid,
                        item_id: mid,
                        label: gcname + ' (' + gccode + ')',
                        row: {
                            id: mid,
                            code: gccode,
                            name: gcname,
                            quantity: 1,
                            base_quantity: 1,
                            price: gcprice,
                            real_unit_price: gcprice,
                            tax_rate: 0,
                            qty: 1,
                            type: 'manual',
                            discount: '0',
                            serial: '',
                            option: '',
                        },
                        tax_rate: false,
                        options: false,
                        units: false,
                    };
                    localStorage.setItem('gymitems', JSON.stringify(gymitems));
                    loadItems();
                    $('#gcModal').modal('hide');
                    $('#gccard_no').val('');
                    $('#gcvalue').val('');
                    $('#gcexpiry').val('');
                    $('#gcprice').val('');
                } else {
                    $('#gcerror').text(data.message);
                    $('.gcerror-con').show();
                }
            },
        });
        return false;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function(e) {
        if (count == 1) {
            gymitems = {};
            if ($('#gymcustomer').val()) {
                $('#gymcustomer').select2('readonly', true);
                $('#gymwarehouse').select2('readonly', true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal')
            .appendTo('body')
            .modal('show');
        return false;
    });

    $(document).on('click', '#addItemManually', function(e) {
        var mid = new Date().getTime(),
            mcode = $('#mcode').val(),
            mname = $('#mname').val(),
            mtax = parseInt($('#mtax').val()),
            munit = parseInt($('#munit').val()),
            munit_code = $("#munit option:selected").text(),
            mqty = parseFloat($('#mquantity').val()),
            mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
            unit_price = parseFloat($('#mprice').val()),
            mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function() {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            gymitems[mid] = {
                id: mid,
                item_id: mid,
                label: mname + ' (' + mcode + ')',
                row: {
                    id: mid,
                    code: mcode,
                    name: mname,
                    quantity: mqty,
                    base_quantity: mqty,
                    price: unit_price,
                    unit_price: unit_price,
                    real_unit_price: unit_price,
                    tax_rate: mtax,
                    unit: munit,
                    unit_code: munit_code,
                    tax_method: 0,
                    qty: mqty,
                    type: 'manual',
                    discount: mdiscount,
                    serial: '',
                    option: '',
                },
                tax_rate: mtax_rate,
                units: false,
                options: false,
            };
            localStorage.setItem('gymitems', JSON.stringify(gymitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#munit').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function() {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(),
            item_tax_method = 0;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function() {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });
    /* --------------------------
     * Edit Row Last Number Method
    --------------------------- */
    if (site.settings.product_option !== 0) {
        $(document).on('change', '.maxserial', function() {
            if (!is_numeric(parseFloat($(this).closest('tr').find(".rserial").val()))) {
                var serial = 0;
            } else {
                var serial = parseFloat($(this).closest('tr').find(".rserial").val());
            }
            var macserial = parseFloat($(this).val());
            if (!is_numeric(macserial) || macserial < serial) {
                bootbox.alert(lang.unexpected_value);
                $(this).val(0);
            }else{
                var item_id = $(this)
                    .closest('tr')
                    .attr('data-item-id');
                gymitems[item_id].row.max_serial = macserial;
                gymitems[item_id].row.qty = macserial - serial;
                gymitems[item_id].row.base_quantity = macserial - serial;
                
                localStorage.setItem('gymitems', JSON.stringify(gymitems));
                loadItems();
            }
        });
    }
    /* --------------------------
     * Edit Row Quantity Method
    --------------------------- */
    var old_row_qty;
    $(document)
        .on('focus', '.rquantity', function() {
            old_row_qty = $(this).val();
        })
        .on('change', '.rquantity', function() {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');



            if( gymitems[item_id].quantity==null){
                gymitems[item_id].row.quantity = new_qty;
            }else{
                gymitems[item_id].quantity = new_qty;
               
            }
           
           
            // if (gymitems[item_id].row.unit != gymitems[item_id].row.base_unit) {
            //     $.each(gymitems[item_id].units, function() {
            //         if (this.id == gymitems[item_id].row.unit) {
            //             gymitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
            //         }
            //     });
            // }
            var wh = $("#poswarehouse").val();
            var status = 0;

       

            // gymitems[item_id].row.period = new_qty;
            localStorage.setItem('gymitems', JSON.stringify(gymitems));
            // console.log(gymitems);
            loadItems();
        });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document)
        .on('focus', '.rprice', function() {
            old_price = $(this).val();
        })
        .on('change', '.rprice', function() {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_price);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            gymitems[item_id].row.price = new_price;
            localStorage.setItem('gymitems', JSON.stringify(gymitems));
            loadItems();
        });

    $(document).on('click', '#removeReadonly', function() {
        $('#gymcustomer').select2('readonly', false);
        //$('#gymwarehouse').select2('readonly', false);
        return false;
    });
});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#gymcustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + 'customers/suggestions',
            dataType: 'json',
            quietMillis: 15,
            data: function(term, page) {
                return {
                    term: term,
                    limit: 10,
                };
            },
            results: function(data, page) {
                if (data.results != null) {
                    return { results: data.results };
                } else {
                    return { results: [{ id: '', text: 'No Match Found' }] };
                }
            },
        },
    });
}
//localStorage.clear();
function loadItems() {
    if (localStorage.getItem('gymitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        var add_col = 0;

        $('#gymTable tbody').empty();
        gymitems = JSON.parse(localStorage.getItem('gymitems'));
        console.log(gymitems)
        var whOverselling = JSON.parse(localStorage.getItem('gymoverselling'));
        sortedItems =
            site.settings.item_addition == 1 ?
            _.sortBy(gymitems, function(o) {
                return [parseInt(o.order)];
            }) :
            gymitems;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function() {
            var arr             = JSON.parse(localStorage.getItem('group_price'));
            var item            = this;
            var item_id         = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order          = item.order ? item.order : new Date().getTime(); 
            var itemQ=0;
            if(item.row.quantity==null){
                itemQ = item.quantity;
            }else{
                itemQ = item.row.quantity;
            }
            var product_id      = item.row.id,
                item_type       = item.row.type,
                combo_items     = item.combo_items,
                item_price      = item.row.price,
                item_qty        = itemQ,
                item_aqty       = itemQ,
                item_tax_method = item.row.tax_method,
                item_ds         = item.row.discount,
                item_discount   = 0,
                item_expiry     = item.expiry,
                item_option     = item.row.option,
                option_name     = item.row.option_name ? item.row.option_name : '',
                start_time     = item.row.start_time ? item.row.start_time : '',
                end_time        = item.row.end_time ? item.row.end_time : '',
                item_code       = item.row.code,
                item_period= item.row.period,
                item_period_type= item.row.period_type,
                item_serial     = item.row.serial_no,
                item_max_serial = item.row.max_serial ? item.row.max_serial : '',
                item_detail     = item.row.details ? item.row.details : '',
                item_warranty   = item.row.warranty ? item.row.warranty : '',
                item_name       = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;'),
                item_weight     = item.row.weight ? item.row.weight : 0.0000;
            var product_unit    = item.row.unit,
                base_quantity   = item.row.base_quantity;
            var unit_price      = item.row.price;   
            var addition_type   = item.row.addition_type ? item.row.addition_type : '';
           



            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf('%') !== -1) {
                    var pds = ds.split('%');
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal((unit_price * parseFloat(pds[0])) / 100, 4);
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                } else {
                    item_discount = formatDecimal(ds);
            }
          
            product_discount += formatDecimal(item_discount * item_qty, 4);
            unit_price = formatDecimal(unit_price - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            if (site.settings.tax1 == 1 && 0) {
                if (pr_tax !== false && pr_tax != 0) {
                    if (pr_tax.type == 1) {
                            if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimal(unit_price - pr_tax_val, 4) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price + item_discount, 4);
            var product_unit_code = '',
                f_type = item.type_id ? item.type_id : '';
            var f_option = '',
                f_sel = '';
            if(item.units){
                $.each(item.units, function(index, val_item) {
                    if (product_unit == val_item.id) {
                        product_unit_code = val_item.name;
                    }
                });
            }else{
                product_unit_code = item.row.unit_code;
            }    
            // $.each(item.units, function(index, val_item) {
            //     if (product_unit == val_item.id) {
            //         product_unit_code = val_item.name;
            //     }
            // });
            // $.each(item.fiber.type, function(index, fItem){
            //     f_option += '<option onClick=setQtyItem("'+item_id+'",'+fItem.qty+','+fItem.id+') value="'+fItem.id+'" '+((f_type==fItem.id)?"selected":"")+'>'+fItem.name+'</option>';
            // });
            var sel_opt = '';
            if (item.name == 'Fiber') {
                f_sel = '<select class="form-control" name="addition_type[]"><option onClick="setQtyItem()" value="">Select Type</option>' +
                    f_option +
                    '</select>';
            }
            var row_no = item.id;
            var newTr = $('<tr style ="background-color:#FFFFFF" id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            if (item.row.code == 'sub_qty') {
                tr_html =
                    '<td colspan="9" class="text-center">' +
                    '<input name="product_code[]" type="hidden" value="sub_qty">Sub Quantity</td>' +
                    '<input name="product_id[]" type="hidden" class="rid" value="">' +
                    '<input name="product_name[]" type="hidden" class="rid" value="">' +
                    '<input name="product_type[]" type="hidden" class="rid" value="">' +
                    '<input name="product_option[]" type="hidden" class="rid" value="">' +
                    '<input name="product_comment[]" type="hidden" class="rid" value="">' +
                    '<input name="serial[]" type="hidden" class="rid" value="">' +
                    '<input name="max_serial[]" type="hidden" class="rid" value="">' +
                    '<input name="product_expiry[]" type="hidden" class="rid" value="">' +
                    '<input name="product_discount[]" type="hidden" class="rid" value="">' +
                    '<input name="product_tax[]" type="hidden" class="rid" value="">' +
                    '<input name="net_price[]" type="hidden" class="rid" value="">' +
                    '<input name="unit_price[]" type="hidden" class="rid" value="">' +
                    '<input name="real_unit_price[]" type="hidden" class="rid" value="">' +
                    '<input name="quantity[]" type="hidden" class="rid" value="">' +
                    '<input name="product_unit[]" type="hidden" class="rid" value="">' +
                    '<input name="product_detail[]" type="hidden" class="rid" value="">' +
                    '<input name="product_base_quantity[]" type="hidden" class="rid" value="">';
                newTr.html(tr_html);
                newTr.prependTo('#gymTable');
            } else {
                tr_html =
                    '<td ><input name="product_id[]" type="hidden" class="rid" value="' +
                    product_id +
                    '"><input name="product_type[]" type="hidden" class="rtype" value="' +
                    product_id +
                    '"><input name="product_code[]" type="hidden" class="rcode" value="' +
                    item_code +
                    '"><input name="product_name[]" type="hidden" class="rname" value="' +
                    item_name +
                    '"><input name="product_option[]" type="hidden" class="roption" value="' +
                    item_option +
                    '"><input name="product_period_type[]" type="hidden" class="rperiod_type" value="' +
                    item_period_type +
                    '"><input name="product_period[]" type="hidden" class="rperiod" value="' +
                    item_period+
                    '"><input name="product_expiry[]" type="hidden" class="rexpiry" value="' +
                    item_expiry +
                    '"><input name="product_detail[]" type="hidden" class="rdetail" value="' +
                    item_detail +
                    '"><span class="sname" id="name_' +
                    row_no +
                    '">' +
                    item_name + (item_expiry != null ? ' (' + item_expiry + ')' : '') + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + '</span><i class="hide pull-right fa fa-edit tip pointer edit" id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" title="Edit" style="cursor:pointer;' + ((item.free) ? "display:none;" : "") + '"></i> ' + (item_detail != '' ? '[' + item_detail + ']' : "") + '</td>';
                if (item.name == 'Fiber') {
                    tr_html += '<td class="text-center">' + f_sel + '</td>';
                }
                if (site.settings.product_serial == 1) {
                    tr_html +=
                        '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' +
                        row_no +
                        '" value="' +
                        item_serial +
                        '"></td>';
                }
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rprice" name="price[]" type="hidden" id="price_' +
                    row_no +
                    '" value="' +
                    item_price +
                    '"><input class="ruprice" name="unit_price[]" type="hidden" value="' +
                    unit_price +
                    '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' +
                    item.row.price +
                    '"><span class="text-right sprice" id="sprice_' +
                    row_no +
                    '">' +
                    formatMoney(item_price) +
                    '</span></td>';
                tr_html +=
                    '<td><input class="form-control text-center rquantity" ' + ((item.free) ? "disabled" : "") + ' tabindex="' +
                    (site.settings.set_focus == 1 ? an : an + 1) +
                    '" name="quantity[]" type="text" value="' +
                    formatDecimal(item_qty) +
            
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="quantity_' +
                    row_no +
                    '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' +
                    product_unit +
                    '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' +
                    base_quantity +
                    '"><input name="product_weight[]" type="hidden" class="rproduct_weight" value="' +
                    item_weight +
                    '"></td>';
                tr_html +=
                    '<td class="text-right"><input name="start_time[]" value="' + start_time + '"  class="form-control start_time datetime" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="start_time_' +
                    row_no +
                    '" onClick="this.select();"></td>';
                tr_html +=
                    '<td class="text-right"><input name="end_time[]" value="' + end_time + '" class="form-control end_time datetime" readonly data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="end_time_' +
                    row_no +
                    '" onClick="this.select();"></td>';
                // if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                //     tr_html +=
                //         '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' +
                //         row_no +
                //         '" value="' +
                //         item_ds +
                //         '"><span class="text-right sdiscount text-danger" id="sdiscount_' +
                //         row_no +
                //         '">' +
                //         formatMoney(0 - item_discount * item_qty) +
                //         '</span></td>';
                // }
                if (site.settings.tax1 == 1 && 0) {
                    tr_html +=
                        '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                        row_no +
                        '" value="' +
                        pr_tax.id +
                        '"><span class="text-right sproduct_tax" id="sproduct_tax_' +
                        row_no +
                        '">' +
                        (parseFloat(pr_tax_rate) != 0 ? '(' + formatDecimal(pr_tax_rate) + ')' : '') +
                        ' ' +
                        formatMoney(pr_tax_val * item_qty) +
                        '</span></td>';
                }

                tr_html +=
                    '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
                    row_no +
                    '">' +
                    formatMoney((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) +
                    '</span></td>';
                tr_html +=
                    '<td class="text-center"><i class="fa fa-times tip pointer gymdel" id="' +
                    row_no +
                    '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo('#gymTable');
                total += formatDecimal((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty), 4);
                count += parseFloat(item_qty);
                an++;
            }
        });
        var col = ((site.settings.product_serial == 1) ? 3 : 2) + add_col;
        if (site.settings.product_serial == 1) {
            col++;
        }
        var tfoot = '<tr id="tfoot" class="tfoot active ">';
        if (site.settings.warranty == 1) {
            tfoot += '<th class="text-right"></th>';
        }

        tfoot += '<th colspan="' +
            col +
            '">Total</th><th class="text-center">' +
            formatQty(parseFloat(count) - 1) +
            '</th>';
        tfoot += '<th colspan="2" class="text-right"></th>';
        // if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
        //     tfoot += '<th class="text-right">' + formatMoney(product_discount) + '</th>';
        // }
        if (site.settings.tax1 == 1 && 0) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot +=
            '<th class="text-right">' +
            formatMoney(total) +
            '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#gymTable tfoot').html(tfoot);
        // Order level discount calculations
        if ((gymdiscount = localStorage.getItem('gymdiscount'))) {
            var ds = gymdiscount;
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((total * parseFloat(pds[0])) / 100, 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
            //total_discount += parseFloat(order_discount);
        }
        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if ((gymtax2 = localStorage.getItem('gymtax2'))) {
                $.each(tax_rates, function() {
                    if (this.id == gymtax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal(((total - order_discount) * this.rate) / 100, 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(total + invoice_tax - order_discount + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text(an - 1 + ' (' + formatQty(parseFloat(count) - 1) + ')');
        $('#total_items').val(parseFloat(count) - 1);
        //$('#tds').text('('+formatMoney(product_discount)+'+'+formatMoney(order_discount)+')'+formatMoney(total_discount));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $('html, body').animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if (count > 1) {
            $('#gymcustomer').select2('readonly', true);
            $('#gymwarehouse').select2('readonly', true);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_invoice_item(item) {

    if (count == 1) {
        gymitems = {};
        if ($('#gymcustomer').val()) {
            $('#gymcustomer').select2('readonly', true);
            $('#gymwarehouse').select2('readonly', true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) return; 
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;

    if (gymitems[item_id]) {
        var new_qty = parseFloat(gymitems[item_id].quantity) + 1;
        // gymitems[item_id].row.base_quantity = new_qty;
        // if (gymitems[item_id].row.unit != gymitems[item_id].row.base_unit) {
        //     $.each(gymitems[item_id].units, function() {
        //         if (this.id == gymitems[item_id].row.unit) {
        //             gymitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
        //         }
        //     });
        // }
        gymitems[item_id].quantity  = new_qty;
    } else {
        gymitems[item_id] = item;
    }
    gymitems[item_id].order = new Date().getTime();
   
    localStorage.setItem('gymitems', JSON.stringify(gymitems));

    loadItems();
    return true;
}

if (typeof Storage === 'undefined') {
    $(window).bind('beforeunload', function(e) {
        if (count > 1) {
            var message = 'You will loss data!';
            return message;
        }
    });
}

function setQtyItem(item_id, qty, id) {
    var overselling = localStorage.getItem('gymoverselling');
    if (!is_numeric(qty) || qty <= 0) {
        if (site.settings.overselling != 1 || (site.settings.overselling == 1 && overselling != 1)) {
            $('#add_sale, #edit_sale').attr('disabled', true);
            bootbox.alert('This type has not enough quantity !');
            return;
        }
    }

    gymitems[item_id].qty = qty;
    gymitems[item_id].type_id = id;
    localStorage.setItem('gymitems', JSON.stringify(gymitems));
   
    loadItems();
}

function setOrderDiscountByCustomerGroup(customer_id){
    if (quote == null || (quote != null && (quote.order_discount_id == null || quote.order_discount_id == ''))) {
        if (customer_id != '' && customer_id != null) {
            $.ajax({
                type: "get",
                url: site.base_url + "sales/getCustomerGroupByCustomerID_ajax/" + customer_id,
                success: function(dataResult){
                    if(dataResult && dataResult.percent != 0){
                        var order_discount = (-1 * dataResult.percent + '%');
                        localStorage.setItem('gymdiscount', order_discount);
                        $('#gymdiscount').val(order_discount);
                    } else {
                        localStorage.setItem('gymdiscount', '');
                        $('#gymdiscount').val('');
                    }
                    loadItems();
                },
                error: function(jqXHR, textStatus, errorThrown){
                    // console.log("Error!: " + textStatus);
                },
                complete: function(xhr, statusText){
                    // console.log(xhr.status + " " + statusText);
                }
            });
        }
    }
}