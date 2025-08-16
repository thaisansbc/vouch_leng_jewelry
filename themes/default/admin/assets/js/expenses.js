$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
    if (expref = localStorage.getItem('expref')) {
        $('#expref').val(expref);
    }
	if (exdiscount = localStorage.getItem('exdiscount')) {
		$('#exdiscount').val(exdiscount);
	}
    $('#exptax2').change(function (e) {
        localStorage.setItem('exptax2', $(this).val());
        $('#exptax2').val($(this).val());
    });
    if (exptax2 = localStorage.getItem('exptax2')) {
        $('#exptax2').select2("val", exptax2);
    }
	if (expproject = localStorage.getItem('expproject')) {
        $('#project').select2("val", expproject);
    }
	if (exproom = localStorage.getItem('exproom')) {
        $('#room').select2("val", exproom);
    }
	
	if (expvehicle = localStorage.getItem('expvehicle')) {
        $('#vehicle').select2("val", expvehicle);
    }
	
	if (exppayable_account = localStorage.getItem('exppayable_account')) {
        $('#payable_account').select2("val", exppayable_account);
    }
	
	if (exppaying_from = localStorage.getItem('exppaying_from')) {
        $('#paying_from').select2("val", exppaying_from);
    }
	
    $('#expsupplier').change(function (e) {
        localStorage.setItem('expsupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if ((expsupplier = localStorage.getItem('expsupplier')) && expsupplier != 0) {
        $('#expsupplier').val(expsupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
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
        nsSupplier();
    }

    if (localStorage.getItem('expitems')){
        loadItems();
    }
    $('#expref').change(function (e) {
        localStorage.setItem('expref', $(this).val());
    });
	$('#project').change(function (e) {
        localStorage.setItem('expproject', $(this).val());
    });
	$('#room').change(function (e) {
        localStorage.setItem('exproom', $(this).val());
    });
	
	$('#vehicle').change(function (e) {
        localStorage.setItem('expvehicle', $(this).val());
    });
	$('#payable_account').change(function (e) {
        localStorage.setItem('exppayable_account', $(this).val());
    });
	$('#paying_from').change(function (e) {
        localStorage.setItem('exppaying_from', $(this).val());
    });

	
    if (expref = localStorage.getItem('expref')) {
        $('#expref').val(expref);
    }
    $('#expwarehouse').change(function (e) {
        localStorage.setItem('expwarehouse', $(this).val());
    });
    if (expwarehouse = localStorage.getItem('expwarehouse')) {
        $('#expwarehouse').select2("val", expwarehouse);
    }

    $('#expnote').redactor('destroy');
    $('#expnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('expnote', v);
        }
    });
    if (expnote = localStorage.getItem('expnote')) {
        $('#expnote').redactor('set', expnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
	if (site.settings.tax2 != 0) {
		$('#exptax2').change(function () {
			localStorage.setItem('exptax2', $(this).val());
			loadItems();
			return;
		});
	}
	var old_podiscount;
	$('#exdiscount').focus(function () {
		exdiscount = $(this).val();
	}).change(function () {
		if (is_valid_discount($(this).val())) {
			localStorage.removeItem('exdiscount');
			localStorage.setItem('exdiscount', $(this).val());
			loadItems();
			return;
		} else {
			$(this).val(exdiscount);
			bootbox.alert(lang.unexpected_value);
			return;
		}

	});
	$(document).on('click', '.expdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete expitems[item_id];
		row.remove();
		if(expitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('expitems', JSON.stringify(expitems));
			loadItems();
			return;
		}
	});
	
	
	
	
	$('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				removeLS();
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
	
    var old_quantity;
    $(document).on("focus", '.quantity', function () {
        old_quantity = $(this).val();
    }).on("change", '.quantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_quantity);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        expitems[item_id].row.quantity = new_qty;
        localStorage.setItem('expitems', JSON.stringify(expitems));
        loadItems();
    });
	

    $(document).on("change", '.description', function () {
        var row = $(this).closest('tr');
        var description = $(this).val(),
        item_id = row.attr('data-item-id');
        expitems[item_id].row.description = description;
        localStorage.setItem('expitems', JSON.stringify(expitems));
        loadItems();
    });


    var old_unit_cost;
    $(document).on("focus", '.unit_cost', function () {
        old_unit_cost = $(this).val();
    }).on("change", '.unit_cost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_unit_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_cost = parseFloat($(this).val());
		item_id = row.attr('data-item-id');
        expitems[item_id].row.unit_cost = new_cost;
        localStorage.setItem('expitems', JSON.stringify(expitems));
        loadItems();
    });



});


function nsSupplier() {
    $('#expsupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
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

function removeLS(){
	if (localStorage.getItem('expitems')) {
		localStorage.removeItem('expitems');
	}
	if (localStorage.getItem('exptax2')) {
		localStorage.removeItem('exptax2');
	}
	if (localStorage.getItem('expref')) {
		localStorage.removeItem('expref');
	}
	if (localStorage.getItem('exdiscount')) {
		localStorage.removeItem('exdiscount');
	}
	if (localStorage.getItem('expwarehouse')) {
		localStorage.removeItem('expwarehouse');
	}
	if (localStorage.getItem('expsupplier')) {
		localStorage.removeItem('expsupplier');
	}
	if (localStorage.getItem('expnote')) {
		localStorage.removeItem('expnote');
	}
	if (localStorage.getItem('expbiller')) {
		localStorage.removeItem('expbiller');
	}
	if (localStorage.getItem('expdate')) {
		localStorage.removeItem('expdate');
	}
	if (localStorage.getItem('expproject')) {
		localStorage.removeItem('expproject');
	}
	if (localStorage.getItem('exproom')) {
		localStorage.removeItem('exproom');
	}
	if (localStorage.getItem('expvehicle')) {
		localStorage.removeItem('expvehicle');
	}
	if (localStorage.getItem('exppayable_account')) {
		localStorage.removeItem('exppayable_account');
	}
}

function loadItems() {
    if (localStorage.getItem('expitems')) {
        total = 0;
        count = 1;
        an = 1;
        invoice_tax = 0;
		order_discount = 0;
        $("#expTable tbody").empty();
        expitems = JSON.parse(localStorage.getItem('expitems'));
        sortedItems = expitems;
        $('#add_expense, #edit_expense, #add_expense_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var expense_id = item.row.id, quantity = item.row.quantity, expense_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_cost = item.row.unit_cost, expense_code = item.row.code, description=item.row.description;
            var expense_account = item.row.expense_account;
            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="expense_id[]" type="hidden" class="expense_id" value="' + expense_id + '"><input name="expense_code[]" type="hidden" class="expense_code" value="' + expense_code + '"><input name="expense_name[]" type="hidden" class="expense_name" value="' + expense_name + '"><span class="sname" id="name_' + row_no + '">' + expense_name+ (expense_account? (' [Account Code - '+expense_account+']') :'') +'</span> </td>';
            tr_html += '<td><input class="form-control description" name="description[]" type="text"  value="' + description + '"></td>';
			tr_html += '<td><input class="form-control text-center unit_cost" tabindex ="'+an+'" name="unit_cost[]" type="text"  value="' + unit_cost + '"></td>';
            tr_html += '<td><input class="form-control text-center quantity"  name="quantity[]" type="text" value="' + formatDecimalRaw(quantity) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(unit_cost)) * parseFloat(quantity))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer expdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#expTable");
            total += formatDecimalRaw(((parseFloat(unit_cost)) * parseFloat(quantity)), 4);
            count += parseFloat(quantity);
            an++;
        });

        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';

        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#expTable tfoot').html(tfoot);
		
		
		if (exdiscount = localStorage.getItem('exdiscount')) {
            var ds = exdiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimalRaw(((total * parseFloat(pds[0])) / 100));
                } else {
                    order_discount = formatDecimalRaw(ds);
                }
            } else {
                order_discount = formatDecimalRaw(ds);
            }
        }
		
        if (site.settings.tax2 != 0) {
            if (exptax2 = localStorage.getItem('exptax2')) {
                $.each(tax_rates, function () {
                    if (this.id == exptax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimalRaw(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        var gtotal = parseFloat(total + invoice_tax  - order_discount);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
		$('#tds').text(formatMoney(order_discount));
        $('#total_items').val((parseFloat(count) - 1));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
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
        expitems = {};
    }
    if (item == null){
        return;
	}
    var item_id = item.id;
	item.order = new Date().getTime();
    expitems[item_id] = item;
    localStorage.setItem('expitems', JSON.stringify(expitems));
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
