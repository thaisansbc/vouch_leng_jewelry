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
if (localStorage.getItem('finishitems')) {
	FinishloadItems();
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
$(document).on('click', '.finishdel', function () {
	var row = $(this).closest('tr');
	var item_id = row.attr('data-item-id');
	delete finishitems[item_id];
	row.remove();
    if (finishitems.hasOwnProperty(item_id)) {} 
    else {
    	localStorage.setItem('finishitems', JSON.stringify(finishitems));	
		FinishloadItems();
    	return;
 	}
	// loadItems();
});

// Using Stock
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
//End Using Stock

// Finish Stock
var old_row_qty_finish = 0;
$(document).on("focus", '.qty_finish', function () {
	old_row_qty_finish = $(this).val();
}).on("change", '.qty_finish', function () {
	var m = 2;		
	var row = $(this).closest('tr');
	if (!is_numeric($(this).val()) || parseFloat($(this).val()) <= 0) {
		$(this).val(old_row_qty_finish);
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
	finishitems[item_id].project_qty = bal_project;
	finishitems[item_id].row.qty_use = new_qty;
	localStorage.setItem('finishitems', JSON.stringify(finishitems));
	FinishloadItems();
});
var old_unit_finish;
$(document).on('focusin', '.unit_finish', function(){
	old_unit_finish = $(this).val();
}).on("change", '.unit_finish', function () {
	var row 		= $(this).closest('tr');
	var units	 	= $(this).val(),
	item_id 		= row.attr('data-item-id');
	var bal_project	= 0;
	var qty_project = row.find('.qty_project').val();
	var qty_old     = row.find('.qty_old').val();
	var new_qty     = row.find('.qty_finish').val();
	var qty_unit	= row.find('#unit').find('option:selected').attr('qty');
	var qty_exp 	= row.find('#exp').find('option:selected').attr('qty');
	if (qty_project > 0) {
		bal_project = (parseFloat(qty_project) + parseFloat(qty_old)) - ( (new_qty?parseFloat(new_qty):0) * (qty_unit?parseFloat(qty_unit):1) );
	}
	if (site.settings.product_expiry == 1) {
		if (qty_exp < (new_qty * qty_unit)) {
			$(this).val(old_unit_finish);
			$(this).select2('val', old_unit_finish);
			bootbox.alert(lang.unexpected_value);
			return;
		}
	}
	if (typeof(finishitems[item_id].type) === "undefined") {
		if(parseFloat(qty_unit) <= 0) {
			$(this).val(old_unit_finish);
			$(this).select2('val', old_unit_finish);
			finishitems[item_id].row.unit = old_unit_finish;
			bootbox.alert(lang.no_match_found);
			// return;
		} else {
			finishitems[item_id].row.unit = units;
		}
	} else {
		finishitems[item_id].row.unit = units;
	}
	finishitems[item_id].project_qty = bal_project;
	localStorage.setItem('finishitems', JSON.stringify(finishitems));
	FinishloadItems();
});
$(document).on("change", '.exp_finish', function () {
	var row     = $(this).closest('tr');
	var expiry	= $(this).val(),
	item_id 	= row.attr('data-item-id');
	var qty_use	= row.find('#exp').find('option:selected').attr('qty');
	finishitems[item_id].row.expiry  = (expiry != 'null' ? expiry : null);
	localStorage.setItem('finishitems', JSON.stringify(finishitems));
	FinishloadItems();
});
$(document).on('click', '#btn_finish', function () {
	var plan_id = $('#plan').val();
	var qty_use = new Array();
	$('.qty_finish').each(function(i){
		var tr       = $(this).parent().parent();
		var unit_qty = tr.find('.unit option:selected').attr('qty');
		var qty_used = $(this).val();
		qty_use[i]   = (unit_qty?unit_qty:1) * qty
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

var delete_pro_id_finish = "";
$(document).on('click', '.btn_delete_finish', function () {
	   delete_pro_id_finish += ($(this).attr("id")+"_");
	   $('#store_del_pro_id_finish').val(delete_pro_id_finish);
});
//End Finish Stock
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
	  $(document).on('click', '.edit_finish', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');

        item_id = row.attr('data-item-id');
        item = 	finishitems[item_id];
		var item_code = item.row.code;
        $('#row_id').val(row_id);
        var td_combo = '';
		var total_stone_setting = 0;
        if (item.combo_items) {

            $.each(item.combo_items, function() {
				var combo_row_id = this.row_id ? this.row_id : row_id;
				if (this.type != 'return' || this.id == item.item_id) {
					td_combo += '<tr data-item-id="'+item_id+'" combo-item-id="'+combo_row_id+'">';
						if (this.type == 'return') {
							td_combo += '<td><i class="fa fa-undo"></i></td>';
						} else {
							td_combo += '<td><i class="fa fa-cogs"></i></td>';
						}
						td_combo += '<td><input value="'+this.id+'" type="hidden" class="combo_product_id"/><input value="'+combo_row_id+'" type="hidden" class="combo_row_id"/><input value="'+this.type+'" type="hidden" class="combo_type"/><input type="hidden" class="combo_code" value="'+this.code+'"/><input type="hidden" class="combo_name" value="'+this.name+'"/><input value="'+this.name+' ('+this.code+')" class="form-control tip combo_product" type="text"/></td>';
						if (site.settings.qty_operation == 1) {
							td_combo += '<td class="text-center"><input value="'+formatDecimal(this.width)+'" class="form-control text-right combo_width" type="text"/></td>';
							td_combo += '<td class="text-center"><input value="'+formatDecimal(this.height)+'" class="form-control text-right combo_height" type="text"/></td>';
						}
						if (this.type == 'use') {
							td_combo += '<td class="text-right"><input value="'+formatDecimal(this.wax_setting_qty)+'" class="form-control text-right wax_setting_qty" type="text"/></td>';
							td_combo += '<td class="text-right"><input value="'+formatDecimal(this.casting_qty)+'" class="form-control text-right casting_qty" type="text"/></td>';
							td_combo += '<td class="text-right"><input value="'+formatDecimal(this.filing_pre_polishing_qty)+'" class="form-control text-right filing_pre_polishing_qty" type="text"/></td>';
							td_combo += '<td class="text-right"><input value="'+formatDecimal(this.stone_setting_qty)+'" class="form-control text-right stone_setting_qty" type="text"/></td>';
						}else {
							td_combo += '<td class="text-right"></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td>';
						}
						console.log(this);
						
						if (this.type == 'return') {
						td_combo += '<td class="text-right"><input value="'+(this.casting_qty == null ? formatDecimal(this.final_polishing_qty) : formatDecimal(this.total_stone_setting_qty))+'" class="form-control text-right final_polishing_qty" type="text"/></td>'; 
						td_combo += '<td class="text-right"><input value="'+(this.casting_qty == null ? formatDecimal(this.quality_inspection_qty) : formatDecimal(this.total_stone_setting_qty))+'" class="form-control text-right quality_inspection_qty" type="text"/></td>';
						td_combo += '<td class="text-right"><input value="'+(this.casting_qty == null ? formatDecimal(this.packaging_qty) : formatDecimal(this.total_stone_setting_qty))+'" class="form-control text-right combo_qty packaging_qty" type="text"/></td>';
						} else {
							td_combo += '<td class="text-right"></td>';
							td_combo += '<td class="text-right"></td>';
							td_combo += '<td class="text-right"></td>';
						}
						td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text" value="'+formatDecimal(this.price)+'"/></td>';
						td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
					td_combo += '/tr>';
				}
            });
        }
        $('#comboProduct tbody').html(td_combo);
		$('#row_id').val(row_id);
        $('#comboModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#comboModal').appendTo("body").modal('show');
    });
	$(document).on('click', '#add_comboProduct', function () {
		var item_id = $('#row_id').val();
        var td_combo = '<tr data-item-id="'+item_id+'">';
            td_combo += '<td><input type="hidden" class="combo_product_id"/><input type="hidden" class="combo_name"/><input type="hidden" class="combo_code" /><input class="form-control tip combo_product" type="text"/></td>';
            if (site.settings.qty_operation == 1) {
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_width" type="text"/></td>';
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_height" type="text"/></td>';
            }
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right wax_setting_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right casting_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right filing_pre_polishing_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right stone_setting_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right final_polishing_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right quality_inspection_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_qty packaging_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text"/></td>';
            td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
            td_combo += '</tr>';
        $('#comboProduct tbody').append(td_combo);  
    });
	
    $(document).on('click', '.delete_combo_product', function () {
        var parent = $(this).parent().parent();
        parent.remove();
        return false;
    });
    $(document).on('click', '#editCombo', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var combo_row_id = row.attr('combo-item-id');
        var combo_items = [];
        var finish_package = [];
        var unit_price = 0;
        $('.combo_product_id').each(function(){
            var parent = $(this).parent().parent();
            var product_id = $(this).val();
			var combo_row_id = parent.find('.combo_row_id').val();
			var combo_type = parent.find('.combo_type').val();
            var product_name = parent.find('.combo_name').val();
            var product_code = parent.find('.combo_code').val();
            var product_price = parent.find('.combo_price').val() - 0;
			var wax_setting_qty = parent.find('.wax_setting_qty').val() - 0;
			var casting_qty = parent.find('.casting_qty').val() - 0;
			var filing_pre_polishing_qty = parent.find('.filing_pre_polishing_qty').val() - 0;
			var stone_setting_qty = parent.find('.stone_setting_qty').val() - 0;
			var total_stone_setting_qty = parent.find('.total_stone_setting_qty').val() - 0;
			var final_polishing_qty = parent.find('.final_polishing_qty').val() - 0;
			var quality_inspection_qty = parent.find('.quality_inspection_qty').val() - 0;
			var packaging_qty = parent.find('.packaging_qty').val() - 0;
			if (product_id > 0) {
				var combo_product = {
					row_id: combo_row_id,
					type: combo_type,
					id: product_id,
					name: product_name,
					code: product_code,
					price: product_price,
					wax_setting_qty: wax_setting_qty,
					casting_qty: casting_qty,
					filing_pre_polishing_qty: filing_pre_polishing_qty,
					stone_setting_qty: stone_setting_qty,
					total_stone_setting_qty: total_stone_setting_qty,
					final_polishing_qty: final_polishing_qty,
					quality_inspection_qty: quality_inspection_qty,
					packaging_qty: packaging_qty,
				};
				if (combo_type == 'return') {
					finish_package.push(packaging_qty);
				}
				combo_items.push(combo_product);
				unit_price += (product_price * packaging_qty);
			}
        }); 
        finishitems[item_id].combo_items = combo_items;
		finishitems[item_id].row.combo_price = unit_price;
		finishitems[item_id].row.qty_use = finish_package[0];
        localStorage.setItem('finishitems', JSON.stringify(finishitems));
        $('#comboModal').modal('hide');
        FinishloadItems();
        return;
    });
    var old_value;
    $(document).on("focus", '.wax_setting_qty, .casting_qty, .filing_pre_polishing_qty, .stone_setting_qty, .final_polishing_qty, .quality_inspection_qty, .packaging_qty, .combo_price', function () {
        old_value = $(this).val();
    }).on("change", '.wax_setting_qty, .casting_qty, .filing_pre_polishing_qty, .stone_setting_qty, .final_polishing_qty, .quality_inspection_qty, .packaging_qty, .combo_price', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id'); 
		var combo_type = row.find('.combo_type').val();
		if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
			$(this).val(old_value);	
			bootbox.alert(lang.unexpected_value);
			return;
		}
		if (combo_type === "use") {
			// if big value update next value update too but i have update medium value not want to update big value
			if (parseFloat($(this).closest('tr').find('.wax_setting_qty').val()) < parseFloat($(this).closest('tr').find('.casting_qty').val())) {
				$(this).closest('tr').find('.casting_qty').val($(this).closest('tr').find('.wax_setting_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.casting_qty').val()) < parseFloat($(this).closest('tr').find('.filing_pre_polishing_qty').val())) {
				$(this).closest('tr').find('.filing_pre_polishing_qty').val($(this).closest('tr').find('.casting_qty').val());
			}	
			if (parseFloat($(this).closest('tr').find('.filing_pre_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.stone_setting_qty').val())) {
				$(this).closest('tr').find('.stone_setting_qty').val($(this).closest('tr').find('.filing_pre_polishing_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.stone_setting_qty').val()) < parseFloat($(this).closest('tr').find('.final_polishing_qty').val())) {
				$(this).closest('tr').find('.final_polishing_qty').val($(this).closest('tr').find('.stone_setting_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.final_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.quality_inspection_qty').val())) {
				$(this).closest('tr').find('.quality_inspection_qty').val($(this).closest('tr').find('.final_polishing_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) < parseFloat($(this).closest('tr').find('.packaging_qty').val())) {
				$(this).closest('tr').find('.packaging_qty').val($(this).closest('tr').find('.quality_inspection_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.wax_setting_qty').val()) < parseFloat($(this).closest('tr').find('.casting_qty').val()) || parseFloat($(this).closest('tr').find('.casting_qty').val()) < parseFloat($(this).closest('tr').find('.filing_pre_polishing_qty').val()) || parseFloat($(this).closest('tr').find('.filing_pre_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.stone_setting_qty').val()) || parseFloat($(this).closest('tr').find('.stone_setting_qty').val()) < parseFloat($(this).closest('tr').find('.final_polishing_qty').val()) || parseFloat($(this).closest('tr').find('.final_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) || parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) < parseFloat($(this).closest('tr').find('.packaging_qty').val())) {
				$(this).val(old_value);	
				bootbox.alert(lang.wax_setting_qty + ' >= ' + lang.casting_qty + ' >= ' + lang.filing_pre_polishing_qty + ' >= ' + lang.stone_setting_qty + ' >= ' + lang.final_polishing_qty + ' >= ' + lang.quality_inspection_qty + ' >= ' + lang.packaging_qty
				);
				return;
			}
		}else if (combo_type === "return") {
			if (parseFloat($(this).closest('tr').find('.final_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.quality_inspection_qty').val())) {
				$(this).closest('tr').find('.quality_inspection_qty').val($(this).closest('tr').find('.final_polishing_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) < parseFloat($(this).closest('tr').find('.packaging_qty').val())) {
				$(this).closest('tr').find('.packaging_qty').val($(this).closest('tr').find('.quality_inspection_qty').val());
			}
			if (parseFloat($(this).closest('tr').find('.final_polishing_qty').val()) < parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) || parseFloat($(this).closest('tr').find('.quality_inspection_qty').val()) < parseFloat($(this).closest('tr').find('.packaging_qty').val())) {
				$(this).val(old_value);
				bootbox.alert(lang.final_polishing_qty + ' >= ' + lang.quality_inspection_qty + ' >= ' + lang.packaging_qty);
				return;
			}
		}
		// finishitems[item_id].combo_items[row.attr('combo-item-id')][$(this).attr('class').split(' ')[0]] = $(this).val() - 0;
		// if ($(this).hasClass('combo_price')) {
		// 	finishitems[item_id].row.combo_price = 0;
		// 	$.each(finishitems[item_id].combo_items, function () {
		// 		finishitems[item_id].row.combo_price += (this.price * this.packaging_qty);
		// 	});
		// }
			// if big value update next value update too but i have update medium value not want to update big value
        // if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
        //     $(this).val(old_value);
        //     bootbox.alert(lang.unexpected_value);
        //     return;
        // }
    });
	$(document).on("change", '.description', function () {
	var row 		= $(this).closest('tr');
	var descript 	= $(this).val(),
	item_id 		= row.attr('data-item-id');
	usitems[item_id].row.description = descript;
	localStorage.setItem('usitems', JSON.stringify(usitems));
	loadItems();
});   
$(document).on("change", '.description_finish', function () {
	var row 		= $(this).closest('tr');
	var descript 	= $(this).val(),
	item_id 		= row.attr('data-item-id');
	finishitems[item_id].row.description = descript;
	localStorage.setItem('finishitems', JSON.stringify(finishitems));
	FinishloadItems();
});  
$('#reset').click(function (e) {
	bootbox.confirm(lang.r_u_sure, function (result) {
		if (result) {
			if (localStorage.getItem('usitems')) {
				localStorage.removeItem('usitems');
			}
			if (localStorage.getItem('finishitems')) {
				localStorage.removeItem('finishitems');
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
		// console.log(usitems);
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
			tr_html += '<td><input type="text" value="'+ item_description +'" class="form-control description"  name="description[]"/></td>';
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
function FinishloadItems() {
    if (localStorage.getItem('finishitems')) {
        finishcount = 1;
        $("#PFinish tbody").empty();
        finishitems = JSON.parse(localStorage.getItem('finishitems'));
		$('#from_location').select2("readonly", true);
		item_description 		= '';
		item_reason      		= '';
		item_qty_use     		= 0;
		item_qty_by_unit     	= '';
		console.log(finishitems);
        $.each(finishitems, function () {
            var item 			= this;
            var item_id 		= site.settings.item_addition == 1 ? item.item_id : item.id;
            finishitems[item_id] 	= item;
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
			var opt = $("<select id=\"unit_finish\" name=\"unit_finish\[\]\" style=\"padding-top: 2px !important;\" class=\"form-control unit_finish\" />");
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
			var exp_date = $("<select id=\"exp_finish\" name=\"exp_finish\[\]\" style=\"padding-top: 2px !important;\" class=\"form-control exp_finish\" />");
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
			console.log(item);
			
			var row_no = (new Date).getTime();
			var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');			
			tr_html = '<td><input type="hidden" value="'+ product_id +'" name="product_id_finish[]"/><input type="hidden" value="'+ item_code +'" name="item_code_finish[]"/><input type="hidden" value="'+ item_name +'" name="name_finish[]"/><input type="hidden" value="'+ item_cost +'" name="cost_finish[]"/> <input type="hidden" value="'+ stock_item_id +'" name="stock_item_id_finish[]"/>'+ item_label +'<i class="pull-right fa fa-edit tip pointer edit_finish" id="' +row_no +'" data-item="' +item_id +'" title="Edit" style="cursor:pointer"></i></td>';
			if (site.settings.product_expiry == 1) {
				tr_html += '<td>'+(exp_date.get(0).outerHTML)+'</td>';
			}
			tr_html += '<td><input type="text" value="'+ item_description +'" class="form-control description_finish"  name="description_finish[]"/></td>';
			tr_html += '<td class="text-center">'+ formatQuantity2(qoh) +'</td>';
			tr_html += '<td><input type="text" value="'+ item_qty_use +'" class="form-control qty_finish" name="qty_finish[]" style="text-align:center !important;"/><input type="hidden" value="'+ qty_plan +'" class="qty_project" name="qty_project_finish[]" /><input type="hidden" value="'+ qty_old +'" class="qty_old" name="qty_old_finish[]" /><input type="hidden" value="'+ have_plan +'" class="have_plan" name="have_plan_finish[]" /><input type="hidden" value="'+qoh+'" name="qoh_finish[]" /></td>';
			tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';			
			tr_html += '<td class="bal_pro">'+ formatQuantity2(item_proj) +'</td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip finishdel btn_delete_finish" id="' + product_id + '" title="Remove" style="cursor:pointer;"></i></td>';
			finishcount += 1;
			newTr.html(tr_html);
            newTr.appendTo("#PFinish");
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
function add_finish_item(item) {
    if (finishcount == 1) {
        finishitems = {};
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
    if (finishitems[item_id]) {
        finishitems[item_id].row.qty = parseFloat(finishitems[item_id].row.qty) + 1;
    } else {
        finishitems[item_id] = item;
	}
    localStorage.setItem('finishitems', JSON.stringify(finishitems));
    FinishloadItems();
    return true;
}