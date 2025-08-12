$(document).ready(function(e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var customer = $('#slcustomer');
    customer.change(function(e) {
        localStorage.setItem('slcustomer', $(this).val());
        if (site.settings.customer_group_discount == 1) {
            setOrderDiscountByCustomerGroup($(this).val());
        }
        //$('#slcustomer_id').val($(this).val());
    });
    var saleman_by = $('#slsaleman_by');
        saleman_by.change(function(e) {
        localStorage.setItem('slsaleman_by', $(this).val());
    });
    if ((slcustomer = localStorage.getItem('slcustomer'))) {
        customer.val(slcustomer).select2({
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
        if ($('#slcustomer').val() != '' && $('#slcustomer').val() != null) {
            // setOrderDiscountByCustomerGroup($('#slcustomer').val());
        }
    }
    if ((sldiscount = localStorage.getItem('sldiscount'))) {
        $('#sldiscount').val(sldiscount);
    }
    $('#sltax2').change(function(e) {
        localStorage.setItem('sltax2', $(this).val());
        $('#sltax2').val($(this).val());
    });
    if ((sltax2 = localStorage.getItem('sltax2'))) {
        $('#sltax2').select2('val', sltax2);
    }
    $('#slsale_status').change(function(e) {
        localStorage.setItem('slsale_status', $(this).val());
    });
    if ((slsale_status = localStorage.getItem('slsale_status'))) {
        $('#slsale_status').select2('val', slsale_status);
    }
    $('#slpayment_status').change(function(e) {
        var ps = $(this).val();
        localStorage.setItem('slpayment_status', ps);
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
    if ((slpayment_status = localStorage.getItem('slpayment_status'))) {
        $('#slpayment_status').select2('val', slpayment_status);
        var ps = slpayment_status;
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
    $('#slpayment_term')
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
                localStorage.setItem('slpayment_term', new_payment_term);
                $('#slpayment_term').val(new_payment_term);
            }
        });
    if ((slpayment_term = localStorage.getItem('slpayment_term'))) {
        $('#slpayment_term').val(slpayment_term);
    }

    var old_shipping;
    $('#slshipping')
        .focus(function() {
            old_shipping = $(this).val();
        })
        .change(function() {
            var slsh = $(this).val() ? $(this).val() : 0;
            if (!is_numeric(slsh)) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            shipping = parseFloat(slsh);
            localStorage.setItem('slshipping', shipping);
            var gtotal = total + invoice_tax - order_discount + shipping;
            $('#gtotal').text(formatMoney(gtotal));
            $('#tship').text(formatMoney(shipping));
        });
    if ((slshipping = localStorage.getItem('slshipping'))) {
        shipping = parseFloat(slshipping);
        $('#slshipping').val(shipping);
    } else {
        shipping = 0;
    }
    $('#add_sale, #edit_sale').attr('disabled', true);
    $(document).on('change', '.rserial', function() {
        var item_id = $(this)
            .closest('tr')
            .attr('data-item-id');
        slitems[item_id].row.serial = $(this).val();
        localStorage.setItem('slitems', JSON.stringify(slitems));
    });  
    // If there is any item in localStorage
    if (localStorage.getItem('slitems')) {
        loadItems();
    } 
    // clear localStorage and reload
    $('#reset').click(function(e) {
        bootbox.confirm(lang.r_u_sure, function(result) {
            if (result) {
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('slitems');
                }
                if (localStorage.getItem('sldiscount')) {
                    localStorage.removeItem('sldiscount');
                }
                if (localStorage.getItem('sltax2')) {
                    localStorage.removeItem('sltax2');
                }
                if (localStorage.getItem('slshipping')) {
                    localStorage.removeItem('slshipping');
                }
                if (localStorage.getItem('slref')) {
                    localStorage.removeItem('slref');
                }
                if (localStorage.getItem('slwarehouse')) {
                    localStorage.removeItem('slwarehouse');
                }
                if (localStorage.getItem('sloverselling')) {
                    localStorage.removeItem('sloverselling');
                }
                if (localStorage.getItem('slnote')) {
                    localStorage.removeItem('slnote');
                }
                if (localStorage.getItem('slinnote')) {
                    localStorage.removeItem('slinnote');
                }
                if (localStorage.getItem('slcustomer')) {
                    localStorage.removeItem('slcustomer');
                }
                if (localStorage.getItem('slcurrency')) {
                    localStorage.removeItem('slcurrency');
                }
                if (localStorage.getItem('sldate')) {
                    localStorage.removeItem('sldate');
                }
                if (localStorage.getItem('slstatus')) {
                    localStorage.removeItem('slstatus');
                }
                if (localStorage.getItem('slbiller')) {
                    localStorage.removeItem('slbiller');
                }
                if (localStorage.getItem('gift_card_no')) {
                    localStorage.removeItem('gift_card_no');
                }
                if (localStorage.getItem('slsaleman_by')) {
                    localStorage.removeItem('slsaleman_by');
                }
                if (localStorage.getItem('slshipping_request')) {
                    localStorage.removeItem('slshipping_request');
                }
                if (localStorage.getItem('slshipping_request_phone')) {
                    localStorage.removeItem('slshipping_request_phone');
                }
                if (localStorage.getItem('slshipping_request_address')) {
                    localStorage.removeItem('slshipping_request_address');
                }
                if (localStorage.getItem('slshipping_request_note')) {
                    localStorage.removeItem('slshipping_request_note');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    }); 
    // save and load the fields in and/or from localStorage 
    $('#slref').change(function(e) {
        localStorage.setItem('slref', $(this).val());
    });
    if ((slref = localStorage.getItem('slref'))) {
        $('#slref').val(slref);
    }  
    if ($('#slwarehouse').val()) {
        var wh_id = $('#slwarehouse').val();
        $.ajax({
            url: site.base_url + 'sales/getWarehouseByID_Ajax',
            type: 'GET',
            dataType: 'Json',
            data: {'warehouse_id':wh_id},
            success: function(data) {
                if(data != null) {
                    localStorage.setItem('sloverselling', data.overselling);
                }
            }
        });
    } 
    $('#slwarehouse').change(function(e) {
        localStorage.setItem('slwarehouse', $(this).val());
        $.ajax({
            url: site.base_url + 'sales/getWarehouseByID_Ajax',
            type: 'GET',
            dataType: 'Json',
            data: {'warehouse_id': $(this).val()},
            success: function(data) {
                if(data != null) {
                    localStorage.setItem('sloverselling', data.overselling);
                }
            }
        });
    });
    if ((slwarehouse = localStorage.getItem('slwarehouse'))) {
        $('#slwarehouse').select2('val', slwarehouse);  
    } 
    $('#slnote').redactor('destroy');
    $('#slnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html',],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        },
    });
    if ((slnote = localStorage.getItem('slnote'))) {
        $('#slnote').redactor('set', slnote);
    }
    $('#slinnote').redactor('destroy');
    $('#slinnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html',],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('slinnote', v);
        },
    });
    if ((slinnote = localStorage.getItem('slinnote'))) {
        $('#slinnote').redactor('set', slinnote);
    }
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
        $('#sltax2').change(function() {
            localStorage.setItem('sltax2', $(this).val());
            loadItems();
            return;
        });
    }
    // Order discount calculation
    var old_sldiscount;
    $('#sldiscount')
        .focus(function() {
            old_sldiscount = $(this).val();
        })
        .change(function() {
            var new_discount = $(this).val() ? $(this).val() : '0';
            if (is_valid_discount(new_discount)) {
                localStorage.removeItem('sldiscount');
                localStorage.setItem('sldiscount', new_discount);
                loadItems();
                return;
            } else {
                $(this).val(old_sldiscount);
                bootbox.alert(lang.unexpected_value);
                return;
        }
    });
    /* ----------------------
     * Delete Row Method
     * ---------------------- */
    $(document).on('click', '.sldel', function() {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete slitems[item_id];
        row.remove();
        if (slitems.hasOwnProperty(item_id)) {} else {
            localStorage.setItem('slitems', JSON.stringify(slitems));
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
        item = slitems[item_id];
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
        if (site.settings.tax1) {
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
                            if (slitems[item_id].row.tax_method == 0) {
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
            $('#pserial').val(row.children().children('.rserial').val());
            
            if(item.product_serials!=''){
                uopt1 = $("<select id=\"product_serial\" name=\"product_serial\" class=\"form-control select\"/>");
                var serialno = row.children().children('.rserial').val();
                var myarray = serialno.split("#");
                $.each(item.product_serials, function () {
                    if(jQuery.inArray(this.serial, myarray) !== -1){
                        $("<option />", { value: this.id, text: this.serial, selected:true}).appendTo(uopt1);
                    }else{
                        $("<option />", { value: this.id, text: this.serial}).appendTo(uopt1);
                    }
                });
            }else{
                uopt1 = '<p style="margin: 12px 0 0 0;">n/a</p>';
            }
            $("#pserials-div").html(uopt1);
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
        if (site.settings.product_option) {
            var popt_com = '<p style="margin: 12px 0 0 0;">n/a</p>';
            if (item.row.product_option_comment) {
                
                popt_com = $('<select id="p_comment_option" name="p_comment_option" class="form-control select" />');
                $.each(item.row.product_option_comment, function() {
                    if (this.id == item.row.comment_option) {
                      $('<option />', { value: this.id, text: this.name, selected: true }).appendTo(popt_com);
                    } else {
                        $('<option />', { value: this.id, text: this.name }).appendTo(popt_com);
                   }
                });
            }
            $('#poptions-div_1').html(popt_com);
        }
        $('#saleman_item').select2('val', item.row.saleman_item);
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#p_comment_option').select2('val', item.row.comment_option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').select2('val', discount);
        $('#pdiscount').val(discount);
        $('#padiscount').val('');
        $('#psubt').val(row.find('.ssubtotal').text());
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo('body').modal('show');
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
        var item        = slitems[item_id];
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
        var item = slitems[item_id];
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
            if (item.units && unit != slitems[item_id].row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == unit) {
                        base_quantity = unitToBaseQty($('#pquantity').val(), this);
                        $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4)).change();
                    }
                });
            } else {
                $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
            }
        }
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
        if (unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function() {
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
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if (!is_valid_discount($('#pdiscount').val()) || ($('#pdiscount').val() != 0 && $('#pdiscount').val() > price)) {
                bootbox.alert(lang.unexpected_value);
                $("#pdiscount").val($('#pdiscount').attr('data'));
                return false;
            } else {
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
        if (site.settings.product_option !== 0) {
            var option_id = $('#popt').val() ? $('#popt').val() : '';
            if (option_id != '') {
                $.ajax({
                    url: site.base_url + 'sales/option_number',
                    type: 'GET',
                    dataType: 'Json',
                    data: {'option_id':option_id},
                    success: function(data) {
                        slitems[item_id].row.serial_no = data.options.last_no ? data.options.last_no : 0;
                        slitems[item_id].row.max_serial = data.options.last_no;
                        slitems[item_id].row.option_name = data.options.name;
                        localStorage.setItem('slitems', JSON.stringify(slitems));
                        loadItems();
                    }
                });
            }
        }
        $('.rserial').prop('readonly', true);
        if($('#poption').val()){
            var poption = $('#poption').val();
        } else {
            var poption = $('#popt').val() ? $('#popt').val() : '';
        }

        if($('#p_comment_option').val()){
            var poption_comment = $('#p_comment_option').val();
        } else {
            var poption_comment = '';
        }
        slitems[item_id].row.fup = 1;
        slitems[item_id].row.qty = quantity;
        slitems[item_id].row.base_quantity = parseFloat(base_quantity);
        slitems[item_id].row.real_unit_price = price;
        slitems[item_id].row.unit = unit;
        slitems[item_id].row.tax_rate = new_pr_tax;
        slitems[item_id].tax_rate = new_pr_tax_rate;
        slitems[item_id].row.discount = discount;
        slitems[item_id].row.option = poption; 
        slitems[item_id].row.comment_option = poption_comment; 
        slitems[item_id].row.serial_no = $('#product_serial').val();
        slitems[item_id].row.details = $('#pdescription').val();
        slitems[item_id].row.saleman_item = $('#saleman_item').val();
        localStorage.setItem('slitems', JSON.stringify(slitems));
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
            var item = slitems[item_id];
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
            if (site.settings.tax1 == 1) {
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
        var item = slitems[item_id];
        var unit = $('#punit').val(),
            base_quantity = parseFloat($('#pquantity').val()),
            base_unit_price = item.row.base_unit_price;
        if (unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function() {
                if (this.id == unit) {
                    base_unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        if (item.options !== false) {
            $.each(item.options, function() {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(base_unit_price) + parseFloat(this.price)).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
     * Sell Gift Card modal
     ------------------------------- */
    $(document).on('click', '#sellGiftCard', function(e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2('readonly', true);
                $('#slwarehouse').select2('readonly', true);
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
        //if (typeof slitems === "undefined") {
        //    var slitems = {};
        //}
        $.ajax({
            type: 'get',
            url: site.base_url + 'sales/sell_gift_card',
            dataType: 'json',
            data: { gcdata: gc_data },
            success: function(data) {
                if (data.result === 'success') {
                    slitems[mid] = {
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
                    localStorage.setItem('slitems', JSON.stringify(slitems));
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
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2('readonly', true);
                $('#slwarehouse').select2('readonly', true);
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
            slitems[mid] = {
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
            localStorage.setItem('slitems', JSON.stringify(slitems));
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
            } else {
                var item_id = $(this).closest('tr').attr('data-item-id');
                slitems[item_id].row.max_serial = macserial;
                slitems[item_id].row.qty = macserial - serial;
                slitems[item_id].row.base_quantity = macserial - serial;
                localStorage.setItem('slitems', JSON.stringify(slitems));
                loadItems();
            }
        });
    }
    /* --------------------------
     * Edit Row Quantity Method
    --------------------------- */
    var old_row_qty;
    $(document).on('focus', '.rquantity', function() {
        old_row_qty = $(this).val();
    }).on('change', '.rquantity', function() {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            slitems[item_id].row.base_quantity = new_qty;
            if (slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
                $.each(slitems[item_id].units, function() {
                    if (this.id == slitems[item_id].row.unit) {
                        slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            var wh = $("#poswarehouse").val();
            var status = 0;
            $.each(slitems, function() {
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                slitems[item_id] = item;
                if (item.row.code != "Time") {
                    $.ajax({
                        type: "get",
                        url: site.base_url + "pos/getProductToBuy",
                        data: { product_id: slitems[item_id].row.id, warehouse_id: wh, qty: new_qty, positems: item.row },
                        dataType: "json",
                        success: function(data) {
                            for (var i = 0; i < data.length; i++) {
                                if (data[i].qty != 1) {
                                    status = 1;
                                }
                            }
                            $("#add_item").removeClass('ui-autocomplete-loading');
                        }
                    }).done(function() {
                        $('#modal-loading').hide();
                    });
                }
            });
            $.ajax({
                type: "get",
                url: site.base_url + "pos/getProductPromo",
                data: { product_id: slitems[item_id].row.id, warehouse_id: wh, qty: new_qty },
                dataType: "json",
                success: function(data) {
                    if (data) {
                        for (var i = 0; i < data.length; i++) {
                            data.free = true;
                            data.parent = slitems[item_id].row.id;
                            delete slitems[item_id].row.id;
                            if (status == 1) {
                                add_invoice_item(data[i]);
                            }
                        }
                    }
                    $("#add_item").removeClass('ui-autocomplete-loading');
                }
            }).done(function() {
                $('#modal-loading').hide();
            });
            slitems[item_id].row.qty = new_qty;
            localStorage.setItem('slitems', JSON.stringify(slitems));
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
            slitems[item_id].row.price = new_price;
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
        });

    $(document).on('click', '#removeReadonly', function() {
        $('#slcustomer').select2('readonly', false);
        //$('#slwarehouse').select2('readonly', false);
        return false;
    });

    $(document).on('click', '.combo', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = slitems[item_id];
        $('#row_id').val(row_id);
        var td_combo = '';
        if (item.combo_items) {
            $.each(item.combo_items, function() {
                td_combo += '<tr>'
                    td_combo += '<td><input value="'+this.id+'" type="hidden" class="combo_product_id"/><input type="hidden" class="combo_code" value="'+this.code+'"/><input type="hidden" class="combo_name" value="'+this.name+'"/><input value="'+this.name+' ('+this.code+')" class="form-control tip combo_product" type="text"/></td>';
                    if (site.settings.qty_operation == 1) {
                        td_combo += '<td class="text-center"><input value="'+formatDecimal(this.width)+'" class="form-control text-right combo_width" type="text"/></td>';
                        td_combo += '<td class="text-center"><input value="'+formatDecimal(this.height)+'" class="form-control text-right combo_height" type="text"/></td>';
                    }
                    td_combo += '<td class="text-center"><input value="'+formatDecimal(this.qty)+'" class="form-control text-right combo_qty" type="text"/></td>';
                    td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text" value="'+formatDecimal(this.price)+'"/></td>';
                    td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
                td_combo += '/tr>';
            });
        }
        $('#comboProduct tbody').html(td_combo);
        $('#comboModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#comboModal').appendTo("body").modal('show');
    });
    $(document).on('click', '#add_comboProduct', function () {
        var td_combo = '<tr>';
            td_combo += '<td><input type="hidden" class="combo_product_id"/><input type="hidden" class="combo_name"/><input type="hidden" class="combo_code" /><input class="form-control tip combo_product" type="text"/></td>';
            if (site.settings.qty_operation == 1) {
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_width" type="text"/></td>';
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_height" type="text"/></td>';
            }
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text"/></td>';
            td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
            td_combo += '</tr>';
        $('#comboProduct tbody').append(td_combo);  
    });
    $(document).on('click', '.delete_combo_product', function () {
        var parent = $(this).parent().parent();
        parent.remove();
        return false;
    });
    $(document).on('click', '#editCombo', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var combo_items = [];
        var unit_price = 0;
        $('.combo_product_id').each(function(){
            var parent = $(this).parent().parent();
            var product_id = $(this).val();
            var product_name = parent.find('.combo_name').val();
            var product_code = parent.find('.combo_code').val();
            var product_price = parent.find('.combo_price').val() - 0;
            var product_qty = parent.find('.combo_qty').val() - 0;
            var product_width = parent.find('.combo_width').val() - 0;
            var product_height = parent.find('.combo_height').val() - 0;
            if (product_id > 0) {
                var combo_product = { 
                        id:product_id,  
                        name: product_name, 
                        code : product_code,
                        price : product_price,
                        qty : product_qty,
                        width : product_width,
                        height : product_height,
                        };
                combo_items.push(combo_product);
                unit_price += (product_price * product_qty);
            }
        });
        slitems[item_id].row.real_unit_price = unit_price;
        slitems[item_id].combo_items = combo_items;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#comboModal').modal('hide');
        loadItems();
        return;
    });
    var old_value;
    $(document).on("focus", '.combo_qty, .combo_price', function () {
        old_value = $(this).val();
    }).on("change", '.combo_qty, .combo_price', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_value);
            bootbox.alert(lang.unexpected_value);
            return;
        }
    });
    var old_combo_w_h;
    $(document).on("focus", '.combo_width, .combo_height', function () {
        old_combo_w_h = $(this).val();
    }).on("change", '.combo_width, .combo_height', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_combo_w_h);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var parent = $(this).parent().parent();
        var combo_width = parent.find('.combo_width').val() - 0;
        var combo_height = parent.find('.combo_height').val() - 0;
        var combo_square = combo_width * combo_height;
        parent.find('.combo_qty').val(combo_square);
    });
});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#slcustomer').select2({
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


//  var oldser;
//     $(document).on("focus", '.item_barcode', function () {
//         old_value = $(this).val();
//     }).on("change", '.item_barcode', function () {
//         var row = $(this).closest('tr');
//         var item_id = row.attr('data-item-id');
//         var new_ser = $(this).val();
//         slitems[item_id].row.item_barcode = new_ser;
//         localStorage.setItem('slitems', JSON.stringify(slitems));
//         loadItems();
//         return;
//     });


//localStorage.clear();
function loadItems() {
    if (localStorage.getItem('slitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        var add_col = 0;
        $('#slTable tbody').empty();
        slitems = JSON.parse(localStorage.getItem('slitems'));
        var whOverselling = JSON.parse(localStorage.getItem('sloverselling'));
        sortedItems = site.settings.item_addition == 1 ? _.sortBy(slitems, function(o) { return [parseInt(o.order)]; }) : slitems;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function() {
            var arr             = JSON.parse(localStorage.getItem('group_price'));
            var item            = this;
            var item_id         = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order          = item.order ? item.order : new Date().getTime();
            var product_id      = item.row.id,
                item_type       = item.row.type,
                combo_items     = item.combo_items,
                item_price      = item.row.price,
                item_qty        = item.row.qty,
                item_aqty       = item.row.quantity,
                item_tax_method = item.row.tax_method,
                item_ds         = item.row.discount,
                item_discount   = 0,
                item_units      = item.units,
                item_expiry     = item.expiry,
                item_option     = item.row.option,
                option_name     = item.row.option_name?item.row.option_name:'',
                item_comment_option  = item.row.comment_option,

                item_code       = item.row.code,
                item_serial     = item.row.serial_no,
             
                item_max_serial = item.row.max_serial ? item.row.max_serial : '',
                item_detail     = item.row.details ? item.row.details : '',
                item_warranty   = item.row.warranty ? item.row.warranty : '',
                item_name       = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;'),
                item_weight     = item.row.weight ? item.row.weight : 0.0000;
            var product_unit    = item.row.unit,
                base_quantity   = item.row.base_quantity;
            var unit_price      = item.row.real_unit_price;
            var addition_type   = item.row.addition_type ? item.row.addition_type : '';
            var saleman_item    = item.row.saleman_item ? item.row.saleman_item : '';
            if ((arr != null) && (arr != undefined) && (arr != "")) {
                if (!(item.free) || (arr != null)) {
                    for (var i = 0; i < arr.length; i++) {
                        var obj = arr[i];
                        if (product_id === obj['product_id'] && product_unit === obj['unit_id']) {
                            if(product_unit != item.row.base_unit){
                                $.each(item_units, function(index, val_unit) {
                                    if (obj['unit_id'] == val_unit.id) {
                                        from_qty = unitToBaseQty(obj['qty_from'] , item_units[index]);
                                        To_qty = unitToBaseQty(obj['qty_to'] , item_units[index]);
                                    }
                                });
                            } else {
                                from_qty = obj['qty_from'];
                                To_qty   = obj['qty_to'];
                            }
                            if (base_quantity >= from_qty && base_quantity <= To_qty) {
                                unit_price      = obj['price'];
                                item_price      = obj['price'];
                                base_unit_price = obj['price'];
                            }
                        }
                    }
                }
            }
            if (item.units && item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function() {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        if (site.settings.select_price == 1) {
                            if (item.row.base_unit == item.row.sale_unit) {
                                if (is_editing == false) {
                                    unit_price = formatDecimal((parseFloat(item.row.base_unit_price)), 4);
                                } else {
                                    unit_price = formatDecimal((parseFloat(item.row.price)), 4);   
                                }
                            } else {
                                l = this;
                                $.each(item.set_price, function () {
                                    if (this.unit_id == l.id) {
                                        if (item.row.base_unit_price == this.price) {
                                            unit_price = formatDecimal((parseFloat(this.price)), 4);
                                        } else { 
                                            unit_price = formatDecimal((parseFloat(item.row.price)), 4);
                                        }
                                    }
                                });
                            }
                        } else {
                            $.each(item.units, function() {
                                if (this.id == product_unit) {
                                    unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                                }
                            });
                        }
                    }
                });
            }
            var sel_opt = '';
            if (item.options !== false) {
                $.each(item.options, function() {
                    if (this.id == item_option) {
                        sel_opt = this.name;
                        if (this.price != 0 && this.price != '' && this.price != null) {
                            item_price = parseFloat(unit_price) + parseFloat(this.price);
                            unit_price = item_price;
                        }
                    }
                });
            }
     
            var sel_opt_comment = '';
            // if (item.row.product_option_comment !== false) {
            //     $.each(item.row.product_option_comment, function() {
            //         if (this.id == item_comment_option) {
            //             sel_opt_comment = this.name;
            //         }
            //     });
            // }


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
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0) {
                    if (pr_tax.type == 1) {
                            if (item_tax_method == '0') {
                            pr_tax_val  = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val  = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val  = parseFloat(pr_tax.rate);
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
            if (item.units) {
                $.each(item.units, function(index, val_item) {
                    if (product_unit == val_item.id) {
                        product_unit_code = val_item.name;
                    }
                });
            } else {
                product_unit_code = item.row.unit_code;
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
                newTr.prependTo('#slTable');
            } else {
                var button_combo = '';
                var product_combo = '<input type="hidden" name="product_combo[]"/>';
                if (item_type == 'combo' && combo_items) {
                    button_combo = '<i class="pull-right fa-regular fa-rectangle-list tip pointer combo" id="' + row_no + '" data-item="' + item_id + '" title="Combo" style="cursor:pointer;margin-right:5px;"></i>'; 
                    product_combo = "<input type='hidden' name='product_combo[]' value='"+JSON.stringify(combo_items)+"'/>";
                }
                tr_html =
                    '<td ><input name="product_id[]" type="hidden" class="rid" value="' +
                    product_id +
                    '"><input name="product_type[]" type="hidden" class="rtype" value="' +
                    item_type +
                    '"><input name="product_code[]" type="hidden" class="rcode" value="' +
                    item_code +
                    '"><input name="product_name[]" type="hidden" class="rname" value="' +
                    item_name +
                    '"><input name="product_option[]" type="hidden" class="roption" value="' +
                    item_option +
                    '"><input name="product_comment_option[]" type="hidden" class="roption_comment" value="' +
                    item_comment_option +
                    '"><input name="product_expiry[]" type="hidden" class="rexpiry" value="' +
                    item_expiry +
                    '"><input name="product_detail[]" type="hidden" class="rdetail" value="' +
                    item_detail +'"><span class="sname" id="name_' +row_no +'">' +item_code +
                    ' - ' + item_name + 
                    ((item_expiry   != null && site.settings.product_expiry == 1) ? ' (' + item_expiry + ')' : '') + 
                    (sel_opt != '' ? ' (' + sel_opt + ')' : '')+
                    (sel_opt_comment != '' ? ' (' + sel_opt_comment + ')' : '')+ '</span>'
                        + '<i class="pull-right fa fa-edit tip pointer edit" id="' +row_no +'" data-item="' +item_id +'" title="Edit" style="cursor:pointer;' + ((item.free) ? "display:none;" : "") + '"></i>'+ button_combo; 
                tr_html += (item_detail != '' ? '[' + item_detail + ']' : "") + '</td>';
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

                // tr_html +=
                //         '<td class="text-right"><input class="form-control input-sm item_barcode" name="item_barcode[]" type="text" id="item_barcode_' +
                //         row_no +
                //         '" value="' +
                //         item_barcode +
                //         '"></td>';
                // if (site.settings.product_serial == 1) {
                //     tr_html +=
                //         '<td class="text-right"><input class="form-control input-sm maxserial" name="max_serial[]" type="text" id="max_serial' +
                //         row_no +
                //         '" value="' +
                //         item_max_serial +
                //         '"></td>';
                // }
                if (site.settings.warranty == 1) {
                    tr_html +=
                        '< class="text-right"><input class="form-control input-sm warranty" placeholder="Number of Day" name="warranty[]" type="text" id="warranty' +
                        row_no +'" value="' +item_warranty +'"></td>';
                }
                if (site.settings.sale_man && site.settings.commission) {
                    tr_html +='<input class="saleman_item" name="saleman_item[]" type="hidden" value="'+saleman_item+'">';
                }
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' +
                    row_no +
                    '" value="' +
                    item_price +
                    '"><input class="ruprice" name="unit_price[]" type="hidden" value="' +
                    unit_price +
                    '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' +
                    item.row.real_unit_price +
                    '"><span class="text-right sprice" id="sprice_' +
                    row_no +
                    '">' +
                    formatMoney(item_price) +
                    '</span></td>';
                tr_html += '<td><span id="product_unit_code" class="text-right"> (' + product_unit_code + ') </span></td>';

                var qoh = item_aqty;     
                /*if (site.settings.product_expiry == 1) {
                    var expiry_select = '<select name="expired_data[]"  class="form-control select rexpired" style="width:100%;">';
                    var expiry_option = '';
                    $.each(item.product_expiries, function () {
                        if((this.quantity -0) > 0){
                            if(item.row.expired == this.expiry){
                                expiry_option += '<option selected value="'+this.expiry+'">'+this.expiry+'</option>';
                                qoh = formatDecimalRaw(this.quantity);
                            }else{
                                expiry_option += '<option value="'+this.expiry+'">'+this.expiry+'</option>';
                            }
                            
                        }
                    });
                    expiry_select += expiry_option;
                    expiry_select += '</select>';
                }*/
                if(site.settings.show_qoh == 1){
                    tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
                }
                tr_html +=
                    '<td>'+product_combo+'<input class="form-control text-center rquantity" ' + ((item.free) ? "disabled" : "") + ' tabindex="' +
                    (site.settings.set_focus == 1 ? an : an + 1) +
                    '" name="quantity[]" type="text" value="' +
                    formatQuantity2(item_qty) +
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
                if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                    tr_html +=
                        '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' +
                        row_no +
                        '" value="' +
                        item_ds +
                        '"><span class="text-right sdiscount text-danger" id="sdiscount_' +
                        row_no +
                        '">' +
                        formatMoney(0 - item_discount * item_qty) +
                        '</span></td>';
                }
                if (site.settings.tax1 == 1) {
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
                    '<td class="text-center"><i class="fa fa-times tip pointer sldel" id="' +
                    row_no +
                    '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo('#slTable');
                total += formatDecimal((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty), 4);
                count += parseFloat(item_qty);
                an++;
            }
            if (item_type == 'standard') {
                if (item.options !== false) {
                    $.each(item.options, function() {
                        if (this.id == item_option && base_quantity > this.quantity) {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1 || (site.settings.overselling == 1 && whOverselling != 1)) {
                                $('#add_sale, #edit_sale').attr('disabled', true);
                            }
                        }
                    });
                }
                var new_arr = {};
                if (slitems != null) {
                    $.each(slitems, function (index, obj) {
                        if (obj.row.id == product_id && obj.expiry == item_expiry) {
                            new_arr[obj.row.id + (obj.expiry != null ? ('_' + obj.expiry) : '')] = 
                            new_arr[obj.row.id + (obj.expiry != null ? ('_' + obj.expiry) : '')] === undefined ? 0 : new_arr[obj.row.id + (obj.expiry != null ? ('_' + obj.expiry) : '')];
                            new_arr[obj.row.id + (obj.expiry != null ? ('_' + obj.expiry) : '')] += parseInt(obj.row.base_quantity);
                        }
                    });
                }
                if (item.pitems) {
                    $.each(item.pitems, function () {
                        if (Object.keys(new_arr) == (this.product_id + (this.expiry != null ? ('_' + this.expiry) : ''))) {
                            if (parseInt(Object.values(new_arr)) > parseInt(this.quantity_balance)) {
                                $('#row_' + row_no).addClass('danger');
                                if (site.settings.overselling != 1 || (site.settings.overselling == 1 && whOverselling != 1)) {
                                    $('#add_sale, #edit_sale').attr('disabled', true);
                                }
                            }
                        }
                    });
                }
            } else if (item_type == 'combo') {
                if (combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                    if (site.settings.overselling != 1 || (site.settings.overselling == 1 && whOverselling != 1)) {
                        $('#add_sale, #edit_sale').attr('disabled', true);
                    }
                } else {
                    $.each(combo_items, function() {
                        if (parseFloat(this.quantity) < parseFloat(this.qty) * base_quantity && this.type == 'standard') {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1 || (site.settings.overselling == 1 && whOverselling != 1)) {
                                $('#add_sale, #edit_sale').attr('disabled', true);
                            }
                        }
                    });
                }
            }
        });
        var col = ((site.settings.product_serial == 1) ? 5 : 4) + add_col;
        if (site.settings.product_serial == 1) {
            col++;
        }
        var tfoot = '<tr id="tfoot" class="tfoot active">';
        if (site.settings.warranty == 1) {
            tfoot += '<th class="text-right"></th>';
        }
        tfoot += '<th colspan="' + col + '">Total</th><th class="text-center">' + formatQty(parseFloat(count) - 1) + '</th>';
        if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">' + formatMoney(product_discount) + '</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot += '<th class="text-right">' + formatMoney(total) + '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#slTable tfoot').html(tfoot);
        if ((sldiscount = localStorage.getItem('sldiscount'))) {
            var ds = sldiscount;
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
        }
        if (site.settings.tax2 != 0) {
            if ((sltax2 = localStorage.getItem('sltax2'))) {
                $.each(tax_rates, function() {
                    if (this.id == sltax2) {
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
            $('#slcustomer').select2('readonly', true);
            $('#slwarehouse').select2('readonly', true);
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
        slitems = {};
        if ($('#slwarehouse').val() && $('#slcustomer').val()) {
            $('#slcustomer').select2('readonly', true);
            $('#slwarehouse').select2('readonly', true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) return;
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (slitems[item_id]) {
        var new_qty = parseFloat(slitems[item_id].row.qty) + 1;
        slitems[item_id].row.base_quantity = new_qty;
        if (slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function() {
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        slitems[item_id].row.qty = new_qty;
    } else {
        slitems[item_id] = item;
    }
    slitems[item_id].order = new Date().getTime();
    localStorage.setItem('slitems', JSON.stringify(slitems));
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
    var overselling = localStorage.getItem('sloverselling');
    if (!is_numeric(qty) || qty <= 0) {
        if (site.settings.overselling != 1 || (site.settings.overselling == 1 && overselling != 1)) {
            $('#add_sale, #edit_sale').attr('disabled', true);
            bootbox.alert('This type has not enough quantity !');
            return;
        }
    }
    slitems[item_id].qty = qty;
    slitems[item_id].type_id = id;
    localStorage.setItem('slitems', JSON.stringify(slitems));
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
                        localStorage.setItem('sldiscount', order_discount);
                        $('#sldiscount').val(order_discount);
                    } else {
                        localStorage.setItem('sldiscount', '');
                        $('#sldiscount').val('');
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

$(document).ready(function() {
    $('#slshipping_request').change(function(e) {
        localStorage.setItem('slshipping_request', $(this).val());
    });
    if ((slshipping_request = localStorage.getItem('slshipping_request'))) {
        $('#slshipping_request').select2('val', slshipping_request);
    }
    $('#slshipping_request_phone').change(function(e) {
        localStorage.setItem('slshipping_request_phone', $(this).val());
    });
    if ((slshipping_request_phone = localStorage.getItem('slshipping_request_phone'))) {
        $('#slshipping_request_phone').val(slshipping_request_phone);
    }
    $('#slshipping_request_address').redactor('destroy');
    $('#slshipping_request_address').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html',],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('slshipping_request_address', v);
        },
    });
    if ((slshipping_request_address = localStorage.getItem('slshipping_request_address'))) {
        $('#slshipping_request_address').redactor('set', slshipping_request_address);
    }
    $('#slshipping_request_note').redactor('destroy');
    $('#slshipping_request_note').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html',],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var v = this.get();
            localStorage.setItem('slshipping_request_note', v);
        },
    });
    if ((slshipping_request_note = localStorage.getItem('slshipping_request_note'))) {
        $('#slshipping_request_note').redactor('set', slshipping_request_note);
    }
    checkShippingRequestForm();
    $(document).on('change', '#slshipping_request', function() {
        checkShippingRequestForm();
    });
});
function checkShippingRequestForm() {
    var slshipping_request = $('#slshipping_request').val();
    if (slshipping_request == 1) {
        $('#shipping_request_form').slideDown();
    } else {
        $('#shipping_request_form').slideUp();
    }
}