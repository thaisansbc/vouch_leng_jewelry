$(document).ready(function (e) {
	var $customer = $('#customer');
	$customer.change(function (e) {
        localStorage.setItem('customer', $(this).val());
    });
    if (customer = localStorage.getItem('customer')) {
        $customer.val(customer).select2({
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
    } 
});
if (localStorage.getItem('usitems')) {
	loadItems();
}
$(document).on('click', '.usdel', function () {
	var row = $(this).closest('tr');
	var item_id = row.attr('data-item-id');
	delete usitems[item_id];
	row.remove();
    if (usitems.hasOwnProperty(item_id)) {} 
    else {
    	localStorage.setItem('usitems', JSON.stringify(usitems));
    	loadItems();
    	return;
 	}
	// loadItems();
});
var old_row_qty = 0;
$(document).on("focus", '.qty_use', function () {
	old_row_qty = $(this).val();
}).on("change", '.qty_use', function () {
	var m = 2;
	var row = $(this).closest('tr');
	if (!is_numeric($(this).val()) || parseFloat($(this).val()) <= 0) {
		$(this).val(old_row_qty);
		bootbox.alert(lang.unexpected_value);
		return;
	}
	var bal_project	= 0;
	var qty_project = row.find('.qty_project').val();
	var qty_old     = row.find('.qty_old').val();
	var have_plan   = row.find('.have_plan').val();
	var qty_unit	= row.find('#unit').find('option:selected').attr('qty');
	var qty_exp 	= row.find('#exp').find('option:selected').attr('qty');
	var new_qty 	= parseFloat($(this).val()),
	item_id 		= row.attr('data-item-id');
	if (have_plan > 0 ) {
		if (qty_project > 0) {
			bal_project = (parseFloat(qty_project) + parseFloat(qty_old)) - ( (new_qty?parseFloat(new_qty):0) * (qty_unit?parseFloat(qty_unit):1) );
		}
	}
	if (site.settings.product_expiry == 1) {
		if (qty_exp < (new_qty * qty_unit)) {
			$(this).val(formatDecimal(qty_exp));
			new_qty = qty_exp;
			bootbox.alert(lang.unexpected_value);
			return;
		}
	}
	usitems[item_id].project_qty = bal_project;
	usitems[item_id].row.qty_use = new_qty;
	localStorage.setItem('usitems', JSON.stringify(usitems));
	loadItems();
});
var old_unit;
$(document).on('focusin', '.unit', function(){
    old_unit = $(this).val();
}).on("change", '.unit', function () {
	var row 		= $(this).closest('tr');
	var units	 	= $(this).val(),
	item_id 		= row.attr('data-item-id');
	var bal_project	= 0;
	var qty_project = row.find('.qty_project').val();
	var qty_old     = row.find('.qty_old').val();
	var new_qty     = row.find('.qty_use').val();
	var qty_unit	= row.find('#unit').find('option:selected').attr('qty');
	var qty_exp 	= row.find('#exp').find('option:selected').attr('qty');
	if (qty_project > 0) {
		bal_project = (parseFloat(qty_project) + parseFloat(qty_old)) - ( (new_qty?parseFloat(new_qty):0) * (qty_unit?parseFloat(qty_unit):1) );
	}
	if (site.settings.product_expiry == 1) {
		if (qty_exp < (new_qty * qty_unit)) {
			$(this).val(old_unit);
			$(this).select2('val', old_unit);
			bootbox.alert(lang.unexpected_value);
			return;
		}
	}
	if (typeof(usitems[item_id].type) === "undefined") { 
		if(parseFloat(qty_unit) <= 0) {
			$(this).val(old_unit);
			$(this).select2('val', old_unit);
			usitems[item_id].row.unit = old_unit;
			bootbox.alert(lang.no_match_found);
			// return;
		} else {
			usitems[item_id].row.unit = units;	
		}
	} else {
		usitems[item_id].row.unit = units;
	}
	usitems[item_id].project_qty = bal_project;
	localStorage.setItem('usitems', JSON.stringify(usitems));
	loadItems();
});
$(document).on("change", '.exp', function () {
	var row     = $(this).closest('tr');
	var expiry	= $(this).val(),
	item_id 	= row.attr('data-item-id');
	var qty_use	= row.find('#exp').find('option:selected').attr('qty');
	usitems[item_id].row.expiry  = (expiry != 'null' ? expiry : null);
	localStorage.setItem('usitems', JSON.stringify(usitems));
	loadItems();
});
$(document).on('click', '#btn_using', function () {
	var plan_id = $('#plan').val();
	var qty_use = new Array();
	$('.qty_use').each(function(i){
		var tr       = $(this).parent().parent();
		var unit_qty = tr.find('.unit option:selected').attr('qty');
		var qty_used = $(this).val();
		qty_use[i]   = (unit_qty?unit_qty:1) * qty_used;
	});
	var old_qty = new Array();
	$('.qty_old').each(function(i){
		old_qty[i] = $(this).val();
	});
	var in_plan = new Array();
	$('.have_plan').each(function(i){
		in_plan[i] = $(this).val();
	});
	var have_big = new Array();
	$('.qty_project').each(function(i){
		if ((qty_use[i] > ( parseFloat($(this).val()) + parseFloat(old_qty[i]) )) && in_plan[i] > 0 ) {
			have_big[i] = 1;
		} else {
			have_big[i] = 0;
		}
	});
	if (plan_id) {
		if (jQuery.inArray(1, have_big) !== -1) {
			bootbox.prompt({
				title: "Please insert password", 
				inputType: 'password',
				callback: function (result) {
					$.ajax({
						type: 'get',
						url: site.base_url+"products/checkPasswords/",
						dataType: "json",
						data: { password: result },
						success: function (data) {
							if (jQuery.inArray(1, data) !== -1) {
								$('#btn_submit').trigger('click');
							} else {
								return false;
							}
						}
					});
				}
			}); 
		} else {
			$('#btn_submit').trigger('click');
		}
	} else {
		$('#btn_submit').trigger('click');
	}
}); 
var delete_pro_id = "";
$(document).on('click', '.btn_delete', function () {
   delete_pro_id += ($(this).attr("id")+"_");
   $('#store_del_pro_id').val(delete_pro_id);
});
$('#from_location').change(function (e) {
	localStorage.setItem('from_location', $(this).val());
});
if (from_location = localStorage.getItem('from_location')) {
	$('#from_location').val(from_location);
}
$('#authorize_id').change(function (e) {
	localStorage.setItem('authorize_id', $(this).val());
});
if (authorize_id = localStorage.getItem('authorize_id')) {
	$('#authorize_id').val(authorize_id);
}   
$('#employee_id').change(function (e) {
	localStorage.setItem('employee_id', $(this).val());
});
if (employee_id = localStorage.getItem('employee_id')) {
	$('#employee_id').val(employee_id);
} 
$('#shop').change(function (e) {
	localStorage.setItem('shop', $(this).val());
});
if (shop = localStorage.getItem('shop')) {
	$('#shop').val(shop);
}  
$('#account').change(function (e) {
	localStorage.setItem('account', $(this).val());
});
if (account = localStorage.getItem('account')) {
	$('#account').val(account);
} 
$('#plan').change(function (e) {
	localStorage.setItem('plan', $(this).val());
});
if (plan = localStorage.getItem('plan')) {
	$('#plan').val(plan);
	$('#plan').select2().trigger('change');
}  
$('#address').change(function (e) {
	localStorage.setItem('address', $(this).val());
});
if (address = localStorage.getItem('address')) {
	$('#address').val(address);
} 
$(document).on("change", '.description', function () {
	var row 		= $(this).closest('tr');
	var descript 	= $(this).val(),
	item_id 		= row.attr('data-item-id');
	usitems[item_id].row.description = descript;
	localStorage.setItem('usitems', JSON.stringify(usitems));
	loadItems();
});    
$('#reset').click(function (e) {
	bootbox.confirm(lang.r_u_sure, function (result) {
		if (result) {
			if (localStorage.getItem('usitems')) {
				localStorage.removeItem('usitems');
			}
			if (localStorage.getItem('from_location')) {
				localStorage.removeItem('from_location');
			}
			if (localStorage.getItem('authorize_id')) {
				localStorage.removeItem('authorize_id');
			}
			if (localStorage.getItem('employee_id')) {
				localStorage.removeItem('employee_id');
			}
			if (localStorage.getItem('shop')) {
				localStorage.removeItem('shop');
			}
			if (localStorage.getItem('account')) {
				localStorage.removeItem('account');
			}
			$('#modal-loading').show();
			location.reload();
		}
	});
});
function loadItems() {
    if (localStorage.getItem('usitems')) {
        count = 1;
        $("#UsData tbody").empty();
        usitems = JSON.parse(localStorage.getItem('usitems'));
		$('#from_location').select2("readonly", true);
		item_description 		= '';
		item_reason      		= '';
		item_qty_use     		= 0;
		item_qty_by_unit     	= '';
		console.log(usitems);
        $.each(usitems, function () {
            var item 			= this;
            var item_id 		= site.settings.item_addition == 1 ? item.item_id : item.id;
            usitems[item_id] 	= item;
			var product_id 		= item.row.id, 
				item_code 		= item.row.code, 
				item_name 		= item.row.name, 
				item_label 		= item.label, 
				qoh 			= item.row.qoh, 
				unit_name 		= item.row.unit_name, 
				item_cost 		= item.row.cost, 
				item_unit 		= item.row.unit,  
				qty_plan 		= item.row.project_qty,  
				qty_old 		= item.row.qty_old,  
				item_proj		= item.project_qty,
				have_plan		= item.row.have_plan,
				stock_item_id 	= item.stock_item;
				item_qty_use    = formatDecimal(item.row.qty_use);
			var opt = $("<select id=\"unit\" name=\"unit\[\]\" style=\"padding-top: 2px !important;\" class=\"form-control unit\" />");
            if (item.option_unit !== false) {
            	let row_unit_variant = item.option_unit.find(o => o.unit_variant.toLowerCase() === item.row.unit.toLowerCase());
                $.each(item.option_unit, function () {
                	if (typeof(row_unit_variant) != "undefined") {
                		if(item.row.unit == this.unit){
					  		qoh = this.qty_unit;
							$("<option />", {value: this.unit, text: this.unit_variant, qty: this.qty_unit, selected: 'selected'}).appendTo(opt);
					  	} else {
					  		$("<option />", {value: this.unit, text: this.unit_variant, qty: this.qty_unit}).appendTo(opt);
					  	}
                	} else {
                		if(this.qty_unit > 0) {
				  			qoh = this.qty_unit;
							$("<option />", {value: this.unit, text: this.unit_variant, qty: this.qty_unit, selected: 'selected'}).appendTo(opt);  
				  		} else {
				  			$("<option />", {value: this.unit, text: this.unit_variant, qty: this.qty_unit}).appendTo(opt);
				  		}
                	}
				});
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
                opt = opt.hide();
            }
			var exp_date = $("<select id=\"exp\" name=\"exp\[\]\" style=\"padding-top: 2px !important;\" class=\"form-control exp\" />");
            if (item.expiry_date !== false && item.expiry_date !== undefined) {
                $.each(item.expiry_date, function () {
					if (item.row.expiry == this.expiry || (item.row.expiry == 'null' && this.expiry == null)) {
						$("<option />", {value: (this.expiry != null ? this.expiry : ''), text: (this.expiry != null ? fsd(this.expiry) : 'N/A'), qty: this.quantity_balance, selected: 'selected'}).appendTo(exp_date);
					} else {
						$("<option />", {value: (this.expiry != null ? this.expiry : ''), text: (this.expiry != null ? fsd(this.expiry) : 'N/A'), qty: this.quantity_balance}).appendTo(exp_date);  
					}
				});
            } else {
                $("<option />", {value: 0, text: 'N/A'}).appendTo(exp_date);
                exp_date = exp_date.hide();
            }
			if (item.row.description) {
				item_description = item.row.description;
			} else {
				item_description = '';
			}
			if (item.reason) {
				item_reason = item.reason;
			}
			var row_no = (new Date).getTime();
			var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');			
			tr_html = '<td><input type="hidden" value="'+ product_id +'" name="product_id[]"/><input type="hidden" value="'+ item_code +'" name="item_code[]"/><input type="hidden" value="'+ item_name +'" name="name[]"/><input type="hidden" value="'+ item_cost +'" name="cost[]"/> <input type="hidden" value="'+ stock_item_id +'" name="stock_item_id[]"/>'+ item_label +'</td>';
			if (site.settings.product_expiry == 1) {
				tr_html += '<td>'+(exp_date.get(0).outerHTML)+'</td>';
			}
			tr_html += '<td><input type="text" value="'+ item_description +'" class="form-control" name="description[]"/></td>';
			tr_html += '<td class="text-center">'+ formatQuantity2(qoh) +'</td>';
			tr_html += '<td><input type="text" value="'+ item_qty_use +'" class="form-control qty_use" name="qty_use[]" style="text-align:center !important;"/><input type="hidden" value="'+ qty_plan +'" class="qty_project" name="qty_project[]" /><input type="hidden" value="'+ qty_old +'" class="qty_old" name="qty_old[]" /><input type="hidden" value="'+ have_plan +'" class="have_plan" name="have_plan[]" /><input type="hidden" value="'+qoh+'" name="qoh[]" /></td>';
			tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';			
			tr_html += '<td class="bal_pro">'+ formatQuantity2(item_proj) +'</td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip usdel btn_delete" id="' + product_id + '" title="Remove" style="cursor:pointer;"></i></td>';
			count += 1;
			newTr.html(tr_html);
            newTr.appendTo("#UsData");
        });
    }
}
 
function add_using_stock_item(item) {
    if (count == 1) {
        usitems = {};
        $('#from_location').select2("readonly", true);
		$('#account').select2("readonly", true);
		$('#plan').select2("readonly", true);
		$('#address').select2("readonly", true);
		$('#shop').select2("readonly", true);
    }
    if (item == null) {
        return;
    }
	var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (usitems[item_id]) {
        usitems[item_id].row.qty = parseFloat(usitems[item_id].row.qty) + 1;
    } else {
        usitems[item_id] = item;
	}
    localStorage.setItem('usitems', JSON.stringify(usitems));
    loadItems();
    return true;
}