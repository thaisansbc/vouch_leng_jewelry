$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}

if (localStorage.getItem('pwitems')) {
    loadItems();
}
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('pwitems')) {
                    localStorage.removeItem('pwitems');
                }
                if (localStorage.getItem('pwref')) {
                    localStorage.removeItem('pwref');
                }
				if (localStorage.getItem('pwrate')) {
					
                    localStorage.removeItem('pwrate');
                }
                if (localStorage.getItem('pwwarehouse')) {
                    localStorage.removeItem('pwwarehouse');
                }
				if (localStorage.getItem('pwpayment_term')) {
                    localStorage.removeItem('pwpayment_term');
                }
                if (localStorage.getItem('pwnote')) {
                    localStorage.removeItem('pwnote');
                }
                if (localStorage.getItem('pwcustomer')) {
                    localStorage.removeItem('pwcustomer');
                }
                if (localStorage.getItem('pwdate')) {
                    localStorage.removeItem('pwdate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

// save and load the fields in and/or from localStorage
var $customer = $('#pwcustomer');
$(document).on('change', '#pwdate', function (e) {
	localStorage.setItem('pwdate', $(this).val());
	loadItems();
});
if (pwdate = localStorage.getItem('pwdate')) {
	$('#pwdate').val(pwdate);
}

$('#pwref').change(function (e) {
    localStorage.setItem('pwref', $(this).val());
});
if (pwref = localStorage.getItem('pwref')) {
    $('#pwref').val(pwref);
}

var old_rate;
$(document).on("focus", '#pwrate', function () {
	old_rate = $(this).val();
}).on("change", '#pwrate', function () {
	var new_rate = $(this).val() ? $(this).val() : '0';
	if (is_valid_discount(new_rate)) {
		localStorage.setItem('pwrate', $(this).val());
		loadItems();
	} else {
		$(this).val(old_rate);
		bootbox.alert(lang.unexpected_value);
	}

});
if (pwrate = localStorage.getItem('pwrate')) {
	$('#pwrate').val(pwrate);
}
$('#pwwarehouse').change(function (e) {
    localStorage.setItem('pwwarehouse', $(this).val());
});
if (pwwarehouse = localStorage.getItem('pwwarehouse')) {
    $('#pwwarehouse').select2("val", pwwarehouse);
}
$('#pwpayment_term').change(function (e) {
    localStorage.setItem('pwpayment_term', $(this).val());
});
if (pwpayment_term = localStorage.getItem('pwpayment_term')) {
    $('#pwpayment_term').select2("val", pwpayment_term);
}

        $('#pwnote').redactor('destroy');
        $('#pwnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('pwnote', v);
            }
        });
        if (pwnote = localStorage.getItem('pwnote')) {
            $('#pwnote').redactor('set', pwnote);
        }
        $customer.change(function (e) {
            localStorage.setItem('pwcustomer', $(this).val());
            $('#customer_id').val($(this).val());
        });
        if (pwcustomer = localStorage.getItem('pwcustomer')) {
            $customer.val(pwcustomer).select2({
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


$(document).on('change', '.rexpiry', function () {
    var item_id = $(this).closest('tr').attr('data-item-id');
    pwitems[item_id].row.expiry = $(this).val();
    localStorage.setItem('pwitems', JSON.stringify(pwitems));
});


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



// Order discount calcuation


    /* ----------------------
     * Delete Row Method
     * ---------------------- */

     $(document).on('click', '.pwdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete pwitems[item_id];
        row.remove();
        if(pwitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('pwitems', JSON.stringify(pwitems));
            loadItems();
            return;
        }
    });

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = pwitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        unit_price = formatDecimalRaw(row.children().children('.rprice').val()),
		pnote = row.children().children('.pnote').val();
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
		if(item.units){
			uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
			$.each(item.units, function () {
				if(this.id == item.row.unit) {
					$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
				} else {
					$("<option />", {value: this.id, text: this.name}).appendTo(uopt);
				}
			});
			$('#punits-div').html(uopt);
		}else{
			$('#punits-div').html('<p style="margin: 12px 0 0 0;">n/a</p>');
		}
        
		
		$('#pnote').val(pnote);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
		$('#pserial').val(row.children().children('.rserial').val());
        $('#pexpiry').val(row.children().children('.rexpiry').val());
        $('#prModal').appendTo("body").modal('show');

    });

		

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = pwitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        if(unit != pwitems[item_id].row.base_unit) {
            $.each(item.units, function() {
                if (this.id == unit) {
                    $('#pprice').val(formatDecimalRaw((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))))).change();
                }
            });
        } else {
            $('#pprice').val(formatDecimalRaw(item.row.base_unit_price)).change();
        }
    });

    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');

        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        var unit = $('#punit').val();
        var qty = parseFloat($('#pquantity').val());
		var pnote = $("#pnote").val();
		var unit_price = $('#pprice').val();
		pwitems[item_id].row.pnote = (pnote?pnote:"");
        pwitems[item_id].row.qty = qty,
        pwitems[item_id].row.unit = unit,
		pwitems[item_id].row.unit_price = unit_price,
        pwitems[item_id].row.expiry = $('#pexpiry').val() ? $('#pexpiry').val() : '';
		pwitems[item_id].row.serial_no = $('#pserial').val() ? $('#pserial').val() : '';
        localStorage.setItem('pwitems', JSON.stringify(pwitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        $('#mModal').appendTo("body").modal('show');
        return false;
    });
	
	$(document).on('click', '#addItemManually', function (e) {
		var mid = (new Date).getTime();
		mcode = $('#mcode').val(),
		mname = $('#mname').val(),
		mqty = parseFloat($('#mquantity').val()),
		unit_price = parseFloat($('#mprice').val()),
		add_product = $('#add_product').val();
		if (mcode && mname && mqty && unit_price) {
			pwitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "unit_price": unit_price,"add_product": add_product,  "qty": mqty, "type": "manual",  "serial_no": "","expiry": "", "units": false}};
			localStorage.setItem('pwitems', JSON.stringify(pwitems));
			loadItems();
		}
		$('#mModal').modal('hide');
		$('#mcode').val('');
		$('#mname').val('');
		$('#mquantity').val('');
		$('#mprice').val('');
		return false;
	});
    var old_qty;
    $(document).on("focus", '.rquantity', function () {
        old_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        pwitems[item_id].row.qty = new_qty;
        localStorage.setItem('pwitems', JSON.stringify(pwitems));
        loadItems();
    });
	
	$(document).on("focus", '.rserial', function () {
	}).bind("keypress", '.rserial', function (e) {
		if(e.keyCode == 13){
			e.preventDefault();
			return false;
		}
	});
	
	$(document).on("change", '.rserial', function () {
        var row = $(this).closest('tr');
        var serial_no = $(this).val();
        item_id = row.attr('data-item-id');
        pwitems[item_id].row.serial_no = serial_no;
        localStorage.setItem('pwitems', JSON.stringify(pwitems));
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
        pwitems[item_id].row.unit_price = new_price;
        localStorage.setItem('pwitems', JSON.stringify(pwitems));
        loadItems();
    });
	
	
	var product_rate;
    $(document).on("focus", '.product_rate', function () {
        old_product_rate = $(this).val();
    }).on("change", '.product_rate', function () {
        var row = $(this).closest('tr');
		var new_product_rate = $(this).val() ? $(this).val() : '0';
		if (!is_valid_discount($(this).val())) {
            $(this).val(old_product_rate);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        
        var item_id = row.attr('data-item-id');
        pwitems[item_id].row.product_rate = new_product_rate;
        localStorage.setItem('pwitems', JSON.stringify(pwitems));
        loadItems();
    });
	

    $(document).on("click", '#removeReadonly', function () {
		 $('#pwcustomer').select2('readonly', false);
		 return false;
	 });

});

function nsCustomer() {
    $('#pwcustomer').select2({
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

    if (localStorage.getItem('pwitems')) {
        total = 0;
        count = 1;
        an = 1;

        $("#pwTable tbody").empty();
		var pwrate = localStorage.getItem('pwrate');
		var pwdate = localStorage.getItem('pwdate');
        pwitems = JSON.parse(localStorage.getItem('pwitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(pwitems, function(o){return [parseInt(o.order)];}) : pwitems;

        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;
            var product_id = item.row.id, item_type = item.row.type, item_price = item.row.unit_price, item_qty = item.row.qty,item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
			var pnote = (item.row.pnote?item.row.pnote:"");
            var product_unit = item.row.unit;
			var add_product = (item.row.add_product)?item.row.add_product:0;
			if(item.row.expiry){
				var item_expiry = item.row.expiry;
			}else{
				var item_expiry = fd1(pwdate);
			}

			if(is_valid_discount(item.row.product_rate)){
				var product_rate = item.row.product_rate;
			}else if(pwrate){
				var product_rate = pwrate;
			}else{
				var product_rate = 0;
			}
			
            var customer = localStorage.getItem('pwcustomer');

            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input type="hidden" name="add_product[]" value="'+add_product+'"/><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +' </span> <i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
			if(localStorage.getItem('pwupdate')==1){
				tr_html += '<td><input class="form-control date next_payment" name="next_payment[]" type="text" value="' + item.row.next_date + '" data-id="' + row_no + '" data-item="' + item_id + '" id="next_payment_' + row_no + '"></td>';
			}
			tr_html += '<td class="text-right"><input class="form-control text-left rserial" name="serial_no[]" value="'+item.row.serial_no+'" id="serial_no_' + row_no + '"/></td>';
			tr_html += '<td><input class="form-control text-center product_rate" name="product_rate[]" type="text" value="' + product_rate + '" data-id="' + row_no + '" data-item="' + item_id + '" id="product_rate_' + row_no + '">';
			tr_html += '<td><input class="product_unit" name="product_unit[]" type="hidden" value="' + product_unit + '"><input class="form-control text-center rprice" name="unit_price[]" type="text" id="price_' + row_no + '" value="' + item_price + '"><input class="pnote" name="pnote[]" type="hidden" value="' + pnote + '"></td>';
			tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';

            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) * parseFloat(item_qty)))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pwdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#pwTable");
			
            total += formatDecimalRaw(((parseFloat(item_price) * parseFloat(item_qty))));
            count += parseFloat(item_qty);
            an++;

        });
		
        var col = 5;
		if(localStorage.getItem('pwupdate')==1){
			col = col + 1;
		}
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatQuantity2(parseFloat(count) - 1) + '</th>';

        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#pwTable tfoot').html(tfoot);

        var gtotal = total;
        $('#total').text(formatMoney(total));
        $('#titems').text((an-1)+' ('+(parseFloat(count)-1)+')');
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}


function add_pawn_item(item) {
	
    if (count == 1) {
        pwitems = {};
        if ($('#pwcustomer').val()) {
            $('#pwcustomer').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (pwitems[item_id]) {

        var new_qty = parseFloat(pwitems[item_id].row.qty) + 1;
        pwitems[item_id].row.base_quantity = new_qty;
        if(pwitems[item_id].row.unit != pwitems[item_id].row.base_unit) {
            $.each(pwitems[item_id].units, function(){
                if (this.id == pwitems[item_id].row.unit) {
                    pwitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        pwitems[item_id].row.qty = new_qty;

    } else {
        pwitems[item_id] = item;
    }
    pwitems[item_id].order = new Date().getTime();
    localStorage.setItem('pwitems', JSON.stringify(pwitems));
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
