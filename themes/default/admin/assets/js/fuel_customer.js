$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
	$('#add_fuel_customer, #edit_fuel_customer').attr('disabled', true);
	var $customer = $('#fccustomer');
    $customer.change(function (e) {
        localStorage.setItem('fccustomer', $(this).val());
    });
	
	if (fccustomer = localStorage.getItem('fccustomer')) {
        $customer.val(fccustomer).select2({
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
	
	function nsCustomer() {
		$('#fccustomer').select2({
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
	
	$('#fcreference').change(function (e) {
        localStorage.setItem('fcreference', $(this).val());
    });
    if (fcreference = localStorage.getItem('fcreference')) {
        $('#fcreference').val(fcreference);
    }
	
	$('#fcsalesman').change(function (e) {
		localStorage.setItem('fcsalesman', $(this).val());
	});
	if (fcsalesman = localStorage.getItem('fcsalesman')) {
		$('#fcsalesman').select2("val", fcsalesman);
	}
    $('#fcwarehouse').change(function (e) {
        localStorage.setItem('fcwarehouse', $(this).val());
    });
    if (fcwarehouse = localStorage.getItem('fcwarehouse')) {
        $('#fcwarehouse').select2("val", fcwarehouse);
    }
	$('#fctime').change(function (e) {
		localStorage.setItem('fctime', $(this).val());
	});
	if (fctime = localStorage.getItem('fctime')) {
		$('#fctime').select2("val", fctime);
	}
	$('#fcnote').redactor('destroy');
    $('#fcnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('fcnote', v);
        }
    });
	$(document).on('change', '#fcnote', function (e) {
		localStorage.setItem('fcnote', $(this).val());
	});
		
    if (fcnote = localStorage.getItem('fcnote')) {
		$('#fcnote').redactor('set', fcnote);
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
	
	$(document).on('click', '.del_fcls', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete fcitems[item_id];
		row.remove();
		if(fcitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('fcitems', JSON.stringify(fcitems));
			loadItems();
			return;
		}
	});
	$('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('fcitems')) {
					localStorage.removeItem('fcitems');
				}
				if (localStorage.getItem('fcreference')) {
					localStorage.removeItem('fcreference');
				}
				if (localStorage.getItem('fcwarehouse')) {
					localStorage.removeItem('fcwarehouse');
				}
				if (localStorage.getItem('fcsalesman')) {
					localStorage.removeItem('fcsalesman');
				}
				if (localStorage.getItem('fcdate')) {
					localStorage.removeItem('fcdate');
				}
				if (localStorage.getItem('shbiller')) {
					localStorage.removeItem('shbiller');
				}
				if (localStorage.getItem('fcnote')) {
					localStorage.removeItem('fcnote');
				}
				if (localStorage.getItem('fccustomer')) {
					localStorage.removeItem('fccustomer');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
	
	if (localStorage.getItem('fcitems')){
        loadItems();
    }
	
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
		var item_id = row.attr('data-item-id');
        var quantity = parseFloat($(this).val());
		var subtotal = quantity *  fcitems[item_id].row.unit_price;
        fcitems[item_id].row.quantity = quantity;
		fcitems[item_id].row.subtotal = subtotal;
        localStorage.setItem('fcitems', JSON.stringify(fcitems));
        loadItems();
    });  
	
	var old_unit_price;
    $(document).on("focus", '.unit_price', function () {
        old_unit_price = $(this).val();
    }).on("change", '.unit_price', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($(this).val());
		var subtotal = unit_price *  fcitems[item_id].row.quantity;
        fcitems[item_id].row.unit_price = unit_price;
		fcitems[item_id].row.subtotal = subtotal;
        localStorage.setItem('fcitems', JSON.stringify(fcitems));
        loadItems();
    });  
	
	$(document).on("change", '.fcnozzle', function () {
		var id = $(this).val();
		var row = $(this).closest("tr");
		var item_id = row.attr('data-item-id');
		
		var nozzles_arrays = [];
		$.each(fcitems,function(key,index){
			var nozzle_id = index.row.nozzle_id;
			nozzles_arrays.push(nozzle_id);
		});
		
		if(fcitems[item_id].nozzles !== false && id != 0){
			if($.inArray(id,nozzles_arrays) < 0){
				$.each(fcitems[item_id].nozzles, function(){
					if (this.id == id) {
						fcitems[item_id].row.nozzle_id = this.id;
						fcitems[item_id].row.nozzle_no = this.nozzle_no;
						fcitems[item_id].row.product_id = this.product_id;
						fcitems[item_id].row.unit_price = this.unit_price;
					}
				});
			}else{
				bootbox.alert("Invalid file selected. Can't the same nozzle.");
			}
		}else{
			fcitems[item_id].row.nozzle_id = 0;
			fcitems[item_id].row.nozzle_no = 0;
			fcitems[item_id].row.product_id = 0;
			fcitems[item_id].row.quantity = 0;
		}
		localStorage.setItem('fcitems', JSON.stringify(fcitems));
		loadItems();
	});
	
	$(document).on("change", '.truck_id', function () {
		var truck_id = $(this).val();
		var row = $(this).closest("tr");
		var item_id = row.attr('data-item-id');
		fcitems[item_id].row.truck_id = truck_id;
		localStorage.setItem('fcitems', JSON.stringify(fcitems));
		loadItems();
	});

});

function loadItems() {
	if (localStorage.getItem('fcitems')) {
		total = 0;
		qty = 0;
		count = 1;
		an = 1;
		$("#glTable tbody").empty();
		fcitems = JSON.parse(localStorage.getItem('fcitems'));
		sortedItems = fcitems;
		$('#add_fuel_customer, #edit_fuel_customer').attr('disabled', false);
		$.each(sortedItems, function () {
			var item = this;
			var item_id = item.id;
			var unit_price = item.row.unit_price, subtotal = item.row.subtotal, quantity = item.row.quantity;
			var row_no = item_id, tank_id = item.row.id, nozzle_id = item.row.nozzle_id,truck_id = item.row.truck_id,  nozzle_no = item.row.nozzle_no, tank_code = item.row.code, tank_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;"), product_id = item.row.product_id;
			
			if (typeof (item.row.fuel_sale_id) === "undefined") {
				var fuel_sale_id = "";
			}else{
				var fuel_sale_id = item.row.fuel_sale_id;
			}
			var optnozzles = '<p>n/a</p>';
			if(this.nozzles !== false) {
				optnozzles = "<select  name=\"tank[]\" class=\"form-control select fcnozzle\">";
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
			if(truck==1){
				var opttrucks = '<p>n/a</p>';
				if(this.customer_trucks !== false) {
					opttrucks = "<select  name=\"truck_id[]\" class=\"form-control select truck_id\">";
					opttrucks += "<option value='0'>n/a</option>";
					$.each(this.customer_trucks, function () {
						if(this.id == truck_id) {
							opttrucks += "<option value="+this.id+" selected>"+this.name+' - '+this.plate_number+"</option>";
						}else{
							opttrucks += "<option value="+this.id+">"+this.name+' - '+this.plate_number+"</option>";
						}
					});
					opttrucks += "</select>";
				}
			}
			var readonly_price = '';
			if(edit_price == 0){
				readonly_price = ' readonly';
			}

			var newTr = $('<tr id="row_' + row_no + '" class="" data-item-id="' + row_no + '"></tr>');
			tr_html  = '<td class="text-left"><input name="tank_id[]" type="hidden" value="'+tank_id+'"><span class="sname" id="name_' + row_no + '">' + tank_code +' - '+ tank_name +'</span></td>';
			tr_html += '<td class="text-left"><input name="nozzle_id[]" type="hidden" value="'+nozzle_id+'"><input name="nozzle_no[]" type="hidden" value="'+nozzle_no+'"><input name="product_id[]" type="hidden" value="'+product_id+'">'+optnozzles+'</td>';
			if(truck==1){
				tr_html += '<td class="text-center">'+opttrucks+'</td>';
			}
			tr_html += '<td class="text-center"><input type="text" '+readonly_price+' name="unit_price[]" class="form-control text-right unit_price" value="'+ formatDecimal(unit_price) +'" /></td>';
			tr_html += '<td class="text-center"><input type="hidden" name="fuel_sale_id[]" value="'+fuel_sale_id+'"/><input type="text" name="quantity[]" '+(fuel_sale_id > 0 ? "readonly" : "")+'  class="form-control text-right quantity" value="'+quantity+'"></td>';
			tr_html += '<td class="text-right"><input name="subtotal[]" type="hidden" value="' + subtotal + '"><span>'+ formatMoney(subtotal) +'</span></td>';
			tr_html += '<td class="text-center">'+(fuel_sale_id > 0 ? "" : '<i class="fa fa-times tip del_fcls" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i>')+'</td>';
			
			newTr.html(tr_html);
			newTr.prependTo("#glTable");
			total += formatDecimal(subtotal);
			qty += formatDecimal(quantity);
			count += 1;;
			an++;
		});
		var col = 3;
		if(truck==1){
			col++;
		}
		var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th>';
			tfoot += '<th class="text-right">' + formatDecimal(parseFloat(qty)) + '</th>';
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
		if (count > 1) {
            $('#fccustomer').select2("readonly", true);
			$('#fcwarehouse').select2("readonly", true);
        }else{
			$('#fccustomer').select2("readonly", false);
			$('#fcwarehouse').select2("readonly", false);
		} 
		$('select').select2();
        set_page_focus();
	}
}

function add_item(item) {
	if (count == 1) {
		fcitems = {};
	}
	if (item == null){
		return;
	}
	var item_id = item.id;
	fcitems[item_id] = item;
	localStorage.setItem('fcitems', JSON.stringify(fcitems));
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
