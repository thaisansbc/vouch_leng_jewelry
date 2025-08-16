$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				clearLS();
				clearLS__();
				$('#modal-loading').show();
				location.reload();
			}
		});
	}); 
    $('#slpayment_status').change(function(e) {
        var ps = $(this).val();
        localStorage.setItem('slpayment_status', ps);
        if (ps == 'booking' || ps == 'partial' || ps == 'paid') {
            if (ps == 'paid' || ps == 'partial') {
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
	$(document).on('change', '#shldate', function (e) {
		localStorage.setItem('shldate', $(this).val());
	});
	if (shldate = localStorage.getItem('shldate')) {
		$('#shldate').val(shldate);
	}
	$(document).on('change', '#shlref', function (e) {
		localStorage.setItem('shlref', $(this).val());
	});
	if (shlref = localStorage.getItem('shlref')) {
		$('#shlref').val(shlref);
	}
	$(document).on('change', '#shlbiller', function (e) {
		localStorage.setItem('shlbiller', $(this).val());
	});
	if (shlbiller = localStorage.getItem('shlbiller')) {
		$('#shlbiller').val(shlbiller);
	}
	$(document).on('change', '#shltax2', function (e) {
		localStorage.setItem('shltax2', $(this).val());
	});
	if (shltax2 = localStorage.getItem('shltax2')) {
		$('#shltax2').val(shltax2);
	}
	$(document).on('change', '#shldiscount', function (e) {
		localStorage.setItem('shldiscount', $(this).val());
	});
	if (shldiscount = localStorage.getItem('shldiscount')) {
		$('#shldiscount').val(shldiscount);
	}
	$(document).on('change', '#shlpayment_term', function (e) {
		localStorage.setItem('shlpayment_term', $(this).val());
	});
	if (shlpayment_term = localStorage.getItem('shlpayment_term')) {
		$('#shlpayment_term').val(shlpayment_term);
	}
	
	$(document).on('change', '#shlacademic_year', function (e) {
		localStorage.setItem('shlacademic_year', $(this).val());
	});
	if (shlacademic_year = localStorage.getItem('shlacademic_year')) {
		$('#shlacademic_year').val(shlacademic_year);
	}
	
	$(document).on('change', '#shlprogram', function (e) {
		localStorage.setItem('shlprogram', $(this).val());
	});
	if (shlprogram = localStorage.getItem('shlprogram')) {
		$('#shlprogram').val(shlprogram);
	}
	
	$(document).on('change', '#shlgrade', function (e) {
		localStorage.setItem('shlgrade', $(this).val());
	});
	if (shlgrade = localStorage.getItem('shlgrade')) {
		$('#shlgrade').val(shlgrade);
	}
	
	$(document).on('change', '#slhchild_no', function (e) {
		localStorage.setItem('slhchild_no', $(this).val());
	});
	if (slhchild_no = localStorage.getItem('slhchild_no')) {
		$('#slhchild_no').val(slhchild_no);
	}
	
	$(document).on('change', '#slhfee_type', function (e) {
		localStorage.setItem('slhfee_type', $(this).val());
	});
	if (slhfee_type = localStorage.getItem('slhfee_type')) {
		$('#slhfee_type').val(slhfee_type);
	}
	
	$('#shlnote').redactor('destroy');
	$('#shlnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('shlnote', v);
		}
	});
	if (shlnote = localStorage.getItem('shlnote')) {
		$('#shlnote').redactor('set', shlnote);
	}
	$('#shlinnote').redactor('destroy');
	$('#shlinnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('shlinnote', v);
		}
	});
	if (shlinnote = localStorage.getItem('shlinnote')) {
		$('#shlinnote').redactor('set', shlinnote);
	}
	$(document).on('change', '#shlcustomer', function (e) {
		localStorage.setItem('shlcustomer', $(this).val());
	});
	if (shlcustomer = localStorage.getItem('shlcustomer')) {
		$('#shlcustomer').val(shlcustomer).select2({
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
	//------student-------
	$(document).on('change', '#shlstudent', function (e) {
		localStorage.setItem('shlstudent', $(this).val());
	});
	if (shlstudent = localStorage.getItem('shlstudent')) {
		$('#shlstudent').val(shlstudent).select2({
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
	//-----close student----
	if (localStorage.getItem('shlitems')) {
		loadItems();
	}  
	$('select').select2();
	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = shlitems[item_id];
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });
	$(document).on('click', '#editComment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        shlitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('shlitems', JSON.stringify(shlitems));
        $('#cmModal').modal('hide');
        loadItems();
        return;
    });
	$(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = shlitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        unit_price = formatDecimalRaw(row.children().children('.ruprice').val()),
        discount = row.children().children('.rdiscount').val();
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
                            if (shlitems[item_id].row.tax_method == 0) {
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
        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });
		var total = (net_price+pr_tax_val) * qty;
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
		$('#pro_total').text(formatMoney(total));
		$('#hpro_total').val(total);
        $('#prModal').appendTo("body").modal('show');
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
        if(unit != shlitems[item_id].row.base_unit) {
            $.each(shlitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        shlitems[item_id].row.fup = 1,
        shlitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        shlitems[item_id].row.base_quantity = parseFloat(base_quantity),
		shlitems[item_id].row.unit_price = price,
        shlitems[item_id].row.unit = unit,
        shlitems[item_id].row.tax_rate = new_pr_tax,
        shlitems[item_id].tax_rate = new_pr_tax_rate,
        shlitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
		localStorage.setItem('shlitems', JSON.stringify(shlitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });
	$(document).on('change', '#punit', function () {
        var row           = $('#' + $('#row_id').val());
        var item_id       = row.attr('data-item-id');
        var item          = shlitems[item_id];

		var unit          = $('#punit').val();
        var opt           = $('#poption').val();
        var base_quantity = $('#pquantity').val();
        var aprice        = 0;
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
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
	
	$(document).on('change', '#pprice, #ptax, #pdiscount, #pquantity', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
		var quantity = parseFloat($('#pquantity').val());
        var item = shlitems[item_id];
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
        shlitems[item_id].row.base_quantity = new_qty;
        if(shlitems[item_id].row.unit != shlitems[item_id].row.base_unit) {
            $.each(shlitems[item_id].units, function(){
                if (this.id == shlitems[item_id].row.unit) {
					base_qty = unitToBaseQty(new_qty, this);
                    shlitems[item_id].row.base_quantity = base_qty;
                }
            });  
        }  
        shlitems[item_id].row.qty = new_qty;
        localStorage.setItem('shlitems', JSON.stringify(shlitems));
        loadItems();
    });
	$(document).on('click', '.sldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete shlitems[item_id];
        row.remove();
        if(shlitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('shlitems', JSON.stringify(shlitems));
            loadItems();
            return;
        }
    });
	var old_sldiscount;
    $('#shldiscount').focus(function () {
        old_sldiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('shldiscount');
            localStorage.setItem('shldiscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_sldiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }
    });
	$('#shltax2').change(function () {
		localStorage.setItem('shltax2', $(this).val());
		loadItems();
		return;
	});
});              
function clearLS(){
	if (localStorage.getItem('shldate')) {
		localStorage.removeItem('shldate');
	}
	if (localStorage.getItem('shlref')) {
		localStorage.removeItem('shlref');
	}
	if (localStorage.getItem('shlbiller')) {
		localStorage.removeItem('shlbiller');
	}
	if (localStorage.getItem('shlstudent')) {
		localStorage.removeItem('shlstudent');
	}
	if (localStorage.getItem('shltax2')) {
		localStorage.removeItem('shltax2');
	}
	if (localStorage.getItem('shldiscount')) {
		localStorage.removeItem('shldiscount');
	}
	if (localStorage.getItem('shlpayment_term')) {
		localStorage.removeItem('shlpayment_term');
	}
	if (localStorage.getItem('shlnote')) {
		localStorage.removeItem('shlnote');
	}
	if (localStorage.getItem('shlinnote')) {
		localStorage.removeItem('shlinnote');
	}
	if (localStorage.getItem('shlitems')) {
		localStorage.removeItem('shlitems');
	}
	if (localStorage.getItem('shlacademic_year')) {
		localStorage.removeItem('shlacademic_year');
	}
	if (localStorage.getItem('shlprogram')) {
		localStorage.removeItem('shlprogram');
	}
	if (localStorage.getItem('shlgrade')) {
		localStorage.removeItem('shlgrade');
	}
	if (localStorage.getItem('shlchild_no')) {
		localStorage.removeItem('shlchild_no');
	}
	if (localStorage.getItem('shlfee_type')) {
		localStorage.removeItem('shlfee_type');
	}
	if (localStorage.getItem('shlfee_type')) {
		localStorage.removeItem('shlfee_type');
	}
	if (localStorage.getItem('shlbatch')) {
		localStorage.removeItem('shlbatch');
	}
	if (localStorage.getItem('slpayment_status')) {
		localStorage.removeItem('slpayment_status');
	}
}
function nsCustomer() {
	$('#shlstudent').select2({
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
   
function loadItems() 
{
    if (localStorage.getItem('shlitems')) {
        total = 0;
        count = 1;
        an = 1;  
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $("#slTable tbody").empty();
        shlitems = JSON.parse(localStorage.getItem('shlitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(shlitems, function(o){  return [parseInt(o.order)];}) :   shlitems;
        $('#add_sale, #edit_sale').attr('disabled', false);

        // console.log(sortedItems);
		$.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id; item.order = item.order ? item.order : new Date().getTime();
			var product_id = item.row.id, item_type = item.row.type, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
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
            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			var label_name = item_code +' - '+ item_name;

			var product_unit_code = item.row.unit_code;
			if(item.units){
                $.each(item.units, function(index, val_item) {
                    if (product_unit == val_item.id) {
                        product_unit_code = val_item.name;
                    }
                });
            }

            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input type="hidden" name="cost[]" value="'+formatDecimalRaw(item.row.cost)+'" /><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><span class="sname" id="name_' + row_no + '">' + label_name +'</span><i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i></td>';
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + formatDecimalRaw(item.row.real_unit_price) + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<td><span id="product_unit_code" class="text-right">' + product_unit_code + '</span></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
			if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + item_discount_percent+'</span></td>';
            }    
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer sldel " id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#slTable");
			$('select').select2();
            total += formatDecimalRaw(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
			if(item_type == 'standard' && base_quantity > item_aqty) {				
				$('#row_' + row_no).addClass('danger');
				if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
			}
        });      
		         
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
		if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }            
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#slTable tfoot').html(tfoot);
        if (shldiscount = localStorage.getItem('shldiscount')) {
            var ds = shldiscount;
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
            if (shltax2 = localStorage.getItem('shltax2')) {
                $.each(tax_rates, function () {
                    if (this.id == shltax2) {
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
        var gtotal = parseFloat(((total + invoice_tax) - order_discount));
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
            $('#shlstudent').select2("readonly", true);
        }else{
			$('#shlstudent').select2("readonly", false);
		}       
        set_page_focus();
    }            
}                
                 
function add_invoice_item(item) {
    if (count == 1) {
        shlitems = {};
        if ($('#shlstudent').val()) {
            $('#shlstudent').select2("readonly", true);
        } else { 
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }        
    }            
    if (item == null){
		return;
	}
          
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (shlitems[item_id]) {
        var new_qty = parseFloat(shlitems[item_id].row.qty) + 1;
        shlitems[item_id].row.base_quantity = new_qty;
        if(shlitems[item_id].row.unit != shlitems[item_id].row.base_unit) {
            $.each(shlitems[item_id].units, function(){
                if (this.id == shlitems[item_id].row.unit) {
                    shlitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });  
        }        
        shlitems[item_id].row.qty = new_qty;
    } else {     
        shlitems[item_id] = item;
    }            
    shlitems[item_id].order = new Date().getTime();
    localStorage.setItem('shlitems', JSON.stringify(shlitems));
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