$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
    if (exrref = localStorage.getItem('exrref')) {
        $('#exrref').val(exrref);
    }
	if (exrdiscount = localStorage.getItem('exrdiscount')) {
		$('#exrdiscount').val(exrdiscount);
	}
    $('#exrtax2').change(function (e) {
        localStorage.setItem('exrtax2', $(this).val());
        $('#exrtax2').val($(this).val());
    });
    if (exrtax2 = localStorage.getItem('exrtax2')) {
        $('#exrtax2').select2("val", exrtax2);
    }
	if (exrproject = localStorage.getItem('exrproject')) {
        $('#project').select2("val", exrproject);
    }
	if (exrroom = localStorage.getItem('exrroom')) {
        $('#room').select2("val", exrroom);
    }
	if (exrpvehicle = localStorage.getItem('exrpvehicle')) {
        $('#vehicle').select2("val", exrpvehicle);
    }
	
	$(document).on('change', '#exrurgent', function (e) {
		localStorage.setItem('exrurgent', $(this).val());
	});
	if (exrurgent = localStorage.getItem('exrurgent')) {
		$('#exrurgent').select2("val", exrurgent);
	}
	
    $('#exrsupplier').change(function (e) {
        localStorage.setItem('exrsupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if ((exrsupplier = localStorage.getItem('exrsupplier')) && exrsupplier != 0) {
        $('#exrsupplier').val(exrsupplier).select2({
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

    if (localStorage.getItem('exritems')){
        loadItems();
    }
    $('#exrref').change(function (e) {
        localStorage.setItem('exrref', $(this).val());
    });
	$('#project').change(function (e) {
        localStorage.setItem('exrproject', $(this).val());
    });
	$('#room').change(function (e) {
        localStorage.setItem('exrroom', $(this).val());
    });
	
	$('#vehicle').change(function (e) {
        localStorage.setItem('exrpvehicle', $(this).val());
    });
    if (exrref = localStorage.getItem('exrref')) {
        $('#exrref').val(exrref);
    }
    $('#exrwarehouse').change(function (e) {
        localStorage.setItem('exrwarehouse', $(this).val());
    });
    if (exrwarehouse = localStorage.getItem('exrwarehouse')) {
        $('#exrwarehouse').select2("val", exrwarehouse);
    }

    $('#exrnote').redactor('destroy');
    $('#exrnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('exrnote', v);
        }
    });
    if (exrnote = localStorage.getItem('exrnote')) {
        $('#exrnote').redactor('set', exrnote);
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
		$('#exrtax2').change(function () {
			localStorage.setItem('exrtax2', $(this).val());
			loadItems();
			return;
		});
	}
	var old_podiscount;
	$('#exrdiscount').focus(function () {
		exrdiscount = $(this).val();
	}).change(function () {
		if (is_valid_discount($(this).val())) {
			localStorage.removeItem('exrdiscount');
			localStorage.setItem('exrdiscount', $(this).val());
			loadItems();
			return;
		} else {
			$(this).val(exrdiscount);
			bootbox.alert(lang.unexpected_value);
			return;
		}

	});
	$(document).on('click', '.expdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete exritems[item_id];
		row.remove();
		if(exritems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('exritems', JSON.stringify(exritems));
			loadItems();
			return;
		}
	});
	
	$('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('exritems')) {
					localStorage.removeItem('exritems');
				}
				if (localStorage.getItem('exrtax2')) {
					localStorage.removeItem('exrtax2');
				}
				if (localStorage.getItem('exrdiscount')) {
                    localStorage.removeItem('exrdiscount');
                }
				if (localStorage.getItem('exrref')) {
					localStorage.removeItem('exrref');
				}
				if (localStorage.getItem('exrwarehouse')) {
					localStorage.removeItem('exrwarehouse');
				}
				if (localStorage.getItem('exrnote')) {
					localStorage.removeItem('exrnote');
				}
				if (localStorage.getItem('exrdate')) {
					localStorage.removeItem('exrdate');
				}
				if (localStorage.getItem('exrbiller')) {
					localStorage.removeItem('exrbiller');
				}
				if (localStorage.getItem('exrrequester')) {
					localStorage.removeItem('exrrequester');
				}
				if (localStorage.getItem('exrurgent')) {
					localStorage.removeItem('exrurgent');
				}

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
        exritems[item_id].row.quantity = new_qty;
        localStorage.setItem('exritems', JSON.stringify(exritems));
        loadItems();
    });
	

    $(document).on("change", '.description', function () {
        var row = $(this).closest('tr');
        var description = $(this).val(),
        item_id = row.attr('data-item-id');
        exritems[item_id].row.description = description;
        localStorage.setItem('exritems', JSON.stringify(exritems));
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
        exritems[item_id].row.unit_cost = new_cost;
        localStorage.setItem('exritems', JSON.stringify(exritems));
        loadItems();
    });



});


function nsSupplier() {
    $('#exrsupplier').select2({
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

function loadItems() {
    if (localStorage.getItem('exritems')) {
        total = 0;
        count = 1;
        an = 1;
        invoice_tax = 0;
		order_discount = 0;
        $("#expTable tbody").empty();
        exritems = JSON.parse(localStorage.getItem('exritems'));
        sortedItems = exritems;
        $('#add_expense_request, #edit_expense_request, #add_expense_request_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var expense_id = item.row.id, quantity = item.row.quantity, expense_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_cost = item.row.unit_cost, expense_code = item.row.code, description=item.row.description;
            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="expense_id[]" type="hidden" class="expense_id" value="' + expense_id + '"><input name="expense_code[]" type="hidden" class="expense_code" value="' + expense_code + '"><input name="expense_name[]" type="hidden" class="expense_name" value="' + expense_name + '"><span class="sname" id="name_' + row_no + '">' + expense_code +' - '+ expense_name +'</span> </td>';
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
		
		
		if (exrdiscount = localStorage.getItem('exrdiscount')) {
            var ds = exrdiscount;
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
            if (exrtax2 = localStorage.getItem('exrtax2')) {
                $.each(tax_rates, function () {
                    if (this.id == exrtax2) {
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
        exritems = {};
    }
    if (item == null){
        return;
	}
    var item_id = item.id;
	item.order = new Date().getTime();
    exritems[item_id] = item;
    localStorage.setItem('exritems', JSON.stringify(exritems));
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
