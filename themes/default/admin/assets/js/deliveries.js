$(document).ready(function () {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    if (dediscount = localStorage.getItem('dediscount')) {
        $('#dediscount').val(dediscount);
    }
    $('#detax2').change(function (e) {
        localStorage.setItem('detax2', $(this).val());
        $('#detax2').val($(this).val());
    });
    if (detax2 = localStorage.getItem('detax2')) {
        $('#detax2').select2("val", detax2);
    }
    $('#dereceived_phone').change(function (e) {
        localStorage.setItem('dereceived_phone', $(this).val());
        $('#dereceived_phone').val($(this).val());
    });
    if (dereceived_phone = localStorage.getItem('dereceived_phone')) {
        $('#dereceived_phone').val(dereceived_phone);
    }
    var old_shipping;
    $('#deshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('deshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (deshipping = localStorage.getItem('deshipping')) {
        shipping = parseFloat(deshipping);
        $('#deshipping').val(shipping);
    } else {
        shipping = 0;
    }
    if (localStorage.getItem('deitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('deitems')) {
                    localStorage.removeItem('deitems');
                }
                if (localStorage.getItem('dediscount')) {
                    localStorage.removeItem('dediscount');
                }
                if (localStorage.getItem('detax2')) {
                    localStorage.removeItem('detax2');
                }
                if (localStorage.getItem('deshipping')) {
                    localStorage.removeItem('deshipping');
                }
                if (localStorage.getItem('deref')) {
                    localStorage.removeItem('deref');
                }
                if (localStorage.getItem('dewarehouse')) {
                    localStorage.removeItem('dewarehouse');
                }
                if (localStorage.getItem('denote')) {
                    localStorage.removeItem('denote');
                }
                if (localStorage.getItem('deaddress')) {
                    localStorage.removeItem('deaddress');
                }
                if (localStorage.getItem('dereceived_phone')) {
                    localStorage.removeItem('dereceived_phone');
                }
                if (localStorage.getItem('deinnote')) {
                    localStorage.removeItem('deinnote');
                }
                if (localStorage.getItem('decustomer')) {
                    localStorage.removeItem('decustomer');
                }
                if (localStorage.getItem('decurrency')) {
                    localStorage.removeItem('decurrency');
                }
                if (localStorage.getItem('dedate')) {
                    localStorage.removeItem('dedate');
                }
                if (localStorage.getItem('debiller')) {
                    localStorage.removeItem('debiller');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    });
    $('#deref').change(function (e) {
        localStorage.setItem('deref', $(this).val());
    });
    if (deref = localStorage.getItem('deref')) {
        $('#deref').val(deref);
    }
    $('#dewarehouse').change(function (e) {
        localStorage.setItem('dewarehouse', $(this).val());
    });
    if (dewarehouse = localStorage.getItem('dewarehouse')) {
        $('#dewarehouse').select2("val", dewarehouse);
    }
    $('#denote').redactor('destroy');
    $('#denote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('denote', v);
        }
    });
    if (denote = localStorage.getItem('denote')) {
        $('#denote').redactor('set', denote);
    }
    $('#deaddress').redactor('destroy');
    $('#deaddress').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('deaddress', v);
        }
    });
    if (deaddress = localStorage.getItem('deaddress')) {
        $('#deaddress').redactor('set', deaddress);
    }
    var $customer = $('#decustomer');
    $customer.change(function (e) {
        localStorage.setItem('decustomer', $(this).val());
    });
    if (decustomer = localStorage.getItem('decustomer')) {
        $customer.val(decustomer).select2({
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
                deietMillis: 15,
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
    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
	$(document).on('click', '.dedel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete deitems[item_id];
		row.remove();
		if(deitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('deitems', JSON.stringify(deitems));
			loadItems();
			return;
		}
	});
});

$(document).on("change", '.rexpired', function () {
	var new_expired = $(this).val();
	var item_id = $(this).closest('tr').attr('data-item-id');
	deitems[item_id].row.expired = new_expired;
	localStorage.setItem('deitems', JSON.stringify(deitems));
	loadItems();
});

var old_row_qty;
$(document).on("focus", '.rquantity', function () {
	old_row_qty = $(this).val();
}).on("change", '.rquantity', function () {
	var row = $(this).closest('tr');
	if (!is_numeric($(this).val()) || parseFloat($(this).val()) <= 0) {
		$(this).val(old_row_qty);
		bootbox.alert(lang.unexpected_value);
		return;
	}
	var new_qty = parseFloat($(this).val()),
	base_qty = new_qty,
	item_id = row.attr('data-item-id');
	deitems[item_id].row.base_quantity = new_qty;
	if(deitems[item_id].row.unit != deitems[item_id].row.base_unit) {
		$.each(deitems[item_id].units, function(){
			if (this.id == deitems[item_id].row.unit) {
				base_qty = unitToBaseQty(new_qty, this);
				deitems[item_id].row.base_quantity = base_qty;
			}
		});  
	}  
	deitems[item_id].row.qty = new_qty;
	localStorage.setItem('deitems', JSON.stringify(deitems));
	loadItems();
});


$(document).on('change', '.punit', function () {
	var row     = $(this).closest('tr');
	var item_id = row.attr('data-item-id');	
    var item    = deitems[item_id];	
	var parent  = $(this).parent().parent();
    var opt           = item.row.option,
        unit          = $(this).val(),
        base_quantity = item.row.base_quantity,
        aprice        = 0;

    var price = item.row.unit_price;
    if (item.units && unit != deitems[item_id].row.base_unit) {
        $.each(item.units, function () {
            if (this.id == unit) {
                base_quantity = unitToBaseQty(base_quantity, this);
                price = formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4);
            }
        });
    } else {
        price = formatDecimal(item.row.base_unit_price + aprice);
    }
    if (item.units && unit != deitems[item_id].row.base_unit) {
        $.each(item.units, function () {
            if (this.id == unit) {
                base_quantity = unitToBaseQty(base_quantity, this);
                price         = formatDecimal(((parseFloat(item.row.base_unit_price + aprice)) * unitToBaseQty(1, this)), 4);
            }
        });
    } else {
        price = formatDecimal(item.row.base_unit_price + aprice);
    }
	// deitems[item_id].row.fup = 1;
    // deitems[item_id].row.qty = base_quantity;
    // deitems[item_id].row.base_quantity = parseFloat(base_quantity);
    deitems[item_id].row.real_unit_price = price;
    deitems[item_id].row.unit = unit;
	localStorage.setItem('deitems', JSON.stringify(deitems));
	loadItems();
});

function loadItems() {
    if (localStorage.getItem('deitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $("#deTable tbody").empty();
        deitems     = JSON.parse(localStorage.getItem('deitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(deitems, function(o){return [parseInt(o.order)];}) : deitems;
        console.log(sortedItems);
        $('#add_delivery, #edit_delivery').attr('disabled', false);
        $.each(sortedItems, function () {
            var item               = this;
            var item_id            = site.settings.item_addition == 1 ? item.item_id : item.id;
                item.order         = item.order ? item.order : new Date().getTime();
            var product_id         = item.row.id,
                item_type          = item.row.type,
                combo_items        = item.combo_items,
                item_price         = item.row.price,
                item_qty           = item.row.qty,
                item_tax_method    = item.row.tax_method,
                item_ds            = item.row.discount,
                item_discount      = 0,
                item_expiry        = item.row.expiry ? item.row.expiry : '',
                item_option        = item.row.option,
                item_code          = item.row.code,
                item_name          = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;');
            var product_unit       = item.row.unit,
                base_quantity      = item.row.base_quantity;
            var unit_price         = item.row.real_unit_price;
            var real_unit_price    = item.row.real_unit_price;
            var sale_item_id       = item.row.sale_item_id;
            var sale_order_item_id = item.row.sale_order_item_id;
            var arr = JSON.parse(localStorage.getItem('group_price'));
            if ((arr != null) && (arr != undefined) && (arr != "")) {
                if (!(item.free) || (arr != null)) {
                    for (var i = 0; i < arr.length; i++) {
                        var obj = arr[i];
                        for (var key in obj) {
                            if (product_id === obj['product_id'] && base_quantity >= obj['qty_from'] && base_quantity <= obj['qty_to']) {
                                unit_price = obj['price'];
                                item_price = obj['price'];
                                base_unit_price = obj['price'];
                            }
                        }
                    }
                }
            }
            // if (item.units && item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function() {
                    if (this.id == product_unit) {
                        base_quantity   = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price      = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                        real_unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    }
                }); 
            // }           
            var td_unit = $("<select id=\"unit\" name=\"unit\[\]\" class=\"form-control select punit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
					if (this.id == item.row.unit) {
						$("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(td_unit);
						base_quantity = formatDecimalRaw(unitToBaseQty(item.row.qty, this), 4);
					}else{
						$("<option />", {value: this.id, text: this.name}).appendTo(td_unit);
					}
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(td_unit);
                td_unit = td_unit.hide();
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
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
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
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
			var qoh = item.row.quantity;
			if (site.settings.product_expiry == 1) {
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
			}
			var row_no  = item_id;
			var newTr   = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			    tr_html = '<td><input name="sale_item_id[]" type="hidden" class="rs_item_id" value="' + sale_item_id + '"><input name="sale_order_item_id[]" type="hidden" class="rs_item_id" value="' + sale_order_item_id + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit hidden" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
			if(site.settings.show_qoh == 1){
				tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
			}
			if (site.settings.product_expiry == 1) {
                tr_html += '<td><input type="text" class="form-control input-sm rexpiry" name="expired_data[]" value="' + item_expiry + '" readonly></td>';
			}
			tr_html += '<input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimal(item_price) + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + real_unit_price + '">'; //item.row.real_unit_price
            tr_html += '<td class="text-center"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><span>' + item.row.squantity + '</span></td>';
			tr_html += '<td class="text-center"><input name="serial_no[]" type="hidden" value="' + item.row.serial + '"><span>' + (item.row.dquantity != '' ? item.row.dquantity : 0) + '</span></td>';
			tr_html += '<td class="text-center"><span>' + item.row.balance_unit_qty + '</span></td>';
			tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();" style="background-color:#fff;" /></td>';
			tr_html += '<td><input type="hidden" value="' + item.row.sale_qty + '" name="sale_qty[]"/><input type="hidden" value="' + item.row.balanace_qty + '" name="balanace_qty[]" class="balanace_qty"/><input type="hidden" value="' + base_quantity + '" name="base_quantity[]" class="base_quantity"/>' + (td_unit.get(0).outerHTML) + '</td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip pointer dedel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			tr_html += '<input type="hidden" name="parent_id[]" value="' + item.row.parent_id + '" />';
			newTr.html(tr_html);
            newTr.prependTo("#deTable");
			$('select').select2();
            total += parseFloat(base_quantity);
            an++;
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
						$('#add_delivery, #edit_delivery').attr('disabled', true); 
                    }
                });
            } else if(site.settings.product_expiry == 1 && item.product_expiries){
				if(base_quantity > qoh || base_quantity > item.row.quantity || base_quantity > item.row.balanace_qty){
					$('#row_' + row_no).addClass('danger');
				}
				if(item_type == 'standard' && base_quantity > item.row.quantity || base_quantity > item.row.balanace_qty){
					$('#add_delivery, #edit_delivery').attr('disabled', true); 
				}
			} else if(item_type == 'standard' && base_quantity > item.row.balanace_qty) {
                $('#row_' + row_no).addClass('danger');
				$('#add_delivery, #edit_delivery').attr('disabled', true);
            } else if(item_type == 'standard' && base_quantity > item.row.quantity){
				$('#row_' + row_no).addClass('danger');
				if(site.settings.overselling != 1) { $('#add_delivery, #edit_delivery').attr('disabled', true); }
			} else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
					$('#add_delivery, #edit_delivery').attr('disabled', true);
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
						   $('#add_delivery, #edit_delivery').attr('disabled', true);
                       }
                   });
                }
            }
        });
        var col = 4;
		if (site.settings.product_expiry == 1) { col++; }
		if (site.settings.show_qoh == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(total)) + '</th>';
            tfoot += '<th></th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#deTable tfoot').html(tfoot);
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Quotation Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {
    if (count == 1) {
        deitems = {};
        if ($('#dewarehouse').val() && $('#decustomer').val()) {
            $('#decustomer').select2("readonly", true);
            $('#dewarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (deitems[item_id]) {
        var new_qty = parseFloat(deitems[item_id].row.qty) + 1;
        deitems[item_id].row.base_quantity = new_qty;
        if(deitems[item_id].row.unit != deitems[item_id].row.base_unit) {
            $.each(deitems[item_id].units, function(){
                if (this.id == deitems[item_id].row.unit) {
                    deitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        deitems[item_id].row.qty = new_qty;

    } else {
        deitems[item_id] = item;
    }
    deitems[item_id].order = new Date().getTime();
    localStorage.setItem('deitems', JSON.stringify(deitems));
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