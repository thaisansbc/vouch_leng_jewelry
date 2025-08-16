$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
	$('#add_fuel_sale, #edit_fuel_sale').attr('disabled', true);
    if (localStorage.getItem('shitems')){
        loadItems();
    }
    $('#shref').change(function (e) {
        localStorage.setItem('shref', $(this).val());
    });
    if (shref = localStorage.getItem('shref')) {
        $('#shref').val(shref);
    }
	$('#shsaleman').change(function (e) {
		localStorage.setItem('shsaleman', $(this).val());
	});
	if (shsaleman = localStorage.getItem('shsaleman')) {
		$('#shsaleman').select2("val", shsaleman);
	}
    $('#shwarehouse').change(function (e) {
        localStorage.setItem('shwarehouse', $(this).val());
    });
    if (shwarehouse = localStorage.getItem('shwarehouse')) {
        $('#shwarehouse').select2("val", shwarehouse);
    }
	$('#shtime').change(function (e) {
		localStorage.setItem('shtime', $(this).val());
	});
	if (shtime = localStorage.getItem('shtime')) {
		$('#shtime').select2("val", shtime);
	}
	$('#shnote').redactor('destroy');
    $('#shnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('shnote', v);
        }
    });
	$(document).on('change', '#shnote', function (e) {
		localStorage.setItem('shnote', $(this).val());
	});
		
    if (shnote = localStorage.getItem('shnote')) {
        $('#shnote').val('set', shnote);
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
	
	$(document).on('click', '.del_gsl', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete shitems[item_id];
		row.remove();
		if(shitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('shitems', JSON.stringify(shitems));
			loadItems();
			return;
		}
	});
	$('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('shitems')) {
					localStorage.removeItem('shitems');
				}
				if (localStorage.getItem('shref')) {
					localStorage.removeItem('shref');
				}
				if (localStorage.getItem('shwarehouse')) {
					localStorage.removeItem('shwarehouse');
				}
				if (localStorage.getItem('shsaleman')) {
					localStorage.removeItem('shsaleman');
				}
				if (localStorage.getItem('shdate')) {
					localStorage.removeItem('shdate');
				}
				if (localStorage.getItem('shbiller')) {
					localStorage.removeItem('shbiller');
				}
				if (localStorage.getItem('shnote')) {
					localStorage.removeItem('shnote');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
	
	// Event keyup selection
	$(document).on("change", '.shnozzle', function () {
		var id = $(this).val();
		var row = $(this).closest("tr");
		var item_id = row.attr('data-item-id');
		
		var nozzles_arrays = [];
		$.each(shitems,function(key,index){
			var nozzle_id = index.row.nozzle_id;
			nozzles_arrays.push(nozzle_id);
		});
		
		if(shitems[item_id].nozzles !== false && id != 0){
			if($.inArray(id,nozzles_arrays) < 0){
				$.each(shitems[item_id].nozzles, function(){
					if (this.id == id) {
						shitems[item_id].row.nozzle_id = this.id;
						shitems[item_id].row.nozzle_no = this.nozzle_no;
						shitems[item_id].row.start_no = this.nozzle_start_no;
						shitems[item_id].row.product_id = this.product_id;
						shitems[item_id].row.unit_price = this.unit_price;
						shitems[item_id].row.customer_qty = this.customer_qty;
						shitems[item_id].row.customer_amount = this.customer_amount;
					}
				});
			}else{
				bootbox.alert("Invalid file selected. Can't the same nozzle.");
			}
		}else{
			shitems[item_id].row.nozzle_id = 0;
			shitems[item_id].row.nozzle_no = 0;
			shitems[item_id].row.start_no = 0;
			shitems[item_id].row.end_no = 0;
			shitems[item_id].row.product_id = 0;
			shitems[item_id].row.quantity = 0;
			shitems[item_id].row.customer_qty = 0;
			shitems[item_id].row.customer_amount = 0;
		}
		localStorage.setItem('shitems', JSON.stringify(shitems));
		loadItems();
	});

	// Event keyup textbox
	var old_value;
	$(document).on("focus", '.shstart_no, .shend_no, .shunit_price, .shusing_qty', function () {
        old_value = $(this).val();
    }).on("change", '.shstart_no, .shend_no, .shunit_price, .shusing_qty', function () {
		var row = $(this).closest("tr");
		if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_value);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var item_id = row.attr('data-item-id');
		var start_no = row.find(".shstart_no").val()-0;
		var end_no = row.find(".shend_no").val()-0;
		var unit_price = row.find(".shunit_price").val()-0;
		var using_qty = row.find(".shusing_qty").val()-0;
		var quantity = formatDecimal(end_no - start_no - (shitems[item_id].row.customer_qty > 0 ? shitems[item_id].row.customer_qty : 0));
		if(using_qty > 0){
			quantity = quantity - using_qty;
		}
		if(end_no <= start_no){
			row.find(".shend_no").val('');
			bootbox.alert(lang.unexpected_value);
            return;
		}
		var subtotal = formatDecimal(quantity * unit_price);
		shitems[item_id].row.start_no = start_no;
		shitems[item_id].row.end_no = end_no;
		shitems[item_id].row.quantity = quantity;
		shitems[item_id].row.using_qty = using_qty;
		shitems[item_id].row.unit_price = unit_price;
		shitems[item_id].row.subtotal = subtotal;
		localStorage.setItem('shitems', JSON.stringify(shitems));
		loadItems();
		var index = row.find(".shend_no").attr("tabindex");
		var index_true = parseFloat(index) + 1;
		$('[tabindex='+(index_true)+']').focus().select(); 
	});

	
	var old_cash;
	$(document).on("focus", '.change-money-usd, .change-money-kh, .cash_submit_usd, .cash_submit_khr, .credit_amount_usd, .credit_amount_khr', function () {
        old_cash = $(this).val();
    }).on("change", '.change-money-usd, .change-money-kh, .cash_submit_usd, .cash_submit_khr, .credit_amount_usd, .credit_amount_khr', function () {
		var row = $(this).closest("tr");
		if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_cash);
            bootbox.alert(lang.unexpected_value);
            return;
        }
	});

});

function loadItems() {
	if (localStorage.getItem('shitems')) {
		$('select').select2('destroy');
		total = 0;
		qty = 0;
		cus_qty = 0;
		cus_amount = 0;
		count = 1;
		an = 1;
		$("#glTable tbody").empty();
		shitems = JSON.parse(localStorage.getItem('shitems'));
		sortedItems = shitems;
		$('#add_fuel_sale, #edit_fuel_sale').attr('disabled', false);
		$.each(sortedItems, function () {
			var item = this;
			var item_id = item.id;
			var start_no = item.row.start_no, end_no = item.row.end_no,  unit_price = item.row.unit_price, subtotal = item.row.subtotal, quantity = item.row.quantity;
			var row_no = item_id, tank_id = item.row.id, nozzle_id = item.row.nozzle_id, nozzle_no = item.row.nozzle_no, tank_code = item.row.code, tank_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;"), product_id = item.row.product_id;
			var optnozzles = '<p>n/a</p>';
			if(this.nozzles !== false) {
				optnozzles = "<select name=\"tank[]\" class=\"form-control select shnozzle\">";
						optnozzles += "<option value='0'>n/a</option>";
				$.each(this.nozzles, function () {
					if(this.id == nozzle_id) {
						optnozzles += "<option value="+this.id+" selected>("+this.nozzle_no+') - '+this.product_name+"</option>";
					}else{
						optnozzles += "<option value="+this.id+">("+this.nozzle_no+') - '+this.product_name+"</option>";
					}
				});
				optnozzles += "</select>";
			}
			var readonly_price = '';
			if(edit_price == 0){
				readonly_price = ' readonly';
			}
			if(end_no <= 0){
				end_no = '';
			}
			var using_qty = item.row.using_qty;
			if (typeof (using_qty) == "undefined") {
				using_qty = 0;
			}
			
			var newTr = $('<tr id="row_' + row_no + '" class="" data-item-id="' + row_no + '"></tr>');
			tr_html  = '<td class="text-left"><input name="tank_id[]" type="hidden" value="'+tank_id+'"><span class="sname" id="name_' + row_no + '">' + tank_code +' - '+ tank_name +'</span></td>';
			tr_html += '<td class="text-left"><input name="nozzle_id[]" type="hidden" value="'+nozzle_id+'"><input name="nozzle_no[]" type="hidden" value="'+nozzle_no+'"><input name="product_id[]" type="hidden" value="'+product_id+'">'+optnozzles+'</td>';
			tr_html += '<td class="text-center"><input type="text" name="start_no[]" class="form-control text-center shstart_no" readonly value="'+start_no+'" /></td>';             
			tr_html += '<td class="text-center"><input type="text" name="end_no[]" class="form-control text-center shend_no" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="'+end_no+'" /></td>';
			tr_html += '<td class="text-center"><input type="text" '+readonly_price+' name="unit_price[]" class="form-control text-right shunit_price" value="'+ formatDecimal(unit_price) +'" /></td>';
			tr_html += '<td class="text-center"><input type="text" name="using_qty[]" class="form-control text-right shusing_qty" value="'+using_qty+'" /></td>';
			tr_html += '<td class="text-center"><input name="customer_qty[]" type="hidden" value="' + item.row.customer_qty + '">'+ formatDecimal(item.row.customer_qty) +'</span></td>';
			tr_html += '<td class="text-center"><input name="customer_amount[]" type="hidden" value="' + item.row.customer_amount + '">'+ formatMoney(item.row.customer_amount) +'</span></td>';
			tr_html += '<td class="text-center"><input name="quantity[]" type="hidden" value="' + quantity + '"><span class="shquantity">'+ formatDecimal(quantity) +'</span></td>';
			tr_html += '<td class="text-right"><input name="subtotal[]" type="hidden" value="' + subtotal + '"><span class="shsubtotal">'+ formatMoney(subtotal) +'</span></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip del_gsl" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';

			newTr.html(tr_html);
			newTr.appendTo("#glTable");
			total += formatDecimal(subtotal);
			qty += formatDecimal(quantity);
			cus_qty += formatDecimal(item.row.customer_qty);
			cus_amount += formatDecimal(item.row.customer_amount);
			count += 1;;
			an++;
		});
		var col = 5;
		var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th></th>';
			tfoot += '<th class="text-center">' + formatDecimal(parseFloat(cus_qty)) + '</th>';
			tfoot += '<th class="text-center">' + formatMoney(parseFloat(cus_amount)) + '</th>';
			tfoot += '<th class="text-center">' + formatDecimal(parseFloat(qty)) + '</th>';
			tfoot += '<th class="text-right">' + formatMoney(parseFloat(total)) + '</th>';
			tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>';
			tfoot += '</tr>';
		$('#glTable tfoot').html(tfoot);
		$('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
		if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
		}
		$('select').select2();
        set_page_focus();
	}
}

function add_item(item) {
	if (count == 1) {
		shitems = {};
	}
	if (item == null){
		return;
	}
	var item_id = item.id;
	shitems[item_id] = item;
	localStorage.setItem('shitems', JSON.stringify(shitems));
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
