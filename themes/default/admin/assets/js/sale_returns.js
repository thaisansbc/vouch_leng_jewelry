$(document).ready(function (e) {
	$('#add_sale_return, #edit_sale_return, #add_sale_return_next').attr('disabled', true);
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var $customer = $('#srlcustomer');
    $customer.change(function (e) {
        localStorage.setItem('srlcustomer', $(this).val());
    });
    if (srlcustomer = localStorage.getItem('srlcustomer')) {
        $customer.val(srlcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });	
            },
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
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsCustomer();
    }

	if (srldiscount = localStorage.getItem('srldiscount')) {
		$('#srldiscount').val(srldiscount);
	}
	$('#srltax2').change(function (e) {
		localStorage.setItem('srltax2', $(this).val());
		$('#srltax2').val($(this).val());
	});
	if (srltax2 = localStorage.getItem('srltax2')) {
		$('#srltax2').select2("val", srltax2);
	}
	$(document).on('change', '#srlbiller', function (e) {
		localStorage.setItem('srlbiller', $(this).val());
	});
	if (srlbiller = localStorage.getItem('srlbiller')) {
		$('#srlbiller').val(srlbiller);
	}
	$(document).on("change", '.rexpired', function () {
		var new_expired = $(this).val();
		var item_id = $(this).closest('tr').attr('data-item-id');
		srlitems[item_id].row.expired = new_expired;
		localStorage.setItem('srlitems', JSON.stringify(srlitems));
		loadItems();
	});
	
	$(document).on('change', '#srldate', function (e) {
		localStorage.setItem('srldate', $(this).val());
	});
	if (srldate = localStorage.getItem('srldate')) {
		$('#srldate').val(srldate);
	}

	if (localStorage.getItem('srlitems')) {
		loadItems();
	}

    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('srlitems')) {
                    localStorage.removeItem('srlitems');
                }
                if (localStorage.getItem('srldiscount')) {
                    localStorage.removeItem('srldiscount');
                }
                if (localStorage.getItem('srltax2')) {
                    localStorage.removeItem('srltax2');
                }
                if (localStorage.getItem('srlref')) {
                    localStorage.removeItem('srlref');
                }
                if (localStorage.getItem('srlwarehouse')) {
                    localStorage.removeItem('srlwarehouse');
                }
                if (localStorage.getItem('srlnote')) {
                    localStorage.removeItem('srlnote');
                }
                if (localStorage.getItem('srlinnote')) {
                    localStorage.removeItem('srlinnote');
                }
                if (localStorage.getItem('srlcustomer')) {
                    localStorage.removeItem('srlcustomer');
                }
                if (localStorage.getItem('srldate')) {
                    localStorage.removeItem('srldate');
                }
                if (localStorage.getItem('srlbiller')) {
                    localStorage.removeItem('srlbiller');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
	});
	

	$('#srlref').change(function (e) {
		localStorage.setItem('srlref', $(this).val());
	});
	if (srlref = localStorage.getItem('srlref')) {
		$('#srlref').val(srlref);
	}
	$('#srlwarehouse').change(function (e) {
		localStorage.setItem('srlwarehouse', $(this).val());
	});
	if (srlwarehouse = localStorage.getItem('srlwarehouse')) {
		$('#srlwarehouse').select2("val", srlwarehouse);
	}

    $('#srlnote').redactor('destroy');
    $('#srlnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('srlnote', v);
        }
    });
    if (srlnote = localStorage.getItem('srlnote')) {
        $('#srlnote').redactor('set', srlnote);
    }
    $('#srlinnote').redactor('destroy');
    $('#srlinnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('srlinnote', v);
        }
    });
    if (srlinnote = localStorage.getItem('srlinnote')) {
        $('#srlinnote').redactor('set', srlinnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });

    if (site.settings.tax2 != 0) {
        $('#srltax2').change(function () {
            localStorage.setItem('srltax2', $(this).val());
            loadItems();
            return;
        });
    }

    var old_srldiscount;
    $('#srldiscount').focus(function () {
        old_srldiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('srldiscount');
            localStorage.setItem('srldiscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_srldiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });
	$(document).on('click', '.srldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete srlitems[item_id];
        row.remove();
        if(srlitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('srlitems', JSON.stringify(srlitems));
            loadItems();
            return;
        }
    });

     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = srlitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimalRaw(row.children().children('.ruprice').val()),
        discount = row.children().children('.rdiscount').val();
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price)+parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (srlitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }
		
        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

		var bproduct_currency = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.currencies !== false){
            var bproduct_currency = $("<select id=\"pproduct_currency\" name=\"product_currency\" class=\"form-control select\" />");
            $.each(item.currencies, function () {
                if(this.code == item.row.currency_code) {
                    $("<option />", {value: this.code, text: this.name, selected:true}).appendTo(bproduct_currency);
                } else {
                    $("<option />", {value: this.code, text: this.name}).appendTo(bproduct_currency);
                }
            });
        }
        if(item.currencies){
            $('#pproduct_currency-div').html(bproduct_currency);
        }
		
		var total = (net_price+pr_tax_val) * qty;
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
		$('#pro_total').text(formatMoney(total));
		$('#hpro_total').val(total);
        $('#prModal').appendTo("body").modal('show');

    });
	
	$(document).on('change', '#pproduct_currency', function () {
        var row = $('#' + $('#row_id').val()), ccode = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = srlitems[item_id];
		var real_unit_price = item.row.real_unit_price;
		var real_currency_rate = item.row.real_currency_rate;
		
		if(item.currencies !== false) {
            $.each(item.currencies, function () {
                if(this.code == ccode) {
					var pprice = (parseFloat(real_unit_price) / real_currency_rate) * parseFloat(this.rate);
                    $('#pprice').val(pprice).trigger('change');
                }
            });
        }
    });

	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = srlitems[item_id];
		
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#iordered').val(item.row.ordered);
        $('#iordered').select2('val', item.row.ordered);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });

    $(document).on('click', '#editComment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        srlitems[item_id].row.order = parseFloat($('#iorders').val()),
        srlitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('srlitems', JSON.stringify(srlitems));
        $('#cmModal').modal('hide');
        loadItems();
        return;
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });
	
    $(document).on('change', '#pprice, #ptax, #pdiscount, #pquantity', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
		var quantity = parseFloat($('#pquantity').val());
        var item = srlitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }
		
		var total = (unit_price+pr_tax_val) * quantity;
        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
		$('#pro_total').text(formatMoney(total));
		$('#hpro_total').val(total);
    });


	
    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = srlitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(), base_quantity = $('#pquantity').val(), aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
		
		if(unit != srlitems[item_id].row.base_unit) {
            $.each(item.units, function(){
                if (this.id == unit) {
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						$('#pprice').val((this.unit_price - (this.unit_price * ppercent)) + aprice *  (unitToBaseQty(1, this))).change();
					}else{
						$('#pprice').val((item.row.real_unit_price+aprice) * unitToBaseQty(1, this)).change();
					}
				}
            });
        } else {
            $('#pprice').val(formatDecimalRaw(item.row.real_unit_price) + aprice).change();
        }
    });
	
	$(document).on('click', '#calculate_unit_price', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = srlitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var subtotal = parseFloat($('#psubtotal').val()),
        qty = parseFloat($('#pquantity').val());
        $('#pprice').val(formatDecimalRaw((subtotal/qty))).change();
        return false;
    });

    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());		
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != srlitems[item_id].row.base_unit) {
            $.each(srlitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }

		if(item.currencies !== false){
			var currency_code = []; 
			var currency_rate = [];
			var product_currency = $("#pproduct_currency").val()?$("#pproduct_currency").val():null;
			$.each(srlitems[item_id].currencies,function(){
				currency_code[this.code] = this.code;
				currency_rate[this.code] = this.rate;
			});
			srlitems[item_id].row.currency_code = currency_code[product_currency]?currency_code[product_currency]:null;
			srlitems[item_id].row.currency_rate = currency_rate[product_currency]?currency_rate[product_currency]:null;
		}
		
        srlitems[item_id].row.fup = 1,
        srlitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        srlitems[item_id].row.base_quantity = parseFloat(base_quantity),
		srlitems[item_id].row.unit_price = price,
        srlitems[item_id].row.unit = unit,
        srlitems[item_id].row.tax_rate = new_pr_tax,
        srlitems[item_id].tax_rate = new_pr_tax_rate,
        srlitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        srlitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        srlitems[item_id].row.serial = $('#pserial').val();
		localStorage.setItem('srlitems', JSON.stringify(srlitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = srlitems[item_id];
        var unit = $('#punit').val(),  real_unit_price = item.row.real_unit_price;
        if(unit != srlitems[item_id].row.base_unit) {
            $.each(srlitems[item_id].units, function(){
                if (this.id == unit) {
                    real_unit_price = formatDecimalRaw((parseFloat(item.row.real_unit_price)*(unitToBaseQty(1, this))), 4)
                }
            });
        }
        $('#pprice').val(parseFloat(real_unit_price)).trigger('change');
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(real_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

    $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            srlitems = {};
            if ($('#srlwarehouse').val()) {
                $('#srlwarehouse').select2("readonly", true);
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
		add_product = $('#add_product').val(),
        mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            srlitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname,"add_product": add_product, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, 'units': false, "options":false, 'product_expiries': false};
            localStorage.setItem('srlitems', JSON.stringify(srlitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
		$('#mcost').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
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


    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
		base_qty = new_qty,
        item_id = row.attr('data-item-id');
        srlitems[item_id].row.base_quantity = new_qty;
        if(srlitems[item_id].row.unit != srlitems[item_id].row.base_unit) {
            $.each(srlitems[item_id].units, function(){
                if (this.id == srlitems[item_id].row.unit) {
					base_qty = unitToBaseQty(new_qty, this);
                    srlitems[item_id].row.base_quantity = base_qty;
                }
            });  
        }  
        srlitems[item_id].row.qty = new_qty;
        localStorage.setItem('srlitems', JSON.stringify(srlitems));
        loadItems();
    });    

	$(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = srlitems[item_id];
		var qty = item.row.qty;
		var new_unit = parseFloat($(this).val());
		var base_quantity = qty;
		if(new_unit != item.row.base_unit) {
            $.each(item.units, function(){
                if (this.id == new_unit) {
                    base_quantity = unitToBaseQty(qty, this);
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						unit_price = this.unit_price - (this.unit_price * ppercent);
					}else{
						unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
					}
					
				}
            });
        }else{
			unit_price = item.row.real_unit_price;
		}
		srlitems[item_id].row.base_quantity = base_quantity;
		srlitems[item_id].row.unit_price = unit_price;
		srlitems[item_id].row.unit = new_unit;
		localStorage.setItem('srlitems', JSON.stringify(srlitems));
        loadItems();
    });
	
	
	var old_foc;
    $(document).on("focus", '.foc', function () {
        old_foc = $(this).val();
    }).on("change", '.foc', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_foc);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var new_foc = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        srlitems[item_id].row.foc = new_foc;
        localStorage.setItem('srlitems', JSON.stringify(srlitems));
        loadItems();
    });
    
    var old_price;
    $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }        
        var new_price = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        srlitems[item_id].row.price = new_price;
        localStorage.setItem('srlitems', JSON.stringify(srlitems));
        loadItems();
    });          
                 
                 
});              

	function nsCustomer() {
		$('#srlcustomer').select2({
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
						return {results: data.results};
					} else {
						return {results: [{id: '', text: 'No Match Found'}]};
					}
				}    
			}        
		});          
	}       

	function loadItems() {
		if (localStorage.getItem('srlitems')) {
			total = 0;
			count = 1;
			an = 1;  
			product_tax = 0;
			invoice_tax = 0;
			product_discount = 0;
			order_discount = 0;
			total_discount = 0;
			var t_quantity = 0;
			var total_foc = 0;
			$("#srlTable tbody").empty();
			srlitems = JSON.parse(localStorage.getItem('srlitems'));
			sortedItems = (site.settings.item_addition == 1) ? _.sortBy(srlitems, function(o){  return [parseInt(o.order)];}) :   srlitems;
			$('#add_sale_return, #edit_sale_return, #add_sale_return_next').attr('disabled', false);
			$.each(sortedItems, function () {
				var item = this;
				var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
					item.order = item.order ? item.order : new Date().getTime();
				var product_id = item.row.id, item_type = item.row.type, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code,  item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
				var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
				var unit_price = item.row.unit_price;
				var item_comment = item.row.comment ? item.row.comment : '';
				var add_product = (item.row.add_product)?item.row.add_product:0;
				if(item.row.fup != 1 && product_unit != item.row.base_unit) {
					$.each(item.units, function(){
						if (this.id == product_unit) {
							base_quantity = unitToBaseQty(item.row.qty, this);
							if(this.unit_price != null && this.unit_price > 0){
								var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
								unit_price = this.unit_price - (this.unit_price * ppercent);
							}else{
								unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
							}
						}
					});
				}    
				if(item.options !== false) {
					$.each(item.options, function () {
						if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
							item_price = unit_price+(parseFloat(this.price));
							unit_price = item_price;
						}
					});
				}
				
				var ds = item_ds ? item_ds : '0';
				if (ds.indexOf("%") !== -1) {
					var pds = ds.split("%");
					if (!isNaN(pds[0])) {
						item_discount = formatDecimalRaw((((unit_price) * parseFloat(pds[0])) / 100), 4);
					} else {
						item_discount = formatDecimalRaw(ds);
					}
				} else {
					 item_discount = formatDecimalRaw(ds);
				}    
					 
				if(item_discount>0){
					var item_discount_percent = '('+formatDecimalRaw((item_discount * 100)/unit_price)+'%)';
				}else{
					var item_discount_percent = '';
				}    
				product_discount += parseFloat(item_discount * item_qty);
				unit_price = formatDecimalRaw(unit_price-item_discount);
				var pr_tax = item.tax_rate;
				var pr_tax_val = 0, pr_tax_rate = 0;
				if (site.settings.tax1 == 1) {
					if (pr_tax !== false) {
						if (pr_tax.type == 1) {
					 
							if (item_tax_method == '0') {
								pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
								pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
							} else {
								pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
								pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
							}
					 
						} else if (pr_tax.type == 2) {
					 
							pr_tax_val = parseFloat(pr_tax.rate);
							pr_tax_rate = pr_tax.rate;
					 
						}
						product_tax += pr_tax_val * item_qty;
					}
				}    
	  
				item_price = item_tax_method == 0 ? formatDecimalRaw(unit_price-pr_tax_val, 4) : formatDecimalRaw(unit_price);
				unit_price = formatDecimalRaw(unit_price+item_discount, 4);
				var sel_opt = '';
				$.each(item.options, function () {
					if(this.id == item_option) {
						sel_opt = this.name;
					}
				});  
					 
				var row_no = item_id;
				var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
				var label_name = item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '');
				tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + label_name +'</span><i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i><input type="hidden" name="add_product[]" value="'+add_product+'"/> &nbsp;</td>';
				if(item.currencies !== false){
					tr_html += '<input type="hidden" class="currency_rate" name="currency_rate[]"  value="' + item.row.currency_rate + '"/>';
					tr_html += '<input type="hidden" class="currency_code" name="currency_code[]"  value="' + item.row.currency_code + '"/>';
				}
				if (site.settings.product_expiry == 1) {
					tr_html += '<td><input class="form-control date rexpired" name="expired_data[]" type="text" value="' + (item.row.expired ? item.row.expired : '') + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
				}  
				tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + formatDecimalRaw(item.row.real_unit_price) + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
				tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
				if (site.settings.show_unit == 1) {
					uopt = $("<select name=\"sunit\" class=\"form-control sunit select\" />");
					$.each(item.units, function () {
						if(this.id == item.row.unit) {
							$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
						} else {
							$("<option />", {value: this.id, text: this.name}).appendTo(uopt);
						}
					});
					tr_html +='<td>'+(uopt.get(0).outerHTML)+'</td>';
				}
				if (site.settings.foc == 1) {
					tr_html += '<td class="text-center"><input name="foc[]" class="form-control text-center foc" value="'+(item.row.foc > 0 ? item.row.foc : 0)+'"/></td>';
					if(item.row.foc > 0){
						total_foc += parseFloat(item.row.foc);
					}
				}
				if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
					tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + item_discount_percent+'</span></td>';
				}    
				if (site.settings.tax1 == 1) {
					tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (parseFloat(pr_tax_rate) != 0 ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
				}
				tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
				tr_html += '<td class="text-center"><i class="fa fa-times tip pointer srldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
				tr_html += '<input type="hidden" name="item_note[]" value="'+item.row.item_note+'" />';
				newTr.html(tr_html);
				newTr.prependTo("#srlTable");
				$('select').select2();
				var currency_rate = (item.row.currency_rate?item.row.currency_rate:1);
				total += formatDecimalRaw(((parseFloat(item_price / currency_rate) + parseFloat(pr_tax_val / currency_rate)) * parseFloat(item_qty)), 4);
				count += parseFloat(item_qty);
				an++;
				t_quantity += base_quantity;
				if(parseFloat(base_quantity) > parseFloat(item_aqty)) {	
					$('#row_' + row_no).addClass('danger');
					$('#add_sale_return, #edit_sale_return, #add_sale_return_next').attr('disabled', true);
				}
			});      
			var col = 2;
			if (site.settings.product_expiry == 1) { col++; }
			var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
			if (site.settings.show_unit == 1) { 
				tfoot += '<th></th>';	
			}
			if (site.settings.foc == 1) { 
				tfoot += '<th class="text-right">'+formatNumber(total_foc)+'</th>';	
			}
			if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
				tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
			}        
			if (site.settings.tax1 == 1) {
				tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
			}        
			tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
			$('#srlTable tfoot').html(tfoot);
			if (srldiscount = localStorage.getItem('srldiscount')) {
				var ds = srldiscount;
				if (ds.indexOf("%") !== -1) {
					var pds = ds.split("%");
					if (!isNaN(pds[0])) {
						order_discount = formatDecimalRaw((((total) * parseFloat(pds[0])) / 100), 4);
					} else {
						order_discount = formatDecimalRaw(ds);
					}
				} else {
					order_discount = formatDecimalRaw(ds);
				}    
			}        
			if (site.settings.tax2 != 0) {
				if (srltax2 = localStorage.getItem('srltax2')) {
					$.each(tax_rates, function () {
						if (this.id == srltax2) {
							if (this.type == 2) {
								invoice_tax = formatDecimalRaw(this.rate);
							} else if (this.type == 1) {
								invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100), 4);
							}
						}
					});
				}    
			}        
			total_discount = parseFloat(order_discount + product_discount);
			var gtotal = parseFloat((total + invoice_tax) - order_discount);
			$('#total').text(formatMoney(total));
			$('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
			$('#total_items').val((parseFloat(count) - 1));
			$('#tds').text(formatMoney(order_discount));
			if (site.settings.tax2 != 0) {
				$('#ttax2').text(formatMoney(invoice_tax));
			}        
			$('#gtotal').text(formatMoney(gtotal));
			$('#g_total').val(gtotal);
			if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
				$("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
				$(window).scrollTop($(window).scrollTop() + 1);
			}        
			if (count > 1) {
				$('#srlwarehouse').select2("readonly", true);
			}else{
				$('#srlwarehouse').select2("readonly", false);
			}       
			set_page_focus();
		}            
	}                
                 
	function add_invoice_item(item) {
		if (count == 1) {
			srlitems = {};
			if ($('#srlwarehouse').val()) {
				$('#srlwarehouse').select2("readonly", true);
			} else { 
				bootbox.alert(lang.select_above);
				item = null;
				return;
			}        
		}            
		if (item == null) return;  
		var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
		if (srlitems[item_id]) {
			var new_qty = parseFloat(srlitems[item_id].row.qty) + 1;
			srlitems[item_id].row.base_quantity = new_qty;
			if(srlitems[item_id].row.unit != srlitems[item_id].row.base_unit) {
				$.each(srlitems[item_id].units, function(){
					if (this.id == srlitems[item_id].row.unit) {
						srlitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
					}
				});  
			}        
			srlitems[item_id].row.qty = new_qty;
		} else {     
			srlitems[item_id] = item;
		}            
		srlitems[item_id].order = new Date().getTime();
		localStorage.setItem('srlitems', JSON.stringify(srlitems));
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