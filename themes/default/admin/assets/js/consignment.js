$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('csmitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('csmitems')) {
                        localStorage.removeItem('csmitems');
                    }
                    if (localStorage.getItem('csmref')) {
                        localStorage.removeItem('csmref');
                    }
                    if (localStorage.getItem('csmwarehouse')) {
                        localStorage.removeItem('csmwarehouse');
                    }
                    if (localStorage.getItem('csmnote')) {
                        localStorage.removeItem('csmnote');
                    }
                    if (localStorage.getItem('csmcustomer')) {
                        localStorage.removeItem('csmcustomer');
                    }
                    if (localStorage.getItem('csmdate')) {
                        localStorage.removeItem('csmdate');
                    }
                    if (localStorage.getItem('csmbiller')) {
                        localStorage.removeItem('csmbiller');
                    }
                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

    $('#csmref').change(function (e) {
        localStorage.setItem('csmref', $(this).val());
    });
    if (csmref = localStorage.getItem('csmref')) {
        $('#csmref').val(csmref);
    }
    $('#csmwarehouse').change(function (e) {
        localStorage.setItem('csmwarehouse', $(this).val());
    });
    if (csmwarehouse = localStorage.getItem('csmwarehouse')) {
        $('#csmwarehouse').select2("val", csmwarehouse);
    }

    $('#csmnote').redactor('destroy');
    $('#csmnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('csmnote', v);
        }
    });
    if (csmnote = localStorage.getItem('csmnote')) {
        $('#csmnote').redactor('set', csmnote);
    }
    var $customer = $('#csmcustomer');
    $customer.change(function (e) {
        localStorage.setItem('csmcustomer', $(this).val());
    });
    if (csmcustomer = localStorage.getItem('csmcustomer')) {
        $customer.val(csmcustomer).select2({
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

    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });


	$(document).on('click', '.csmdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete csmitems[item_id];
		row.remove();
		if(csmitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('csmitems', JSON.stringify(csmitems));
			loadItems();
			return;
		}
	});


     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = csmitems[item_id];
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
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
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
		if(item.product_serials){
			uopt1 = $("<select class='form-control input-tip select' data-placeholder='Select Serial' id='product_serial' name='product_serial'  />");
			$("<option />", { value: '', text: ''}).appendTo(uopt1);		
			$.each(item.product_serials, function () {
				var serialno = row.children().children('.rserial').val();
				if(this.serial == serialno) {
					$("<option />", { value: this.id, text: this.serial, selected:true}).appendTo(uopt1);
				} else {
					$("<option />", { value: this.id, text: this.serial}).appendTo(uopt1);
				}
			});			
			$("#pserials-div").html(uopt1);
		}

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#net_price').text(formatMoney(net_price));
        $('#prModal').appendTo("body").modal('show');

    });
	
	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = csmitems[item_id];
		
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#iordered').val(item.row.ordered);
        $('#iordered').select2('val', item.row.ordered);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });
	
	$(document).on('change', '#product_serial', function () {
		var row = $('#' + $('#row_id').val());
		var item_id = row.attr('data-item-id');
		var item = csmitems[item_id];
		var serialno = [];
		$.each(item.product_serials, function(){
			serialno[this.id] = this.serial;
		});
		$('#pserial').val(serialno[$(this).val()]);

	});

    $(document).on('click', '#editComment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        csmitems[item_id].row.order = parseFloat($('#iorders').val()),
        csmitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('csmitems', JSON.stringify(csmitems));
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

    $(document).on('change', '#pprice', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = csmitems[item_id];
        $('#net_price').text(formatMoney(unit_price));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = csmitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(),  aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if(unit != csmitems[item_id].row.base_unit) {
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
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var price = parseFloat($('#pprice').val());
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != csmitems[item_id].row.base_unit) {
            $.each(csmitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
		if (site.settings.product_serial == 1) {
			csmitems[item_id].row.cost = parseFloat($('#pscost').val());
		}
        csmitems[item_id].row.fup = 1,
        csmitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        csmitems[item_id].row.base_quantity = parseFloat(base_quantity),
		csmitems[item_id].row.unit_price = price,
        csmitems[item_id].row.unit = unit,
        csmitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
		csmitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('csmitems', JSON.stringify(csmitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

     $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = csmitems[item_id];
        var unit = $('#punit').val(), real_unit_price = item.row.real_unit_price;
        if(unit != csmitems[item_id].row.base_unit) {
            $.each(csmitems[item_id].units, function(){
                if (this.id == unit) {
                    real_unit_price = formatDecimalRaw((parseFloat(item.row.real_unit_price)*(unitToBaseQty(1, this))), 4)
                }
            });
        }
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
            csmitems = {};
            if ($('#csmwarehouse').val() && $('#csmcustomer').val()) {
                $('#csmcustomer').select2("readonly", true);
                $('#csmwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

     $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
        mcode = $('#mcode').val(),
        mname = $('#mname').val(),
        mqty = parseFloat($('#mquantity').val()),
        unit_price = parseFloat($('#mprice').val());
        if (mcode && mname && mqty && unit_price) {
            csmitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, "options":false};
            localStorage.setItem('csmitems', JSON.stringify(csmitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mquantity').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice', function () {
        var unit_price = parseFloat($('#mprice').val());
        $('#mnet_price').text(formatMoney(unit_price));
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
        item_id = row.attr('data-item-id');
        csmitems[item_id].row.base_quantity = new_qty;
        if(csmitems[item_id].row.unit != csmitems[item_id].row.base_unit) {
            $.each(csmitems[item_id].units, function(){
                if (this.id == csmitems[item_id].row.unit) {
                    csmitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        csmitems[item_id].row.qty = new_qty;
        localStorage.setItem('csmitems', JSON.stringify(csmitems));
        loadItems();
    });
	
	$(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = csmitems[item_id];
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
		csmitems[item_id].row.base_quantity = base_quantity;
		csmitems[item_id].row.unit_price = unit_price;
		csmitems[item_id].row.unit = new_unit;
		localStorage.setItem('csmitems', JSON.stringify(csmitems));
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
        csmitems[item_id].row.price = new_price;
        localStorage.setItem('csmitems', JSON.stringify(csmitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
       $('#csmcustomer').select2('readonly', false);
       return false;
    });
	
	$(document).on("change", '.rexpired', function () {
		var new_expired = $(this).val();
		var item_id = $(this).closest('tr').attr('data-item-id');
		csmitems[item_id].row.expired = new_expired;
		localStorage.setItem('csmitems', JSON.stringify(csmitems));
		loadItems();
	});


});

function nsCustomer() {
    $('#csmcustomer').select2({
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
    if (localStorage.getItem('csmitems')) {
        total = 0;
        count = 1;
        an = 1;
        $("#csmTable tbody").empty();
        csmitems = JSON.parse(localStorage.getItem('csmitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(csmitems, function(o){return [parseInt(o.order)];}) :   csmitems;
        $('#add_sale, #edit_sale, #add_quote_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_serial = item.row.serial, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_option = item.row.option, item_code = item.row.code,  item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_price = item.row.unit_price;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var item_comment = item.row.comment ? item.row.comment : '';
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
			
			var qoh = item_aqty;  
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
			
            if(item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = unit_price+(parseFloat(this.price));
                        unit_price = item_price;
                    }
                });
            }
            unit_price = formatDecimalRaw(unit_price);
            item_price = unit_price;
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i></td>';
			if(site.settings.show_qoh == 1){
				tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
			}
			if (site.settings.product_expiry == 1) {
                tr_html += '<td>'+expiry_select+'</td>';
            }
			if (site.settings.product_serial == 1) {
                tr_html += '<td  class="text-right"><input readonly="true" class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }
			tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimalRaw(item_price) + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
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
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney((parseFloat(item_price)) * parseFloat(item_qty)) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer csmdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';

			newTr.html(tr_html);
            newTr.prependTo("#csmTable");
			$('select').select2();
            total += formatDecimalRaw(parseFloat(item_price) * parseFloat(item_qty));
            count += parseFloat(item_qty);
            an++;
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                    }
                });
            } else if(item_type == 'standard' && site.settings.product_expiry == 1 && item.product_expiries){
				base_quantity = base_quantity - 0;
				item_aqty = item_aqty - 0;
				if(base_quantity > qoh || base_quantity > item_aqty){
					$('#row_' + row_no).addClass('danger');
					if(site.settings.overselling != 1) { $('#add_consignment, #add_consignment_next, #edit_consignment').attr('disabled', true); }
				}
			} else if(item_type == 'standard' && base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
            } else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
                       }
                   });
                }
            }

        });
        var col = 2;
		if (site.settings.product_expiry == 1) { col++; }
		if (site.settings.product_serial == 1) { col++; }
		if (site.settings.show_qoh == 1) { col += 1 }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.show_unit == 1) { 
			tfoot += '<th></th>';	
		}
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#csmTable tfoot').html(tfoot);

        // Totals calculations after item addition
        var gtotal = parseFloat(total);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

function add_invoice_item(item) {
    if (count == 1) {
        csmitems = {};
        if ($('#csmwarehouse').val() && $('#csmcustomer').val()) {
            $('#csmcustomer').select2("readonly", true);
            $('#csmwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (csmitems[item_id]) {
        var new_qty = parseFloat(csmitems[item_id].row.qty) + 1;
        csmitems[item_id].row.base_quantity = new_qty;
        if(csmitems[item_id].row.unit != csmitems[item_id].row.base_unit) {
            $.each(csmitems[item_id].units, function(){
                if (this.id == csmitems[item_id].row.unit) {
                    csmitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        csmitems[item_id].row.qty = new_qty;

    } else {
        csmitems[item_id] = item;
    }
    csmitems[item_id].order = new Date().getTime();
    localStorage.setItem('csmitems', JSON.stringify(csmitems));
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
