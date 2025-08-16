$(document).ready(function () {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    $(document).on('keypress', '.rquantity', function (e) {
        if (e.keyCode == 13) {
            $('#add_item').focus();
        }
    });
    $('#toogle-customer-read-attr').click(function () {
        var nst = $('#poscustomer').is('[readonly]') ? false : true;
        $('#poscustomer').select2("readonly", nst);
        return false;
    });
    $(".open-brands").click(function () {
        $('#brands-slider').toggle('slide', {
            direction: 'right'
        }, 700);
    });
    $(".open-category").click(function () {
        $('#category-slider').toggle('slide', {
            direction: 'right'
        }, 700);
    });
    $(".open-subcategory").click(function () {
        $('#subcategory-slider').toggle('slide', {
            direction: 'right'
        }, 700);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).is(".open-brands, .cat-child") && !$(e.target).parents("#brands-slider").size() && $('#brands-slider').is(':visible')) {
            $('#brands-slider').toggle('slide', {
                direction: 'right'
            }, 700);
        }
        if (!$(e.target).is(".open-category, .cat-child") && !$(e.target).parents("#category-slider").size() && $('#category-slider').is(':visible')) {
            $('#category-slider').toggle('slide', {
                direction: 'right'
            }, 700);
        }
        if (!$(e.target).is(".open-subcategory, .cat-child") && !$(e.target).parents("#subcategory-slider").size() && $('#subcategory-slider').is(':visible')) {
            $('#subcategory-slider').toggle('slide', {
                direction: 'right'
            }, 700);
        }
    });
    $('.po').popover({
        html: true,
        placement: 'right',
        trigger: 'click'
    }).popover();
    $('#inlineCalc').calculator({
        layout: ['_%+-CABS', '_7_8_9_/', '_4_5_6_*', '_1_2_3_-', '_0_._=_+'],
        showFormula: true
    });
    $('.calc').click(function (e) {
        e.stopPropagation();
    });
    $(document).on('click', '[data-toggle="ajax"]', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $.get(href, function (data) {
            $("#myModal").html(data).modal();
        });
    });
    $(document).on('click', '.sname', function (e) {
        var row = $(this).closest('tr');
        var itemid = row.find('.rid').val();
        $('#myModal').modal({
            remote: site.base_url + 'products/modal_view/' + itemid
        });
        $('#myModal').modal('show');
    });
});
$(document).ready(function () {

    // Order level shipping and discount localStorage
    if (posdiscount = localStorage.getItem('posdiscount')) {
        $('#posdiscount').val(posdiscount);
    }
    $(document).on('change', '#ppostax2', function () {
        localStorage.setItem('postax2', $(this).val());
        $('#postax2').val($(this).val());
    });

    if (postax2 = localStorage.getItem('postax2')) {
        $('#postax2').val(postax2);
    }

    $(document).on('blur', '#sale_note', function () {
        localStorage.setItem('posnote', $(this).val());
        $('#sale_note').val($(this).val());
    });

    if (posnote = localStorage.getItem('posnote')) {
        $('#sale_note').val(posnote);
    }

    $(document).on('blur', '#staffnote', function () {
        localStorage.setItem('staffnote', $(this).val());
        $('#staffnote').val($(this).val());
    });

    if (staffnote = localStorage.getItem('staffnote')) {
        $('#staffnote').val(staffnote);
    }

    if (posshipping = localStorage.getItem('posshipping')) {
        $('#posshipping').val(posshipping);
        shipping = parseFloat(posshipping);
    }
    $("#pshipping").click(function (e) {
        e.preventDefault();
        shipping = $('#posshipping').val() ? $('#posshipping').val() : shipping;
        $('#shipping_input').val(shipping);
        $('#sModal').modal();
    });
    $('#sModal').on('shown.bs.modal', function () {
        $(this).find('#shipping_input').select().focus();
    });
    $(document).on('click', '#updateShipping', function () {
        var s = parseFloat($('#shipping_input').val() ? $('#shipping_input').val() : '0');
        if (is_numeric(s)) {
            $('#posshipping').val(s);
            localStorage.setItem('posshipping', s);
            shipping = s;
            loadItems();
            $('#sModal').modal('hide');
        } else {
            bootbox.alert(lang.unexpected_value);
        }
    });

    /* ----------------------
     * Order Discount Handler
     * ---------------------- */
    $("#ppdiscount").click(function (e) {
        e.preventDefault();
        var dval = $('#posdiscount').val() ? $('#posdiscount').val() : '0';
        $('#order_discount_input').val(dval);
        $('#dsModal').modal();
    });
    $('#dsModal').on('shown.bs.modal', function () {
        $(this).find('#order_discount_input').select().focus();
        $('#order_discount_input').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                var ds = $('#order_discount_input').val();
                if (is_valid_discount(ds)) {
                    $('#posdiscount').val(ds);
                    localStorage.removeItem('posdiscount');
                    localStorage.setItem('posdiscount', ds);
                    loadItems();
                } else {
                    bootbox.alert(lang.unexpected_value);
                }
                $('#dsModal').modal('hide');
            }
        });
    });

    $(document).on('click', '#updateOrderDiscount', function () {
        var ds = $('#order_discount_input').val() ? $('#order_discount_input').val() : '0';
        if (is_valid_discount(ds)) {
            $('#posdiscount').val(ds);
            localStorage.removeItem('posdiscount');
            localStorage.setItem('posdiscount', ds);
            loadItems();
        } else {
            bootbox.alert(lang.unexpected_value);
        }
        $('#dsModal').modal('hide');
    });

    /* ----------------------
     * Order Tax Handler
     * ---------------------- */
    $("#pptax2").click(function (e) {
        e.preventDefault();
        var postax2 = localStorage.getItem('postax2');
        $('#order_tax_input').select2('val', postax2);
        $('#txModal').modal();
    });
    $('#txModal').on('shown.bs.modal', function () {
        $(this).find('#order_tax_input').select2('focus');
    });
    $('#txModal').on('hidden.bs.modal', function () {
        var ts = $('#order_tax_input').val();
        $('#postax2').val(ts);
        localStorage.setItem('postax2', ts);
        loadItems();
    });
    $(document).on('click', '#updateOrderTax', function () {
        var ts = $('#order_tax_input').val();
        $('#postax2').val(ts);
        localStorage.setItem('postax2', ts);
        loadItems();
        $('#txModal').modal('hide');
    });


    $(document).on('change', '.rserial', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        positems[item_id].row.serial = $(this).val();
        localStorage.setItem('positems', JSON.stringify(positems));
    });

    // If there is any item in localStorage
    if (localStorage.getItem('positems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        if (protect_delete == 1) {
            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Pin Code",
                message: '<input id="pos_pin" name="pos_pin" type="password" placeholder="Pin Code" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> OK",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = md5($('#pos_pin').val());
                            if (pos_pin == pos_settings.pin_code) {

                                if (localStorage.getItem('positems')) {
                                    localStorage.removeItem('positems');
                                }
                                if (localStorage.getItem('posdiscount')) {
                                    localStorage.removeItem('posdiscount');
                                }
                                if (localStorage.getItem('postax2')) {
                                    localStorage.removeItem('postax2');
                                }
                                if (localStorage.getItem('posshipping')) {
                                    localStorage.removeItem('posshipping');
                                }
                                if (localStorage.getItem('posref')) {
                                    localStorage.removeItem('posref');
                                }
                                if (localStorage.getItem('poswarehouse')) {
                                    localStorage.removeItem('poswarehouse');
                                }
                                if (localStorage.getItem('posnote')) {
                                    localStorage.removeItem('posnote');
                                }
                                if (localStorage.getItem('posinnote')) {
                                    localStorage.removeItem('posinnote');
                                }
                                if (localStorage.getItem('poscustomer')) {
                                    localStorage.removeItem('poscustomer');
                                }
                                if (localStorage.getItem('poscurrency')) {
                                    localStorage.removeItem('poscurrency');
                                }
                                if (localStorage.getItem('posdate')) {
                                    localStorage.removeItem('posdate');
                                }
                                if (localStorage.getItem('posstatus')) {
                                    localStorage.removeItem('posstatus');
                                }
                                if (localStorage.getItem('posbiller')) {
                                    localStorage.removeItem('posbiller');
                                }

                                $('#modal-loading').show();
                                if (pos_settings.pos_type == 'table' || pos_settings.pos_type == 'room') {
                                    window.location.href = site.base_url + "pos/index/" + $("#suspend_id").val();
                                } else {
                                    window.location.href = site.base_url + "pos";
                                }

                            } else {
                                bootbox.alert('Wrong Pin Code');
                            }
                        }
                    }
                }
            });
        } else {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('positems')) {
                        localStorage.removeItem('positems');
                    }
                    if (localStorage.getItem('posdiscount')) {
                        localStorage.removeItem('posdiscount');
                    }
                    if (localStorage.getItem('postax2')) {
                        localStorage.removeItem('postax2');
                    }
                    if (localStorage.getItem('posshipping')) {
                        localStorage.removeItem('posshipping');
                    }
                    if (localStorage.getItem('posref')) {
                        localStorage.removeItem('posref');
                    }
                    if (localStorage.getItem('poswarehouse')) {
                        localStorage.removeItem('poswarehouse');
                    }
                    if (localStorage.getItem('posnote')) {
                        localStorage.removeItem('posnote');
                    }
                    if (localStorage.getItem('posinnote')) {
                        localStorage.removeItem('posinnote');
                    }
                    if (localStorage.getItem('poscustomer')) {
                        localStorage.removeItem('poscustomer');
                    }
                    if (localStorage.getItem('poscurrency')) {
                        localStorage.removeItem('poscurrency');
                    }
                    if (localStorage.getItem('posdate')) {
                        localStorage.removeItem('posdate');
                    }
                    if (localStorage.getItem('posstatus')) {
                        localStorage.removeItem('posstatus');
                    }
                    if (localStorage.getItem('posbiller')) {
                        localStorage.removeItem('posbiller');
                    }

                    $('#modal-loading').show();
                    if (pos_settings.pos_type == 'table' || pos_settings.pos_type == 'room') {
                        window.location.href = site.base_url + "pos/index/" + $("#suspend_id").val();
                    } else {
                        window.location.href = site.base_url + "pos";
                    }
                }
            });
        }
    });

    // save and load the fields in and/or from localStorage

    $('#poswarehouse').change(function (e) {
        localStorage.setItem('poswarehouse', $(this).val());
    });
    if (poswarehouse = localStorage.getItem('poswarehouse')) {
        $('#poswarehouse').select2('val', poswarehouse);
    }

    //$(document).on('change', '#posnote', function (e) {
    $('#posnote').redactor('destroy');
    $('#posnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('posnote', v);
        }
    });
    if (posnote = localStorage.getItem('posnote')) {
        $('#posnote').redactor('set', posnote);
    }

    $('#poscustomer').change(function (e) {
        localStorage.setItem('poscustomer', $(this).val());
        if (site.settings.customer_group_discount == 1) {
            setOrderDiscountByCustomerGroup($(this).val());
        }
    });
 
    if (site.settings.customer_group_discount == 1) {
        if ($('#poscustomer').val() != '' && $('#poscustomer').val() != null && $('#posdiscount').val() == 0) {
            setOrderDiscountByCustomerGroup($('#poscustomer').val());
        }
    }

    // prevent default action upon enter
    $('body').not('textarea').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Order tax calculation
    if (site.settings.tax2 != 0) {
        $('#postax2').change(function () {
            localStorage.setItem('postax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calculation
    var old_posdiscount;
    $('#posdiscount').focus(function () {
        old_posdiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';

        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('posdiscount');
            localStorage.setItem('posdiscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_posdiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });

    /* ----------------------
     * Delete Row Method
     * ---------------------- */
    var pwacc = false;
    $(document).on('click', '.posdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var pro_id = row.attr('pro_id');
        var bill_refer = $("#bill_refer").val();
        var check_bill = $("#check_bill_refer").val();
        if (check_bill != "") {
            if(pos_settings.password_reason != 1){
                var boxd = bootbox.dialog({
                    title: "<i class='fa fa-key'></i> Pin Code ?",
                    message: '<input id="pos_pin" name="pos_pin" type="password" placeholder="Pin Code" class="form-control"> ',
                    buttons: {
                        success: {
                            label: "<i class='fa fa-tick'></i> Submit",
                            className: "btn-success verify_pin",
                            callback: function () {
                                var pos_pin = md5($('#pos_pin').val());
                                if (pos_pin == pos_settings.pin_code) {
                                    $.ajax({
                                        type: "get",
                                        url: site.base_url + "pos/del_item",
                                        data: {
                                            pro_id: pro_id,
                                            bill_refer: bill_refer,
                                            pos_pin: pos_pin
                                        },
                                        success: function (data) {
                                            if (data == "success") {
                                                delete positems[item_id];
                                                row.remove();
                                                if (positems.hasOwnProperty(item_id)) {} else {
                                                    localStorage.setItem('positems', JSON.stringify(positems));
                                                    loadItems();
                                                }
                                            }
                                            $('#suspend_sale').click();
                                        }
                                    });
                                    return false;
                                } else {
                                    alert('Wrong Pin Code');
                                }
                            }
                        }
                    }
                });
            } else {
                if (site.settings.reason_option == 1) {
                    var reason = [];
                    $.get(site.base_url + "pos/get_reason").done(function(data){
                        reason.push({'value': '', 'text': 'Select Reason'});
                        if (data) {
                            $.each(data, function(index, value) {
                                reason.push({'value': value.name, 'text': value.name});
                            });
                        }
                        bootbox.prompt({
                            title: "<i class='fa fa-key'></i> Why Remove?",
                            inputType: 'select',
                            inputOptions: reason,
                            callback: function (result) {
                                if(result != "" && result != null){
                                    $.ajax({
                                        type: "get",
                                        url: site.base_url + "pos/del_item_reason",
                                        data: {
                                            pro_id: pro_id,
                                            bill_refer: bill_refer,
                                            note: result
                                        },
                                        success: function (data) {
                                            if (data != "success") {
                                                delete positems[item_id];
                                                row.remove();
                                                if (positems.hasOwnProperty(item_id)) {} else {
                                                    localStorage.setItem('positems', JSON.stringify(positems));
                                                    loadItems();
                                                }
                                            }
                                            $('#suspend_sale').click();
                                        }
                                    });
                                    return false;
                                } else {
                                    // alert('Can\'t Remove Need Reason');
                                }
                            }
                        });
                    });
                } else {
                    var boxd = bootbox.dialog({
                        title: "<i class='fa fa-key'></i> Why Delete?",
                        message: '<input id="note" name="note" type="text" placeholder="Note" class="form-control"> ',
                        buttons: {
                            success: {
                                label: "<i class='fa fa-tick'></i> Submit",
                                className: "btn-success verify_pin",
                                callback: function () {
                                    var note = $('#note').val();
                                    if(note != ""){
                                        $.ajax({
                                            type: "get",
                                            url: site.base_url + "pos/del_item_reason",
                                            data: {
                                                pro_id: pro_id,
                                                bill_refer: bill_refer,
                                                note: note
                                            },
                                            success: function (data) {
                                                if (data != "success") {
                                                    delete positems[item_id];
                                                    row.remove();
                                                    if (positems.hasOwnProperty(item_id)) {} else {
                                                        localStorage.setItem('positems', JSON.stringify(positems));
                                                        loadItems();
                                                    }
                                                }
                                                $('#suspend_sale').click();
                                            }
                                        });
                                        return false;
                                    } else {
                                        alert('Can\'t Remove Need Reason');
                                    }
                                }
                            }
                        }
                    });
                }
            }
        } else {
            delete positems[item_id];
            row.remove();
            if (positems.hasOwnProperty(item_id)) {} else {
                localStorage.setItem('positems', JSON.stringify(positems));
                loadItems();
            }
        }
        return false;
    });

    

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
    $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = positems[item_id];
        var qty = $(row).find('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            unit_price = formatDecimal(row.children().children('.ruprice').val()),
            discount = row.children().children('.rdiscount').val();
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price) + parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#pdiscount').select2('val', item.row.discount);
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0,
                ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    // item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100));
                    item_discount = formatDecimal((parseFloat(((parseFloat(unit_price) * parseFloat(pds[0])) * (1 + Number.EPSILON)) / 100)));
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
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {
                            if (positems[item_id].row.tax_method == 0) {
                                // pr_tax_val  = formatDecimal((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))));
                                pr_tax_val  = formatDecimal(parseFloat((parseFloat(net_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(this.rate)));
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price  -= pr_tax_val;
                            } else {
                                // pr_tax_val  = formatDecimal((((net_price) * parseFloat(this.rate)) / 100));
                                pr_tax_val  = formatDecimal(parseFloat((parseFloat(net_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / 100);
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

            // if(item.product_serials!=''){
            //     uopt1 = $("<select id=\"product_serial\" name=\"product_serial\" class=\"form-control select\"/>");
            //     var serialno = row.children().children('.rserial').val();
            //     var myarray = serialno.split("#");
            //     $.each(item.product_serials, function () {
            //         if(jQuery.inArray(this.serial, myarray) !== -1){
            //             $("<option />", { value: this.id, text: this.serial, selected:true}).appendTo(uopt1);
            //         }else{
            //             $("<option />", { value: this.id, text: this.serial}).appendTo(uopt1);
            //         }
            //     });
            // }else{
            //     uopt1 = '<p style="margin: 12px 0 0 0;">n/a</p>';
            // }
            // $("#pserials-div").html(uopt1);
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if (o == 1) {
                    if (product_option == '') {
                        product_variant = this.id;
                    } else {
                        product_variant = product_option;
                    }
                }
                $("<option />", {
                    value: this.id,
                    text: this.name
                }).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }
        if (item.units !== false) {
            uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
            $.each(item.units, function () {
                if (this.id == item.row.unit) {
                    $("<option />", {
                        value: this.id,
                        text: this.name,
                        selected: true
                    }).appendTo(uopt);
                } else {
                    $("<option />", {
                        value: this.id,
                        text: this.name
                    }).appendTo(uopt);
                }
            });
        } else {
            uopt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        }
        $('#saleman_item').select2('val', item.row.saleman_item);
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({
            minimumResultsForSearch: 7
        });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');
    });
    
    $(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = positems[item_id];
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#isubcomment').val('');
        // $('#isubcomment').val(item.row.subcomment);
        $('#iordered').val(item.row.ordered);
        $('#iordered').select2('val', item.row.ordered);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });
    $(document).on('change', '#isubcomment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        var item_comment_ = $('#isubcomment').val();
        combine_comment ='';
        if (item_comment_ == null) {
            $('#icomment').val(''); 
        }
        for (var i = 0, len = item_comment_.length; i < len; i++) {
            combine_comment += (item_comment_[i].length > 0 ? "   * " + item_comment_[i] + "\n" : "");
        }
        $('#icomment').val(combine_comment);
    });
    $(document).on('click', '#editComment', function () {
        var row     = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        positems[item_id].row.order   = parseFloat($('#iorders').val()),
        positems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '';
        positems[item_id].row.subcomment = $('#isubcomment').val() ? $('#isubcomment').val() : '';
        localStorage.setItem('positems', JSON.stringify(positems));
        $('#cmModal').modal('hide');
        loadItems();
        return;
    });

    /*-------------
  * Add on items*
  --------------*/
    var item_to_addOn = '';
    $(document).on('click', '.add_on_button', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        p_id = row.attr('pro_id');
        p_q = row.find("input[name='quantity[]']").val();
        item = positems[item_id];
        item_to_addOn = item_id;
        $('.addOn-box').empty();
        if (p_id != "") {
            $.ajax({
                type: "get",
                url: site.base_url + "pos/getAddOnItemByPID_ajax/" + p_id,
                dataType: "json",
                success: function (data) {
                    if (data != false) {
                        var p = [];
                        if (data['addOnItems'] != null) {
                            data['addOnItems'].forEach((element, index, array) => {
                            p = data['p_all'].find(x => x.code === element.item_code);
                                var addon_box_content_html = "<div class='item_box' style='display: block; margin: 20px;'>" + "<div style='margin: 0 60px 20px 20px; float: left;'>" + "<div style='text-align: center;'>" + "<input type='checkbox' id='chb_" + index + "' name='chb_" + index + "' class='chk_addon' value='" + p.code + "'> " + "<label for='chb_" + index + "'>" + p.name + "</label>" + "</div>" + "<img src='" + $("#base_url").val() + "assets/uploads/" + p.image + "' alt='' style='width: 150px; height: 150px;'>" + "<div class='input-group' style='width: 120px; margin: 5px 15px;'>" + "<span class='input-group-btn'>" + "<button type='button' class='btn btn-default btn-number' disabled='disabled' id='btn-number_" + index + "' data-type='minus' data-field='quant[1]_" + index + "'>" + "<span class='fa fa2x fa-minus'></span>" + "</button>" + "</span>" + "<input type='text' name='quant[1]_" + index + "' class='form-control input-number text-center' value='0' min='0' max='10' maxlength='3'>" + "<div class='hide'><input class='chkx_addon' value='" + array[index].id + "'></div><span class='input-group-btn'>" + "<button type='button' class='btn btn-default btn-number' data-type='plus' data-field='quant[1]_" + index + "'>" + "<span class='fa fa2x fa-plus'></span>" + "</button>" + "</span>" + "</div>" + "</div>" + "</div>";
                            $('.addOn-box').append(addon_box_content_html);
                            if (item.addOn_items.length != 0) {
                                    $.each(item.addOn_items, function (key, value) {
                                        if (value.row.code == p.code) {
                                            $("#chb_" + index).prop("checked", true);
                                            $("input[name='quant[1]_" + index + "']").val(parseInt(value.row.qty));
                                            if (value.row.qty > 0) {
                                                $("#btn-number_" + index).prop("disabled", false);
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    } else {
                        $('.addOn-box').append("<p style='margin: 20px'>No matching product found.</p>");
                    }
                }
            }).fail(function (xhr, error) {
                
            });
        }

        $('#addOnModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#addOnModal').modal('show');
    });

    $(document).on('click', '.chk_addon', function () {
        if ($(this).prop("checked") == true) {
            $(this).parentsUntil("div.item_box").find("input[type=text]").val(1);
        } else {
            $(this).parentsUntil("div.item_box").find("input[type=text]").val(0);
        }
    })

    $(document).on('click', '.btn-number', function (e) {
        e.preventDefault();
        fieldName = $(this).attr('data-field');
        type = $(this).attr('data-type');
        var input = $("input[name='" + fieldName + "']");
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            if (type == 'minus') {
                if (currentVal > input.attr('min')) {
                    input.val(currentVal - 1).change();
                }
                if (parseInt(input.val()) == input.attr('min')) {
                    $(this).attr('disabled', true);
                }

            } else if (type == 'plus') {
                if (currentVal < input.attr('max')) {
                    input.val(currentVal + 1).change();
                }
                if (parseInt(input.val()) == input.attr('max')) {
                    $(this).attr('disabled', true);
                }
            }

            if (input.val() > 0) {
                $(this).parentsUntil("div.item_box").find("input[type=checkbox]").prop("checked", true);
            } else {
                $(this).parentsUntil("div.item_box").find("input[type=checkbox]").prop("checked", false);
            }
        } else {
            input.val(0);
            $(this).parentsUntil("div.item_box").find("input[type=checkbox]").prop("checked", false);
        }
    });

    $(document).on('focusin', '.input-number', function () {
        $(this).data('oldValue', $(this).val());
        $(this).select();
    });

    $(document).on('focusout', '.input-number', function (e) {
        if ($(this).val() > 0) {
            $(this).parentsUntil("div.item_box").find("input[type=checkbox]").prop("checked", true);
        } else {
            $(this).parentsUntil("div.item_box").find("input[type=checkbox]").prop("checked", false);
        }
    });

    $(document).on('change', '.input-number', function () {
        minValue = parseInt($(this).attr('min'));
        maxValue = parseInt($(this).attr('max'));
        valueCurrent = parseInt($(this).val());

        name = $(this).attr('name');
        if (valueCurrent >= minValue) {
            $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the minimum value was reached');
            $(this).val($(this).data('oldValue'));
        }
        if (valueCurrent <= maxValue) {
            $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the maximum value was reached');
            $(this).val($(this).data('oldValue'));
        }
    });

    $(document).on('keydown', '.input-number', function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $(document).on('click', '#addOn_smb', function (e) {
        $('#modal-loading').show(); 
        var checked_checkboxes = $("[class=addOn-box] input:checked");
        var unchecked_checkboxes = $("[class=addOn-box] input:not(:checked)");
        var wh = $('#poswarehouse').val();
        var cu = $('#poscustomer').val(); 
        positems[e.view.item_id].addOn_items = [];
        localStorage.setItem('positems', JSON.stringify(positems));
        if (checked_checkboxes.length === 0) {
            loadItems();
        } 
        $.each(checked_checkboxes, function (index, value) {
            var code = $(this).val();
            var quantity = $(this).parentsUntil("div.item_box").find("input[type=text]").val();
            var id = $(this).parentsUntil("div.item_box").find("input[type=number]").val();
            addon_id = $(this).parentsUntil("div.item_box").find("input[class=chkx_addon]").val();
            $.ajax({
                type: "get",
                url: site.base_url + "pos/getProductAddOnDataByCode",
                data: {
                    code: code,
                    warehouse_id: wh,
                    customer_id: cu,
                    addon_id: addon_id
                },
                dataType: "json",
                success: function (data) {
                    e.preventDefault();
                    if (data !== null) {
                        data.row.qty = quantity;
                        add_invoice_item_addon(data, item_to_addOn);
                        $('#modal-loading').hide();
                    }
                }
            });
        }); 
        $('#addOnModal').modal('toggle');
    }); 
    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    }); 
    
    $("#posmembership_code").keydown(function(event) {
        var valueidcard = $("#posmembership_code").val();
        let today = new Date().toISOString().slice(0, 10); 
        $.ajax({
            type: 'get',
            url: site.base_url + 'pos/getmember_card',
            dataType: "json",
            data: {
                idcard : valueidcard,
            },
            success: function(data) {
                if(data){
                localStorage.setItem('posdiscount', data.discount+"%");
                posdiscount = localStorage.getItem('posdiscount');
                if(data.card_no != valueidcard){
                    bootbox.alert('Your memebercard was wrong !!');
                    $("#posmembership_code").val('');                       
                    return;
                }else if(today > data.expiry){
                    bootbox.alert('Your membercard was expired!!');
                    $("#posmembership_code").val('');
                    $("#posmembership_code").focus().select();
                    return;
                } 
                if (is_valid_discount(posdiscount)) {
                    $('#posdiscount').val(posdiscount);
                    localStorage.setItem('posmembership_code', valueidcard);
                    localStorage.removeItem('posdiscount');
                    localStorage.setItem('posdiscount', posdiscount);
                    loadItems(); 
                } else {
                    bootbox.alert(lang.unexpected_value);
                }
            }else{
                    $("#posmembership_code").val('');                       
                    return;
                }
            }
        }); 
    }); 

    // $(document).on('change', '#posmembership_code', function () {
    //     var valueidcard = $("#posmembership_code").val();
    //     let today = new Date().toISOString().slice(0, 10);
    //     $.ajax({
    //         type: 'get',
    //         url: site.base_url + 'pos/getmember_card',
    //         dataType: "json",
    //         data: {
    //             idcard : valueidcard,
    //         },
    //         success: function(data) {
    //             if (data != false && data != null) {
    //                 if(today > data.expiry ){
    //                     bootbox.alert('Your membercard was expired!!');
    //                     $("#posmembership_code").val('');
    //                     $("#posmembership_code").focus().select();
    //                     localStorage.removeItem('posdiscount');
    //                 } else {
    //                     var posdiscount = data.discount + "%";
    //                     if (is_valid_discount(posdiscount)) {
    //                         localStorage.removeItem('posdiscount');
    //                         localStorage.setItem('posdiscount', posdiscount);
    //                         $('#posdiscount').val(posdiscount);
    //                     } else {
    //                         bootbox.alert(lang.unexpected_value);
    //                         localStorage.removeItem('posdiscount');
    //                     }
    //                 }
    //             } else {
    //                 bootbox.alert('Your memebercard was wrong !!');
    //                 $("#posmembership_code").val('');     
    //                 localStorage.removeItem('posdiscount');
    //             }
    //             loadItems();
    //         }
    //     });
    // });  
    // $("#posmembership_code").autocomplete({
    //     source: function(request, response) {
    //         var valueidcard = $("#posmembership_code").val();
    //         let today = new Date().toISOString().slice(0, 10);
    //         $.ajax({
    //             type: 'get',
    //             url:site.base_url + 'pos/getmember_card',
    //             dataType: "json",
    //             data: {
    //                 idcard : valueidcard,
    //             },
    //             success: function(data) {
    //                 if (data != false && data != null) {
    //                     if(today > data.expiry){
    //                         bootbox.alert('Your membercard was expired!!');
    //                         $("#posmembership_code").val('');
    //                         $("#posmembership_code").focus().select();
    //                         localStorage.removeItem('posdiscount');
    //                     }    
    //                 }
    //             }
    //         });
    //     }, 
    //      minLength: 1,
    //      autoFocus: false,
    //      delay: 250,
    //      response: function(event, ui) { 
    //      },
    //      select: function (event, ui) {
    //      event.preventDefault(); 
    //      }
    // });
    
    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = positems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                // item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
                item_discount = formatDecimal((parseFloat(((parseFloat(unit_price) * parseFloat(pds[0])) * (1 + Number.EPSILON)) / 100)));
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
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            // pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            // pr_tax_val  = formatDecimal(((unit_price) * parseFloat(this.rate)) / 100);
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / 100);
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
    

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = positems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(),
            unit = $('#punit').val(),
            base_quantity = $('#pquantity').val(),
            aprice = 0;
        if (item.options !== false) {
            $.each(item.options, function () {
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
                        $('#pprice').val(formatDecimal((parseFloat(this.price)))).change();
                    }
                });
            } else {
                $('#pprice').val(formatDecimal(item.row.base_unit_price + aprice)).change();
            }
        } else {
            if (item.units && unit != positems[item_id].row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == unit) {
                        base_quantity = unitToBaseQty($('#pquantity').val(), this);
                        // $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)))).change();
                        $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)))).change();
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
    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'),
            new_pr_tax = $('#ptax').val(),
            new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        if (item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price - parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if (!is_valid_discount($('#pdiscount').val())){
                bootbox.alert(lang.unexpected_value);
                $('#pdiscount').val($('#pdiscount').attr('data'));
                return false;
            } else {
                $('#pdiscount').attr('data', $('#pdiscount').val());
            }
        }
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != positems[item_id].row.base_unit) {
            $.each(positems[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        positems[item_id].row.fup = 1,
        positems[item_id].row.qty = parseFloat($('#pquantity').val()),
        positems[item_id].row.base_quantity = parseFloat(base_quantity),
        positems[item_id].row.real_unit_price = price,
        positems[item_id].row.unit = unit,
        positems[item_id].row.tax_rate = new_pr_tax,
        positems[item_id].tax_rate = new_pr_tax_rate,
        positems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        positems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
       // positems[item_id].row.serial = $('#pserial').val();
        positems[item_id].row.serial_no = $('#product_serial').val();
        positems[item_id].row.saleman_item = $('#saleman_item').val();
        localStorage.setItem('positems', JSON.stringify(positems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()),
            opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = positems[item_id];
        var unit = $('#punit').val(),
            base_quantity = parseFloat($('#pquantity').val()),
            base_unit_price = item.row.base_unit_price;
        if (unit != positems[item_id].row.base_unit) {
            $.each(positems[item_id].units, function () {
                if (this.id == unit) {
                    base_unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))))
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(base_unit_price) + (parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
    * Sell Gift Card modal
    ------------------------------- */
    $(document).on('click', '#sellGiftCard', function (e) {
        if (count == 1) {
            positems = {};
            if ($('#poswarehouse').val() && $('#poscustomer').val()) {
                $('#poscustomer').select2("readonly", true);
                $('#poswarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('.gcerror-con').hide();
        $('#gcModal').appendTo("body").modal('show');
        return false;
    });

    $('#gccustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });

    $('#genNo').click(function () {
        var no = generateCardNo();
        $(this).parent().parent('.input-group').children('input').val(no);
        return false;
    });
    $('.date').datetimepicker({
        format: site.dateFormats.js_sdate,
        fontAwesome: true,
        language: 'bpas',
        todayBtn: 1,
        autoclose: 1,
        minView: 2
    });
    $(document).on('click', '#addGiftCard', function (e) {
        var mid = (new Date).getTime(),
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
        //if (typeof positems === "undefined") {
        //    var positems = {};
        //}
        $.ajax({
            type: 'get',
            url: site.base_url + 'sales/sell_gift_card',
            dataType: "json",
            data: {
                gcdata: gc_data
            },
            success: function (data) {
                if (data.result === 'success') {
                    positems[mid] = {
                        "id": mid,
                        "item_id": mid,
                        "label": gcname + ' (' + gccode + ')',
                        "row": {
                            "id": mid,
                            "code": gccode,
                            "name": gcname,
                            "quantity": 1,
                            "base_quantity": 1,
                            "price": gcprice,
                            "real_unit_price": gcprice,
                            "tax_rate": 0,
                            "qty": 1,
                            "type": "manual",
                            "discount": "0",
                            "serial": "",
                            "option": ""
                        },
                        "tax_rate": false,
                        "options": false,
                        "units": false
                    };
                    localStorage.setItem('positems', JSON.stringify(positems));
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
            }
        });
        return false;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            positems = {};
            if ($('#poswarehouse').val() && $('#poscustomer').val()) {
                $('#poscustomer').select2("readonly", true);
                $('#poswarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
            mcode = $('#mcode').val(),
            mname = $('#mname').val(),
            mtax = parseInt($('#mtax').val()),
            mqty = parseFloat($('#mquantity').val()),
            mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
            unit_price = parseFloat($('#mprice').val()),
            mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            positems[mid] = {
                "id": mid,
                "item_id": mid,
                "label": mname + ' (' + mcode + ')',
                "row": {
                    "id": mid,
                    "code": mcode,
                    "name": mname,
                    "quantity": mqty,
                    "base_quantity": mqty,
                    "price": unit_price,
                    "unit_price": unit_price,
                    "real_unit_price": unit_price,
                    "tax_rate": mtax,
                    "tax_method": 0,
                    "qty": mqty,
                    "type": "manual",
                    "discount": mdiscount,
                    "serial": "",
                    "option": ""
                },
                "tax_rate": mtax_rate,
                'units': false,
                "options": false
            };
            localStorage.setItem('positems', JSON.stringify(positems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                // item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
                item_discount = formatDecimal((parseFloat(((parseFloat(unit_price) * parseFloat(pds[0])) * (1 + Number.EPSILON)) / 100)));
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
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            // pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            // pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / 100);
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(this.rate)) * (1 + Number.EPSILON)) / 100);
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
    $(document).on('change', '.rweight', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var new_weight = parseFloat($(this).val());
        positems[item_id].row.weight = new_weight;
        localStorage.setItem('positems', JSON.stringify(positems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Quantity Method
    --------------------------- */
    $(document).on('click', '.r-btn-number', function (e) {
        e.preventDefault();
        var type = $(this).attr('data-type');
        var parent = $($(this).parent()).parent();
        var input_qty = parent.find('input[name="quantity[]"]');
        var currentVal = parseInt(input_qty.val());
        old_row_qty = parseInt(input_qty.val());
        if (!isNaN(currentVal)) {
            if (type == 'minus') {
                if (currentVal > input_qty.attr('min')) {
                    input_qty.val(currentVal - 1).change();
                }
                if (parseInt(input_qty.val()) == input_qty.attr('min')) {
                    $(this).attr('disabled', true);
                }
            } else if (type == 'plus') {
                if (currentVal < input_qty.attr('max')) {
                    input_qty.val(currentVal + 1).change();
                }
                if (parseInt(input_qty.val()) == input_qty.attr('max')) {
                    $(this).attr('disabled', true);
                }
            }
        }
    });
    // alert(old_row_qty);
 

    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var pro_id = row.attr('pro_id');
        var bill_refer = $("#bill_refer").val();
        var check_bill = $("#check_bill_refer").val();
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
        positems[item_id].row.base_quantity = new_qty;
        if (positems[item_id].row.unit != positems[item_id].row.base_unit) {
            $.each(positems[item_id].units, function () {
                if (this.id == positems[item_id].row.unit) {
                    positems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        if (check_bill != "" && new_qty < old_row_qty) {
            // close table
            if(pos_settings.password_reason != 1 ){
                var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Pin Code ?",
                message: '<input id="pos_pin" name="pos_pin" type="password" placeholder="Pin Code" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = md5($('#pos_pin').val());
                            if (pos_pin == pos_settings.pin_code) {
                                $.ajax({
                                    type: "get",
                                    url: site.base_url + "pos/pin_update_item",
                                    data: {
                                        pro_id: pro_id,
                                        bill_refer: bill_refer,
                                        pos_pin: pos_pin,
                                        old_row_qty : old_row_qty,
                                        new_qty: new_qty
                                    },
                                    success: function (data) { 
                                        if (data == "success") {
                                            positems[item_id].row.qty = new_qty;
                                            localStorage.setItem('positems', JSON.stringify(positems));
                                            loadItems();
                                        }
                                        $('#suspend_sale').click();
                                    }
                                });
                                return false;
                            } else {
                                positems[item_id].row.qty = old_row_qty;
                                localStorage.setItem('positems', JSON.stringify(positems));
                                loadItems();
                                alert('Wrong Pin Code');
                            }
                        }
                    }
                }
            });
            // update item 
            }else{
                var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Why decrease qty ?",
                message: '<input id="reason" name="reason" type="text" placeholder="Reason" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var reason =  $('#reason').val();
                            $.ajax({
                                type: "get",
                                url: site.base_url + "pos/update_item",
                                data: {
                                    pro_id: pro_id,
                                    bill_refer: bill_refer,
                                    reason: reason,
                                    old_row_qty : old_row_qty,
                                    new_qty: new_qty
                                },
                                success: function (data) {
                                    if (data == "success") {
                                        positems[item_id].row.qty = new_qty;
                                        localStorage.setItem('positems', JSON.stringify(positems));
                                        loadItems();
                                    }
                                    $('#suspend_sale').click();
                                }
                            });
                            return false;      
                        }
                    }
                }
            }); 
            }
        } else {
            var wh = $("#poswarehouse").val();
            var status = 0;
            $.each(positems, function () {
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                positems[item_id] = item;
                if (item.row.code != "Time") {
                    $.ajax({
                        type: "get",
                        url: site.base_url + "pos/getProductToBuy",
                        data: { product_id: positems[item_id].row.id, warehouse_id: wh, qty: new_qty, positems: item.row },
                        dataType: "json",
                        success: function (data) {
                            for (var i = 0; i < data.length; i++) {
                                if (data[i].qty != 1) {
                                    status = 1;
                                }
                            }
                            $("#add_item").removeClass('ui-autocomplete-loading');
                        }
                    }).done(function () {
                        $('#modal-loading').hide();
                    });
                }
            });
            $.ajax({
                type: "get",
                url: site.base_url + "pos/getProductPromo",
                data: { product_id: positems[item_id].row.id, warehouse_id: wh, qty: new_qty },
                dataType: "json",
                success: function (data) {
                    if (data) {
                        for (var i = 0; i < data.length; i++) {
                            data.free = true;
                            data.parent = positems[item_id].row.id;
                            delete positems[item_id].row.id;
                            if (status == 1) {
                                add_invoice_item(data[i]);
                            }
                        }
                    }
                    $("#add_item").removeClass('ui-autocomplete-loading');
                }
            }).done(function () {
                $('#modal-loading').hide();
            });
            positems[item_id].row.qty = new_qty;
            localStorage.setItem('positems', JSON.stringify(positems));
            loadItems();
        }
    });
    
    // end ready function
    $(document).on('click', '.combo', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = positems[item_id];
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
        positems[item_id].row.real_unit_price = unit_price;
        positems[item_id].combo_items = combo_items;
        localStorage.setItem('positems', JSON.stringify(positems));
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
 * Load all items
 ----------------------- */

//localStorage.clear();
function loadItems() {
    if (localStorage.getItem('positems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        order_data = {};
        bill_data = {};
        $("#posTable tbody").empty();
        var time = ((new Date).getTime()) / 1000;
                store_name = (biller && biller.company != '-' ? biller.company : biller.name);
                order_data.store_name = store_name;
                bill_data.store_name  = store_name;
                order_data.header = "\n" + lang.order + "\n\n";
                bill_data.header  = "\n" + lang.bill + "\n\n";
                var pos_customer  = 'Customer: ' + $('#select2-chosen-1').text() + "\n";
                var hr = 'Reference: ' + $('#reference_note').val() + "\n";
                var user = 'User: ' + username + "\n";
                var title_room =  "#" + $('.title_room').html() + "\n";
                var pos_curr_time = 'Date Time: ' + date(site.dateFormats.php_ldate, time) + "\n";
                var ob_info = pos_customer + user + pos_curr_time + "\n";
                order_data.info_room = title_room;
                order_data.info = ob_info;
                bill_data.info = ob_info;
                var o_items = '';
                var b_items = '';
                $("#order_span").empty();
                $("#bill_span").empty();
                var styles = '<style>table, th, td { border-collapse:collapse; border: 1px solid #CCC; }.bold { font-weight: bold; }</style>';
                var pos_head1 = '<div style="text-align:center;"><img width="165px" src="' + $("#base_url").val() + 'assets/uploads/logos/' + site.settings.logo2 + '"></div>' +
                    '<span style="text-align:center;">' +
                    '<h3>' + site.settings.site_name + '</h3>' +
                    '<div style="text-align:center;">' + $("#bill_address").val() + '</div>';
                var pos_head2 = '<table width="100%" style="border: none !important;">' +
                    '<tr>' +
                    '<td style="border: none !important;">To: </td><td style="border: none !important;">' + $('#select2-chosen-1').text() + '</td>' +
                    '<td style="border: none !important;">Cashier: </td><td style="border: none !important;">' + username + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="border: none !important;">Time In: </td><td style="border: none !important;">' + $("#start_time").val() + '</td>' +
                    '<td style="border: none !important;">Time Out: </td><td style="border: none !important;">' + date(site.dateFormats.php_ldate, time) + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="border: none !important;">Room: </td><td style="border: none !important;">' + $('.title_room').html() + '</td>' +
                    '<td style="border: none !important;">Bill No:</td><td style="border: none !important;">' + $("#bill_refer").val() + '</td>' +
                    '</tr>' +
                    '</table>';
                var pos_head3 = '<table width="305" style="border: none !important;background:gray;font-size:15px;">' +
                    '<tr>' +
                    '<td width="10" style="text-align:center;padding:5px;">&nbsp; N</td>' +
                    '<td width="155" style="padding:5px;">&nbsp; Name</td>' +
                    '<td width="10" style="padding:5px;">&nbsp; Qty</td>' +
                    '<td width="55" style="padding:5px;">&nbsp; Price</td>' +
                    '<td width="70" style="width:30;padding:5px;">&nbsp; Total</td>' +
                    '</tr>' +
                    '</table>';
                $("#order_span").prepend(styles + pos_head1 + '<h4>' + lang.order + '</h4>' + pos_head2);
                $("#bill_span").prepend(styles + pos_head1 + '<h4>Invoice</h4>' + pos_head2);
                $("#order-table").empty();
                $("#bill-table").empty();
                $("#head_print_order").prepend('<center>' + pos_head1 + '<h4><strong>' + lang.order + '</strong></h4></center>' + pos_head2);

        positems = JSON.parse(localStorage.getItem('positems'));
        var n = 1;
        if (pos_settings.item_order == 1) {
            sortedItems = _.sortBy(positems, function (o) {
                return [parseInt(o.category), parseInt(o.order)];
            });
        } else if (site.settings.item_addition == 1) {
            sortedItems = _.sortBy(positems, function (o) {
                return [parseInt(o.order)];
            });
        } else {
            sortedItems = positems;
        }
        console.log(sortedItems);
        var category = 0, print_cate = false;
            $.each(sortedItems, function () {
                var group_price           = JSON.parse(localStorage.getItem('group_price'));
                var arr                   = JSON.parse(localStorage.getItem('group_price'));
                var item                  = this;
                var item_id               = site.settings.item_addition == 1 ? item.item_id : item.id;
                positems[item_id]         = item;
                item.order                = item.order ? item.order : new Date().getTime();
                currency                  = item.row.currency;
                item_row_id               = item.item_row_id;
                var product_id            = item.row.id,
                item_type                 = item.row.type,
                combo_items               = item.combo_items,
                item_currency             = item.row.currency;
                item_price                = item.row.price,
                item_qty                  = item.row.qty,
                item_expiry               = item.row.expiry,
                item_aqty                 = item.row.quantity,
                item_tax_method           = item.row.tax_method,
                item_ds                   = item.row.discount,
                item_discount             = 0,
                item_units                = item.units,
                item_option               = item.row.option,
                item_code                 = item.row.code,
                item_serial               = item.row.serial_no,
                item_name                 = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
                item_weight               = item.row.weight;
            var product_unit              = item.row.unit,
                base_quantity             = item.row.base_quantity;
            var unit_price                = item.row.real_unit_price;
            item_subcomment               = item.row.subcomment;
            var base_unit_price           = item.row.base_unit_price;
            var item_comment              = item.row.comment ? item.row.comment : '';
            var item_order_time           = item.row.order_time ? item.row.order_time : '';
            var item_discount_description = item.row.discount_description ? item.row.discount_description : '';
            var saleman_item              = item.row.saleman_item ? item.row.saleman_item : '';
            var wh = $('#poswarehouse').val();
            var cs = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: site.base_url + "pos/getCustomerByID",
                data: { customer_id: cs },
                dataType: "json",
                success: function (data) {
                    localStorage.setItem('price_group_id', data.price_group_id);
                    localStorage.setItem('customer_group_id', data.customer_group_id);
                }
            });
            var price_group_id  = localStorage.getItem('price_group_id');
            var customer_group_id = localStorage.getItem('customer_group_id');
            var category_id = item.category;
            var item_ordered = item.row.ordered ? item.row.ordered : 0;
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
                            if(base_quantity >= from_qty && base_quantity <= To_qty){
                                unit_price      = obj['price'];
                                item_price      = obj['price'];
                                base_unit_price = obj['price'];
                            }
                            
                        }
                    }
                }
            }
            if (item.units && item.row.fup != 1 && product_unit != item.row.base_unit) {
                if (site.settings.select_price == 1) {
                    $.each(item.set_price, function () {
                        if (this.id == product_unit) {
                            base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this));
                            unit_price = formatDecimal((parseFloat(this.price)));
                        }
                    });
                } else {
                    $.each(item.units, function () {
                        if (this.id == product_unit) {
                            base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this));
                            unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))));
                        }
                    });
                }
            }
            if (item.options !== false) {
                $.each(item.options, function () {
                    if (this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = parseFloat(unit_price) + (parseFloat(this.price));
                        unit_price = item_price;
                    }
                });
            }
            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    // item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)));
                    item_discount = formatDecimal((parseFloat(((parseFloat(unit_price) * parseFloat(pds[0])) * (1 + Number.EPSILON)) / 100)));
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                item_discount = formatDecimal(ds);
            }
            product_discount += formatDecimal(item_discount * item_qty);
            unit_price = formatDecimal(unit_price - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            // pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)));
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(pr_tax.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(pr_tax.rate)));
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            // pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100);
                            pr_tax_val  = formatDecimal(parseFloat((parseFloat(unit_price) * parseFloat(pr_tax.rate)) * (1 + Number.EPSILON)) / 100);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = formatDecimal(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimal((unit_price - pr_tax_val)) : formatDecimal(unit_price);
            unit_price = formatDecimal((unit_price + item_discount));
            var sel_opt = '';
            $.each(item.options, function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            if (pos_settings.item_order == 1 && category != item.row.category_id) {
                category = item.row.category_id;
                print_cate = true;
                var newTh = $('<tr></tr>');
                newTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
                newTh.appendTo("#posTable");
            } else {
                print_cate = false;
            }
            var def_currency = parseFloat(localStorage.getItem('exchange_bat_in'));
            var currency_code = localStorage.getItem('currency_code');
            var row_no = (new Date).getTime();
            var date_time = moment().format("DD/MM/YYYY HH:mm:ss");
            if (item_code == "Time") {
                var style_ = 'style="display: none;"';
            } else {
                var style_ = "";
            }
            var addOn_html = "";
            var button_addon = '';
            var p_order = "";
            if (item.addOn_items !== undefined) {
                addOn_html = '<br>';
                $.each(item.addOn_items, function () {
                    var addon_pr_tax = item.tax_rate;
                    var addon_pr_tax_val = 0,
                        addon_pr_tax_rate = 0;
                    if (site.settings.tax1 == 1 && 0) {
                        if (addon_pr_tax !== false && addon_pr_tax != 0) {
                            if (addon_pr_tax.type == 1) {
                                if (item_tax_method == '0') {
                                    // addon_pr_tax_val = formatDecimal(((item.row.unit_price) * parseFloat(addon_pr_tax.rate)) / (100 + parseFloat(addon_pr_tax.rate)));
                                    addon_pr_tax_val  = formatDecimal(parseFloat((parseFloat(item.row.unit_price) * parseFloat(addon_pr_tax.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(addon_pr_tax.rate)));
                                    addon_pr_tax_rate = formatDecimal(addon_pr_tax.rate) + '%';
                                } else {
                                    // addon_pr_tax_val = formatDecimal(((item.row.unit_price) * parseFloat(addon_pr_tax.rate)) / 100);
                                    addon_pr_tax_val  = formatDecimal(parseFloat((parseFloat(item.row.unit_price) * parseFloat(addon_pr_tax.rate)) * (1 + Number.EPSILON)) / 100);
                                    addon_pr_tax_rate = formatDecimal(addon_pr_tax.rate) + '%';
                                }
                            } else if (addon_pr_tax.type == 2) {
                                addon_pr_tax_val = formatDecimal(addon_pr_tax.rate);
                                addon_pr_tax_rate = addon_pr_tax.rate;
                            }
                            product_tax += addon_pr_tax_val * item_qty;
                        }
                    }
                    var subtotal_ = formatMoney(parseFloat(this.row.price) + parseFloat(addon_pr_tax_val) * parseFloat(this.row.qty));
                    this.row.currency == "KHR" ? addon_item_subtotal = subtotal_ + '៛' : addon_item_subtotal = '$' + subtotal_;
                    addOn_html += '<div>'
                        + '<input name="addon_row_id[]" type="hidden" value="row_' + row_no + '">'
                        + '<input name="addon_product_id[]" type="hidden" class="rid" value="' + this.row.id + '">'
                        + '<input name="addon_product_type[]" type="hidden" class="rtype" value="' + this.row.type + '">'
                        + '<input name="addon_product_code[]" type="hidden" class="rcode" value="' + this.row.code + '">'
                        + '<input name="addon_product_name[]" type="hidden" class="rname" value="' + this.row.name + '">'
                        + '<input name="addon_product_qty[]" type="hidden" class="rqty" value="' + this.row.qty + '">'
                        + '<input name="addon_product_price[]" type="hidden" class="rprice" value="' + this.row.price + '">'
                        + '<input name="addon_product_unit_price[]" type="hidden" class="runit_price" value="' + this.row.real_unit_price + '">'
                        + '<input name="addon_product_currency[]" type="hidden" class="rcurrency" value="' + this.row.currency + '">'
                        + '<input name="addon_product_tax_rate[]" type="hidden" class="rtax_rate" value="' + this.row.tax_rate + '">'
                        + '<input name="addon_product_option[]" type="hidden" class="roption" value="' + this.row.option + '">'
                        + '<span class="sname" id="name_' + row_no + '" style="margin-left: 15px;"><i class="fa fa-plus" style="font-size: 10px;"></i> ' + this.row.name + " " + (formatMoney(parseFloat(this.row.price) + parseFloat(addon_pr_tax_val))) + " x " + formatQuantity2(this.row.qty) + " = " + addon_item_subtotal + '</span>'
                        + '</div>';
                    p_order += "<br><span style='margin-left:35px;'>" + this.row.name + "&nbsp;&nbsp;[" + formatQuantity2(this.row.qty) + "]<span>";
                });
                button_addon = '<i class="pull-right fa fa-plus-circle fa-2x tip pointer add_on_button" id="' + row_no + '" data-item="' + item_id + '" title="Add-on" style="cursor:pointer;margin-right:5px;"></i>'; 
            }
            var button_combo = '';
            var product_combo = '<input type="hidden" name="product_combo[]"/>';
            if (item_type == 'combo' && combo_items) {
                button_combo = '<i class="pull-right fa-2x fa-regular fa-rectangle-list tip pointer combo" id="' + row_no + '" data-item="' + item_id + '" title="Combo" style="cursor:pointer;margin-right:5px;"></i>'; 
                product_combo = "<input type='hidden' name='product_combo[]' value='"+JSON.stringify(combo_items)+"'/>";
            }

            var newTr = $('<tr ' + style_ + ' id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '" item_row_id = "' + item_row_id + '" pro_id="' + product_id + '"></tr>');
            tr_html = '<td>' + product_combo + '<input name="row_id[]" value="row_' + row_no + '" type="hidden">'+
                            '<input name="item_row_id[]" value="' + item_row_id + '" type="hidden">'+
                            '<input name="pro_id" type="hidden" class="pro_id" value="' + product_id + '-' + item_code + '-' + item_name + '-' + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + '-' + formatQuantity2(item_qty) + '">'+
                            '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">'+
                            '<input name="product_order_time[]" type="hidden" class="rorder_time" value="' + (item_order_time != "" ? item_order_time : date_time) + '">'+
                            '<input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '">'+
                            '<input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '">'+
                            '<input name="product_name[]" type="hidden" class="rname" value="' + item_name + '">'+
                            '<input name="product_option[]" type="hidden" class="roption" value="' + item_option + '">'+
                            '<input name="product_subcomment[]" type="hidden" class="rproduct_subcomment" value="' + item_subcomment + '">'+
                            '<input name="discount_description[]" type="hidden" class="rdiscount_description" value="' + item_discount_description + '">'+
                            '<input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '">'+
                            '<span class="sname" id="name_' + row_no + '">' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + '</span>' + (item_comment != '' ? '<br><span style="font-size:10px;color:blue">'+item_comment+'</span>' : '' ) +'<span class="lb"></span>'+
            '<i class="pull-right fa fa-edit fa-2x tip pointer edit"   id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;' + ((item.free) ? "display:none;" : "") +'"></i>'+
            '<i class="pull-right fa fa-comment fa-2x tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i>'+
            '' +button_addon+button_combo+addOn_html + '</td>';
            tr_html += '<td class="text-right">';
            if (site.settings.product_serial == 1) {
                tr_html += '<input class="form-control input-sm rserial" name="serial[]" type="hidden" id="serial_' + row_no + '" value="' + item_serial + '">';
            }
            if (site.settings.product_discount == 1) {
                tr_html += '<input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '">';
            }
            if (site.settings.sale_man && site.settings.commission) {
                tr_html +='<input class="saleman_item" name="saleman_item[]" type="hidden" value="'+saleman_item+'">';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><input type="hidden" class="sproduct_tax" id="sproduct_tax_' + row_no + '" value="' + formatMoney(pr_tax_val * item_qty) + '">';
            }
            if (item.unit != false) {
                $.each(item.units, function () {
                    if (this.id == item.row.unit) {
                        tr_html += '<span>(' + ((this.name)) + ')</span>';
                    } else {
                        tr_html += '<span></span>';
                    }
                });
            }
            tr_html += '<input class="rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '</span></td>';
            if (item_code == "Time") {
                var hour = formatQuantity2(item_qty);
                var seconds = hour * 3600,
                    sec_num = parseInt(seconds);
                var hours = Math.floor(sec_num / 3600);
                var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                var seconds = sec_num - (hours * 3600) - (minutes * 60);
                if (hours < 10) {
                    hours = "0" + hours;
                }
                if (minutes < 10) {
                    minutes = "0" + minutes;
                }
                if (seconds < 10) {
                    seconds = "0" + seconds;
                }
                var time = hours + ':' + minutes + ':' + seconds;
                tr_html += '<td>' + time + '<button class="btn btn-default btn-number" disabled="disabled" data-type="minus" data-field="quant[1]"><span class="fa fa2x fa-minus"></span></button> <input type="text" name="quant[1]" class="form-control input-number" value="1" min="1" max="10"><button type="button" class="btn btn-default btn-number" data-type="plus" data-field="quant[1]"> <span class="fa fa2x fa-plus blue"></span></button><input class="form-control input-sm kb-pad text-center rquantity" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" type="hidden" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            } else {
                tr_html += '<td>'
                +'<div class="input-group div-qty" style="width: 120px; height: 100%;">'
                    + '<span class="input-group-btn">'
                        + '<button type="button" style="background:#dd8b51" class="btn btn-default r-btn-number" data-type="minus" data-field="quantity_' + row_no + '" >'
                            + '<span class="fa fa-minus"></span>'
                        + '</button>'
                    + '</span>'
                    + '<input class="form-control input-sm kb-pad text-center rquantity" ' + ((item.free) ? "disabled" : "") +' tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" type="text" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"  min="1" max="999" maxlength="10" style="height: 34px;" readonly><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '">'
                    + '<span class="input-group-btn">'
                        + '<button type="button" style="background:#40addd" class="btn btn-default r-btn-number" data-type="plus" data-field="quantity_' + row_no + '">'
                            + '<span class="fa fa2x fa-plus blue"></span>'
                        + '</button>'
                    + '</span>'
                +'</div>' + '</td>';
                if (site.settings.using_weight == 1) {
                    tr_html += '<td><input class="form-control rweight" name="product_weight[]" type="text" id="weight_' + row_no + '" value="' + formatQuantity2(item_weight) + '" data-id="' + row_no + '" data-item="' + item_id + '"></td>';
                }
            }
            var addon_subtotal = 0;
            if (item.addOn_items != null) {
                $.each(item.addOn_items, function () {
                    var addon_pr_tax = item.tax_rate;
                    var addon_pr_tax_val = 0,
                        addon_pr_tax_rate = 0;
                    if (site.settings.tax1 == 1 && 0) {
                        if (addon_pr_tax !== false && addon_pr_tax != 0) {
                            if (addon_pr_tax.type == 1) {
                                if (item_tax_method == '0') {
                                    addon_pr_tax_val  = formatDecimal(parseFloat((parseFloat(item.row.unit_price) * parseFloat(addon_pr_tax.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(addon_pr_tax.rate)));
                                    addon_pr_tax_rate = formatDecimal(addon_pr_tax.rate) + '%';
                                } else {
                                    addon_pr_tax_val  = formatDecimal(parseFloat((parseFloat(item.row.unit_price) * parseFloat(addon_pr_tax.rate)) * (1 + Number.EPSILON)) / (100 + parseFloat(addon_pr_tax.rate)));
                                    addon_pr_tax_rate = formatDecimal(addon_pr_tax.rate) + '%';
                                }
                            } else if (addon_pr_tax.type == 2) {
                                addon_pr_tax_val = formatDecimal(addon_pr_tax.rate);
                                addon_pr_tax_rate = addon_pr_tax.rate;
                            }
                            product_tax += addon_pr_tax_val * item_qty;
                        }
                    }
                    addon_subtotal += ((parseFloat(this.row.price) + parseFloat(addon_pr_tax_val)) * parseFloat(this.row.qty));
                });
            }
            var kh_rate = localStorage.getItem('exchange_kh');
            var ssubtotal = formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty) + parseFloat(addon_subtotal)));
            var ssubtotalkh = formatMoney(parseFloat(kh_rate) * ((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty) + parseFloat(addon_subtotal)));
            if (item_currency != 'KHR') {
                tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + (ssubtotal != 0 ? '$' + ssubtotal : 'Free') + '</span></td>';
            } else {
                tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + (ssubtotal != 0 ? ssubtotal + '៛' : 'ឥតគិតថ្លៃ') + '</span></td>';
            }
            if (permission == false || permission.remove_item == 1) {
            tr_html += '<td class="text-center"><i class="fa fa-times red fa-2x tip pointer posdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            }
            newTr.html(tr_html);
            if (pos_settings.item_order == 1) {
                newTr.appendTo("#posTable");
            } else {
                newTr.prependTo("#posTable");
            }
            if (item_code != "Time") {
            total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) + parseFloat(addon_subtotal));
            count += parseFloat(item_qty);
            an++; }
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if (this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                    }
                });
            } else if (item_type == 'standard') {
                if (base_quantity > item_aqty) {
                    $('#row_' + row_no).addClass('danger');    
                }
                var new_arr = {};
                if (positems != null) {
                    $.each(positems, function (index, obj) {
                        if (obj.row.id == product_id && obj.expiry == item_expiry) {
                            new_arr[obj.row.id] = new_arr[obj.row.id] === undefined ? 0 : new_arr[obj.row.id];
                            new_arr[obj.row.id] += parseFloat(obj.row.base_quantity);
                        }
                    });
                }
                if (Object.keys(new_arr) == product_id) {
                    if (parseFloat(Object.values(new_arr)) > parseFloat(item_aqty)) {
                        $('#row_' + row_no).addClass('danger');
                    }
                }
            } else if (item_type == 'combo') {
                if (combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                } else {
                    $.each(combo_items, function () {
                        if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                            $('#row_' + row_no).addClass('danger');
                        }
                    });
                }
            } 
            var comments = item_comment.split(/\r?\n/g);
            if (pos_settings.remote_printing != 1) {
                b_items += product_name("#" + (an - 1) + " " + item_code + " - " + item_name) + "\n";
                for (var i = 0, len = comments.length; i < len; i++) {
                    b_items += (comments[i].length > 0 ? "   * " + comments[i] + "\n" : "");
                }
                b_items += printLine("   " + formatDecimal(item_qty) + " x " + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + ": " + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)))) + "\n";
                o_items += printLine(product_name('#' + (an - 1) + ' ' + item_code + ' - ' + item_name) + ': [' + item_qty + ']') + "\n";
                for (var i = 0, len = comments.length; i < len; i++) {
                    o_items += (comments[i].length > 0 ? "   * " + comments[i] + "\n" : "");
                }
                o_items += "\n";
            } else {
                if (n == 1) {
                    var table_b = '<tr class="head" style="background:cccccc;">';
                    table_b += '               <th>No</th>';
                    table_b += '               <th>Description</th>';
                    table_b += '               <th>Qty</th>';
                    table_b += '               <th>price</th>';
                    table_b += '               <th>Total</th>';
                    table_b += '</tr>';
                }
                if (pos_settings.item_order == 1 && print_cate) {
                    var bprTh = $('<tr></tr>');
                    bprTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
                    var oprTh = $('<tr></tr>');
                    oprTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
                    $("#order-table").append(oprTh);
                    $("#bill-table").append(bprTh);
                }
                var currency_code = localStorage.getItem('currency_code');
                var default_currency = localStorage.getItem('default_currency');
                if (item_code != "Time") {  
                    var ssubtotal = formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) + parseFloat(addon_subtotal));
                    var bprTr = '<tr>' +
                        '<td width="10">' + (an - 1) + '</td>' +
                        '<td width="155">' + item_name;
                    for (var i = 0, len = comments.length; i < len; i++) {
                        bprTr += (comments[i] ? ' (<b><small>' + comments[i] + '</small></b>)' : '');
                    }
                    bprTr += '<span style="font-size:12px;">' + addOn_html + '</span></td>';
                    if (default_currency != "KHR"){
                    bprTr += '<td width="10">' + formatDecimal(item_qty) + '</td>' +
                        '<td width="70" style="text-align:left;">$' + (item_discount != 0 ? '<del>' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount) + '</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '</td>' +
                        '<td width="70" style="text-align:left;">' + (ssubtotal != 0 ? '$' + ssubtotal : 'Free') + '</td>' ;
                    } else {
                        bprTr += '<td width="10">' + formatDecimal(item_qty) + '</td>' +
                            '<td width="55" style="text-align:left;"> ' + (item_discount != 0 ? '<del>' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount) + '</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '៛</td>' +
                            '<td width="70" style="text-align:left;">' + (ssubtotal != 0 ? ssubtotal + '៛' : 'ឥតគិតថ្លៃ') + '</td>';
                    }
                    bprTr += '</tr>';
                    bprTr += '<tr class="row_' + item_id + '" data-item-id="' + item_id + '">' + '</tr>';
                    var oprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>#' + (an - 1) + " " + item_name;
                    for (var i = 0, len = comments.length; i < len; i++) {
                        oprTr += (comments[i] ? '<br> <b>*</b> <small>' + comments[i] + '</small>' : '');
                    }
                    oprTr += p_order + '</td>' + '<td>[' + (formatDecimal(item_qty)) + ']</td>' + '</tr>';
                }
                if (item_code != "Time") {
                    var barprTh = '<div style="width:1.85in;height:1.80in;">';
                    barprTh += '<p class="text-center"><b>No: 0' + pos_settings.wait_number + '</b></p>';
                    barprTh += '<span class="barcode_name">Items: ' + item_name + '</span><br>';
                    if ((sel_opt)) {
                        barprTh += '<span class="barcode_name">Option: ' + ('(' + sel_opt + ')') + '</span><br>';
                    }
                    if (addOn_html) {
                        barprTh += '<span class="barcode_name">Add-ON: ' + ('(' + addOn_html + ')') + '</span><br>';
                    }
                    barprTh += '<span class="barcode_name">Price: ' + item_price + '</span><br>';
                    barprTh += '</div>';
                }
                $("#barcode-table").append(barprTh);
                $("#order-table").append(oprTr);
                // $("#bill-table").append(table_b).append(bprTr);
                if (n == 1) {
                    $("#bill-table").append(table_b).append(bprTr);
                } else {
                    $("#bill-table").append(bprTr);
                }
            }
            n++;
        });
        if (posdiscount = localStorage.getItem('posdiscount')) {
            var ds = posdiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((parseFloat(((parseFloat(total) * parseFloat(pds[0])) * (1 + Number.EPSILON)) / 100)));
                } else {
                    order_discount = parseFloat(ds);
                }
            } else {
                order_discount = parseFloat(ds);
            }
        }
        if (site.settings.tax2 != 0) {
            if (postax2 = localStorage.getItem('postax2')) {
                $.each(tax_rates, function () {
                    if (this.id == postax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100));
                        }
                    }
                });
            }
        }
        total             = formatDecimal(total);
        product_tax       = formatDecimal(product_tax);
        total_discount    = formatDecimal(order_discount + product_discount);
        var def_currency  = parseFloat(localStorage.getItem('exchange_bat_in'));
        var currency_code = localStorage.getItem('currency_code');
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + parseFloat(shipping));
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatQty(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#tds').text('(' + formatMoney(product_discount) + ') ' + formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(parseFloat(shipping) > 0 ? formatMoney(shipping) : '');
        //--------convert Khmer to EN-----
        var kh_rate = localStorage.getItem('exchange_kh');
        var gtotal_kh = parseFloat(gtotal * kh_rate);
        var gtotal_en = parseFloat(gtotal / kh_rate);
        // if (gtotal_kh.toFixed(2) % 100 >= 50) { //alert("ceil: " + gtotal.toFixed(2));
        //     $('#gtotal_en').text('KHR ' + money_comma(Math.ceil(gtotal_kh.toFixed(2) / 100) * 100));
        // } else { //alert("floor: " + gtotal_kh);
        //     $('#gtotal_en').text('KHR ' + money_comma(Math.floor(gtotal_kh.toFixed(2) / 100) * 100));
        // }
        if (currency_code != "KHR") {
            if (gtotal_kh.toFixed(2) % 100 >= 50) { //alert("ceil: " + gtotal.toFixed(2));
                $('#gtotal_en').text('KHR ' + money_comma(Math.ceil(gtotal_kh.toFixed(2) / 100) * 100));
            } else { //alert("floor: " + gtotal_kh);
                $('#gtotal_en').text('KHR ' + money_comma(Math.floor(gtotal_kh.toFixed(2) / 100) * 100));
            }
            $('#gtotal').text('$' + formatMoney(gtotal));
        } else {
            if (gtotal.toFixed(2) % 100 >= 50) { //alert("ceil: " + gtotal.toFixed(2));
                $('#gtotal').text('KHR ' + money_comma(Math.ceil(gtotal.toFixed(2) / 100) * 100));
            } else { //alert("floor: " + gtotal_kh);
                $('#gtotal').text('KHR ' + money_comma(Math.floor(gtotal.toFixed(2) / 100) * 100));
            }
            $('#gtotal_en').text('$ ' + formatMoney(gtotal_en));
        }
        //--------Khmer currency-----
        /*
        var gtotal_kh = parseFloat(gtotal * kh_rate );
        $("#gtotal_kh").text('(៛ '+formatSA(parseFloat(gtotal_kh).toFixed(0)) + ')');
        */
        if (pos_settings.remote_printing != 1) {
            order_data.items = o_items;
            bill_data.items = b_items;
            var b_totals = '';
            b_totals += printLine(lang.total + ': ' + formatMoney(total)) + "\n";
            if (order_discount > 0 || product_discount > 0) {
                b_totals += printLine(lang.discount + ': ' + formatMoney(order_discount + product_discount)) + "\n";
            }
            if (site.settings.tax2 != 0 && invoice_tax != 0) {
                b_totals += printLine(lang.order_tax + ': ' + formatMoney(invoice_tax)) + "\n";
            }
            b_totals += printLine(lang.grand_total + ': ' + formatMoney(gtotal)) + "\n";
            if (pos_settings.rounding != 0) {
                round_total = roundNumber(gtotal, parseInt(pos_settings.rounding));
                var rounding = formatDecimal(round_total - gtotal);
                b_totals += printLine(lang.rounding + ': ' + formatMoney(rounding)) + "\n";
                b_totals += printLine(lang.total_payable + ': ' + formatMoney(round_total)) + "\n";
            }
            b_totals += "\n" + lang.items + ': ' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')' + "\n";
            bill_data.totals = b_totals;
            bill_data.footer = "\n" + lang.merchant_copy + "\n";
        } else {
            var default_currency = localStorage.getItem('default_currency');
            var bill_totals = '';
            if (default_currency != "KHR") {
                bill_totals += '<tr>' +
                    '<td style="text-align:right;background:f8f8f8;" colspan="4" >សរុប/' + lang.total + ':</td>' +
                    '<td style="text-align:left;background:f8f8f8;"> $' + formatMoney(total) + '</td>' +
                    '</tr>';
            if (order_discount > 0 || product_discount > 0) {
                bill_totals += '<tr>' +
                    '<td colspan="4" style="text-align:right;">បញះតំលៃ/' + lang.discount + ':</td>' +
                    '<td style="text-align:left;"> $' + formatMoney(order_discount + product_discount) + '</td>' +
                    '</tr>';
            }
            if (site.settings.tax2 != 0 && invoice_tax != 0) {
                bill_totals += '<tr>' +
                    '<td colspan="4" style="text-align:right;">' + lang.order_tax + ':</td>' +
                    '<td style="text-align:left;"> $' + formatMoney(invoice_tax) + '</td></tr>';
            }
            bill_totals += '<tr>' +
                '<td colspan="4" style="text-align:right;">' + lang.grand_total + '($):</td>' +
                '<td width="70" style="text-align:left;"> $' + formatMoney(gtotal) + '</td>' +
                '</tr>';
            bill_totals += '<tr>' +
                '<td colspan="4" style="text-align:right;">' + lang.grand_total + '(R):</td>' +
                '<td style="text-align:left;"> ' + formatMoney(gtotal_kh) + '៛</td>' +
                '</tr>';
            if (pos_settings.rounding != 0) {
                round_total = roundNumber(gtotal, parseInt(pos_settings.rounding));
                var rounding = formatDecimal(round_total - gtotal);
                bill_totals += '<tr class="bold">' +
                    '<td colspan="5">' + lang.rounding + ':</td>' +
                    '<td style="text-align:left;"> $' + formatMoney(rounding) + '</td>' +
                    '</tr>';
                bill_totals += '<tr class="bold">' +
                    '<td colspan="4">' + lang.total_payable + ':</td>' +
                    '<td style="text-align:left;"> $' + formatMoney(round_total) + '</td></tr>';
            }
        } else {
            bill_totals += '<tr>' +
                '<td style="text-align:right;background:f8f8f8;" colspan="4" >សរុប/' + lang.total + ':</td>' +
                '<td style="text-align:left;background:f8f8f8;"> ' + formatMoney(total) + '៛</td>' +
                '</tr>';
            if (order_discount > 0 || product_discount > 0) {

                bill_totals += '<tr>' +
                    '<td colspan="4" style="text-align:right;">បញះតំលៃ/' + lang.discount + ':</td>' +
                    '<td style="text-align:left;"> ' + formatMoney(order_discount + product_discount) + '៛</td>' +
                    '</tr>';
            }
            if (site.settings.tax2 != 0 && invoice_tax != 0) {
                bill_totals += '<tr>' +
                    '<td colspan="4" style="text-align:right;">' + lang.order_tax + ':</td>' +
                    '<td style="text-align:left;">' + formatMoney(invoice_tax) + ' ៛</td></tr>';
            }
            bill_totals += '<tr>' +
                '<td colspan="4" style="text-align:right;">' + lang.grand_total + '(KHR):</td>' +
                '<td width="70" style="text-align:left;"> ' + formatMoney(gtotal) + '៛</td>' +
                '</tr>';
            bill_totals += '<tr>' +
                '<td colspan="4" style="text-align:right;">' + lang.grand_total + '(USD):</td>' +
                '<td style="text-align:left;"> $' + formatMoney(gtotal_en) + '</td>' +
                '</tr>';
            if (pos_settings.rounding != 0) {
                round_total = roundNumber(gtotal, parseInt(pos_settings.rounding));
                var rounding = formatDecimal(round_total - gtotal);
                bill_totals += '<tr class="bold">' +
                    '<td colspan="5">' + lang.rounding + ':</td>' +
                    '<td style="text-align:left;"> ' + formatMoney(rounding) + '៛</td>' +
                    '</tr>';
                bill_totals += '<tr class="bold">' +
                    '<td colspan="4">' + lang.total_payable + ':</td>' +
                    '<td style="text-align:left;"> ' + formatMoney(round_total) + '៛</td></tr>';
            }
        }
            /*   bill_totals += '<tr>'+
								'<td></td>'+
								'<td>ចំនួនទំនិញ</td>'+
								'<td>'+lang.items+'</td>'+
								'<td>:</td>'+
								'<td style="text-align:right;">'+(an - 1) + ' (' + (parseFloat(count) - 1) + ')</td>'+
								'</tr>';*/
            bill_totals += '<tr>' + '<td colspan="5"><center>Thank you, Please come again</center></td>' + '</tr>';
            $('#bill-total-table').empty();
            $('#bill-total-table').append(bill_totals);
        }
        if (count > 1) {
            $('#poscustomer').select2("readonly", true);
            $('#poswarehouse').select2("readonly", true);
        } else {
            $('#poscustomer').select2("readonly", false);
            $('#poswarehouse').select2("readonly", false);
        }
        if (KB) {
            display_keyboards();
        }
        if (site.settings.set_focus == 1) {
            $('#add_item').attr('tabindex', an);
            $('[tabindex=' + (an - 1) + ']').focus().select();
        } else {
            $('#add_item').attr('tabindex', 1);
            $('#add_item').focus();
        }
    }
}

function printLine(str) {
    var size = pos_settings.char_per_line;
    var len = str.length;
    var res = str.split(":");
    var newd = res[0];
    for (i = 1; i < (size - len); i++) {
        newd += " ";
    }
    newd += res[1];
    return newd;
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */

function add_invoice_item(item) {
    if (count == 1) {
        positems = {};
        if ($('#poswarehouse').val() && $('#poscustomer').val()) {
            $('#poscustomer').select2("readonly", true);
            $('#poswarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (positems[item_id]) {
        var new_qty = parseFloat(positems[item_id].row.qty) + 1;
        positems[item_id].row.base_quantity = new_qty;
        if (positems[item_id].row.unit != positems[item_id].row.base_unit) {
            $.each(positems[item_id].units, function () {
                if (this.id == positems[item_id].row.unit) {
                    positems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        positems[item_id].row.qty = new_qty;
    } else {
        positems[item_id] = item;
    }
    positems[item_id].order = new Date().getTime();
    positems[item_id].addOn_items = [];
    localStorage.setItem('positems', JSON.stringify(positems));
    loadItems();
    return true;
}

function add_invoice_item_addon(item, id) {
    if (count == 1) {
        positems = {};
        if ($('#poswarehouse').val() && $('#poscustomer').val()) {
            $('#poscustomer').select2("readonly", true);
            $('#poswarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    if (Object.prototype.toString.call(positems[id].addOn_items) !== '[object Array]') {
        positems[id].addOn_items = [];
    }
    positems[id].addOn_items.push(item);
    positems[id].order = new Date().getTime();
    localStorage.setItem('positems', JSON.stringify(positems));
    loadItems();
    return true;
}

function remove_invoice_item_addon(id, addon_item_code) {
    const index = positems[id].addOn_items.findIndex(obj => obj.row.code === addon_item_code);
    positems[id].addOn_items.splice(index, 1);
    localStorage.setItem('positems', JSON.stringify(positems));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}

function display_keyboards() {
    $('.kb-text').keyboard({
        autoAccept: true,
        alwaysOpen: false,
        openOn: 'focus',
        usePreview: false,
        layout: 'custom',
        //layout: 'qwerty',
        display: {
            'bksp': "\u2190",
            'accept': 'return',
            'default': 'ABC',
            'meta1': '123',
            'meta2': '#+='
        },
        customLayout: {
            'default': [
                'q w e r t y u i o p {bksp}',
                'a s d f g h j k l {enter}',
                '{s} z x c v b n m , . {s}',
                '{meta1} {space} {cancel} {accept}'
            ],
            'shift': [
                'Q W E R T Y U I O P {bksp}',
                'A S D F G H J K L {enter}',
                '{s} Z X C V B N M / ? {s}',
                '{meta1} {space} {meta1} {accept}'
            ],
            'meta1': [
                '1 2 3 4 5 6 7 8 9 0 {bksp}',
                '- / : ; ( ) \u20ac & @ {enter}',
                '{meta2} . , ? ! \' " {meta2}',
                '{default} {space} {default} {accept}'
            ],
            'meta2': [
                '[ ] { } # % ^ * + = {bksp}',
                '_ \\ | &lt; &gt; $ \u00a3 \u00a5 {enter}',
                '{meta1} ~ . , ? ! \' " {meta1}',
                '{default} {space} {default} {accept}'
            ]
        }
    });
    $('.kb-pad').keyboard({
        restrictInput: true,
        preventPaste: true,
        autoAccept: true,
        alwaysOpen: false,
        openOn: 'click',
        usePreview: false,
        layout: 'custom',
        display: {
            'b': '\u2190:Backspace',
        },
        customLayout: {
            'default': [
                '1 2 3 {b}',
                '4 5 6 . {clear}',
                '7 8 9 0 %',
                '{accept} {cancel}'
            ]
        }
    });
    var cc_key = (site.settings.decimals_sep == ',' ? ',' : '{clear}');
    $('.kb-pad1').keyboard({
        restrictInput: true,
        preventPaste: true,
        autoAccept: true,
        alwaysOpen: false,
        openOn: 'click',
        usePreview: false,
        layout: 'custom',
        display: {
            'b': '\u2190:Backspace',
        },
        customLayout: {
            'default': [
                '1 2 3 {b}',
                '4 5 6 . ' + cc_key,
                '7 8 9 0 %',
                '{accept} {cancel}'
            ]
        }
    });
}
/*$(window).bind('beforeunload', function(e) {
    if(count > 1){
    var msg = 'You will loss the sale data.';
        (e || window.event).returnValue = msg;
        return msg;
    }
});
*/
if (site.settings.auto_detect_barcode == 1) {
    $(document).ready(function () {
        var pressed = false;
        var chars = [];
        $(window).keypress(function (e) {
            if (e.key == '%') {
                pressed = true;
            }
            chars.push(String.fromCharCode(e.which));
            if (pressed == false) {
                setTimeout(function () {
                    if (chars.length >= 8) {
                        var barcode = chars.join("");
                        $("#add_item").focus().autocomplete("search", barcode);
                    }
                    chars = [];
                    pressed = false;
                }, 200);
            }
            pressed = true;
        });
    });
}

$(document).ready(function () {
    read_card();
});

function generateCardNo(x) {
    if (!x) {
        x = 16;
    }
    chars = "1234567890";
    no = "";
    for (var i = 0; i < x; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        no += chars.substring(rnum, rnum + 1);
    }
    return no;
}

function roundNumber(number, toref) {
    switch (toref) {
        case 1:
            var rn = formatDecimal(Math.round(number * 20) / 20);
            break;
        case 2:
            var rn = formatDecimal(Math.round(number * 2) / 2);
            break;
        case 3:
            var rn = formatDecimal(Math.round(number));
            break;
        case 4:
            var rn = formatDecimal(Math.ceil(number));
            break;
        default:
            var rn = number;
    }
    return rn;
}

function getNumber(x) {
    return accounting.unformat(x);
}

function formatQuantity(x) {
    return (x != null) ? '<div class="text-center">' + formatNumber(x, site.settings.qty_decimals) + '</div>' : '';
}

function formatQuantity2(x) {
    return (x != null) ? formatQuantityNumber(x, site.settings.qty_decimals) : '';
}

function formatQuantityNumber(x, d) {
    if (!d) {
        d = site.settings.qty_decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}

function formatQty(x) {
    return (x != null) ? formatNumber(x, site.settings.qty_decimals) : '';
}

function formatNumber(x, d) {
    if (!d && d != 0) {
        d = site.settings.decimals;
    }
    if (site.settings.sac == 1) {
        return formatSA(parseFloat(x).toFixed(d));
    }
    return accounting.formatNumber(x, d, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep);
}

function formatMoney(x, symbol) {
    if (!symbol) {
        symbol = "";
    }
    if (site.settings.sac == 1) {
        return symbol + '' + formatSA(parseFloat(x).toFixed(site.settings.decimals));
    } 
    return accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
}

function money_comma(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function formatCNum(x) {
    if (site.settings.decimals_sep == ',') {
        var x = x.toString();
        var x = x.replace(",", ".");
        return parseFloat(x);
    }
    return x;
}

function formatDecimal(x, d) {
    if (!d) {
        d = site.settings.decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}

function hrsd(sdate) {
    return moment().format(site.dateFormats.js_sdate.toUpperCase())
}

function hrld(ldate) {
    return moment().format(site.dateFormats.js_sdate.toUpperCase() + ' H:mm')
}

function is_valid_discount(mixed_var){
    if(/[0-9]%[^a-z]/gi.test(mixed_var) || /[0-9]%[^0-9]/gi.test(mixed_var)) return false;
    return (is_numeric(mixed_var) || (/([0-9]%)/i.test(mixed_var))) ? true : false;
}

function is_numeric(mixed_var) {
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
        1)) && mixed_var !== '' && !isNaN(mixed_var);
}

function is_float(mixed_var) {
    return +mixed_var === mixed_var && (!isFinite(mixed_var) || !!(mixed_var % 1));
}

function currencyFormat(x) {
    return formatMoney(x != null ? x : 0);
}

function formatSA(x) {
    x = x.toString();
    var afterPoint = '';
    if (x.indexOf('.') > 0)
        afterPoint = x.substring(x.indexOf('.'), x.length);
    x = Math.floor(x);
    x = x.toString();
    var lastThree = x.substring(x.length - 3);
    var otherNumbers = x.substring(0, x.length - 3);
    if (otherNumbers != '')
        lastThree = ',' + lastThree;
    var res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

    return res;
}

function unitToBaseQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function baseToUnitQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function read_card() {
    var typingTimer;

    $('.swipe').keyup(function (e) {
        e.preventDefault();
        var self = $(this);
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function () {
            var payid = self.attr('id');
            var id = payid.substr(payid.length - 1);
            var v = self.val();
            var p = new SwipeParserObj(v);

            if (p.hasTrack1) {
                var CardType = null;
                var ccn1 = p.account.charAt(0);
                if (ccn1 == 4)
                    CardType = 'Visa';
                else if (ccn1 == 5)
                    CardType = 'MasterCard';
                else if (ccn1 == 3)
                    CardType = 'Amex';
                else if (ccn1 == 6)
                    CardType = 'Discover';
                else
                    CardType = 'Visa';

                $('#pcc_no_' + id).val(p.account).change();
                $('#pcc_holder_' + id).val(p.account_name).change();
                $('#pcc_month_' + id).val(p.exp_month).change();
                $('#pcc_year_' + id).val(p.exp_year).change();
                $('#pcc_cvv2_' + id).val('');
                $('#pcc_type_' + id).val(CardType).change();
                self.val('');
                $('#pcc_cvv2_' + id).focus();
            } else {
                $('#pcc_no_' + id).val('');
                $('#pcc_holder_' + id).val('');
                $('#pcc_month_' + id).val('');
                $('#pcc_year_' + id).val('');
                $('#pcc_cvv2_' + id).val('');
                $('#pcc_type_' + id).val('');
            }
        }, 100);
    });

    $('.swipe').keydown(function (e) {
        clearTimeout(typingTimer);
    });
}

function check_add_item_val() {
    $('#add_item').bind('keypress', function (e) {
        if (e.keyCode == 13 || e.keyCode == 9) {
            e.preventDefault();
            $(this).autocomplete("search");
        }
    });
}

function nav_pointer() {
    var pp = p_page == 'n' ? 0 : p_page;
    (pp == 0) ? $('#previous').attr('disabled', true): $('#previous').attr('disabled', false);
    ((pp + pro_limit) > tcp) ? $('#next').attr('disabled', true): $('#next').attr('disabled', false);
}

function product_name(name, size) {
    if (!size) {
        size = 42;
    }
    return name.substring(0, (size - 7));
}

$.extend($.keyboard.keyaction, {
    enter: function (base) {
        if (base.$el.is("textarea")) {
            base.insertText('\r\n');
        } else {
            base.accept();
        }
    }
});

$(document).ajaxStart(function () {
    $('#ajaxCall').show();
}).ajaxStop(function () {
    $('#ajaxCall').hide();
});

$(document).ready(function () {
    nav_pointer();
    $('#myModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-dialog').empty();
        $(this).removeData('bs.modal');
    });
    $('#myModal2').on('hidden.bs.modal', function () {
        $(this).find('.modal-dialog').empty();
        $(this).removeData('bs.modal');
        $('#myModal').css('zIndex', '1050');
        $('#myModal').css('overflow-y', 'scroll');
    });
    $('#myModal2').on('show.bs.modal', function () {
        $('#myModal').css('zIndex', '1040');
    });
    $('.modal').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
    $('.modal').on('show.bs.modal', function () {
        $('#modal-loading').show();
        $('.blackbg').css('zIndex', '1041');
        $('.loader').css('zIndex', '1042');
    }).on('hide.bs.modal', function () {
        $('#modal-loading').hide();
        $('.blackbg').css('zIndex', '3');
        $('.loader').css('zIndex', '4');
    });
    $('#clearLS').click(function (event) {
        bootbox.confirm("Are you sure?", function (result) {
            if (result == true) {
                localStorage.clear();
                location.reload();
            }
        });
        return false;
    });
});

//$.ajaxSetup ({ cache: false, headers: { "cache-control": "no-cache" } });
if (pos_settings.focus_add_item != '') {
    shortcut.add(pos_settings.focus_add_item, function () {
        $("#add_item").focus();
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.add_manual_product != '') {
    shortcut.add(pos_settings.add_manual_product, function () {
        $("#addManually").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.customer_selection != '') {
    shortcut.add(pos_settings.customer_selection, function () {
        $("#poscustomer").select2("open");
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.add_customer != '') {
    shortcut.add(pos_settings.add_customer, function () {
        $("#add-customer").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.toggle_category_slider != '') {
    shortcut.add(pos_settings.toggle_category_slider, function () {
        $("#open-category").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.toggle_brands_slider != '') {
    shortcut.add(pos_settings.toggle_brands_slider, function () {
        $("#open-brands").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.toggle_subcategory_slider != '') {
    shortcut.add(pos_settings.toggle_subcategory_slider, function () {
        $("#open-subcategory").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.cancel_sale != '') {
    shortcut.add(pos_settings.cancel_sale, function () {
        $("#reset").click();
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.suspend_sale != '') {
    shortcut.add(pos_settings.suspend_sale, function () {
        $("#suspend").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.print_items_list != '') {
    shortcut.add(pos_settings.print_items_list, function () {
        $("#print_btn").click();
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.finalize_sale != '') {
    shortcut.add(pos_settings.finalize_sale, function () {
        if ($('#paymentModal').is(':visible')) {
            $("#submit-sale").click();
        } else {
            $("#payment").trigger('click');
        }
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.today_sale != '') {
    shortcut.add(pos_settings.today_sale, function () {
        $("#today_sale").click();
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.open_hold_bills != '') {
    shortcut.add(pos_settings.open_hold_bills, function () {
        $("#opened_bills").trigger('click');
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
if (pos_settings.close_register != '') {
    shortcut.add(pos_settings.close_register, function () {
        $("#close_register").click();
    }, {
        'type': 'keydown',
        'propagate': false,
        'target': document
    });
}
shortcut.add("ESC", function () {
    $("#cp").trigger('click');
}, {
    'type': 'keydown',
    'propagate': false,
    'target': document
});

if (site.settings.set_focus != 1) {
    $(document).ready(function () {
        $('#add_item').focus();
        
    });
}

function setOrderDiscountByCustomerGroup(customer_id){
    if (customer_id != '' && customer_id != null) {
        $.ajax({
            type: "get",
            url: site.base_url + "sales/getCustomerGroupByCustomerID_ajax/" + customer_id,
            success: function(dataResult){
                if(dataResult && dataResult.percent != 0){
                    var order_discount = (-1 * dataResult.percent + '%');
                    localStorage.setItem('posdiscount', order_discount);
                    $('#posdiscount').val(order_discount);
                } else {
                    localStorage.setItem('posdiscount', 0);
                    $('#posdiscount').val(0);
                }
                loadItems();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // console.log("Error!: " + textStatus);
            },
            complete: function(xhr, statusText){
                // console.log(xhr.status + " " + statusText);
            }
        });
    }
}
function formatMoneyKH(x, symbol) {
    if(!symbol) { symbol = ""; }
    if(site.settings.sac == 1) {
        return symbol+''+formatSA(parseFloat(x).toFixed(0));
    }
    return accounting.formatMoney(x, symbol, 0, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
}
