$(document).ready(function () {
    if (!localStorage.getItem('csref')) {
        localStorage.setItem('csref', '');
    }

    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    //localStorage.clear();
    // If there is any item in localStorage
    if (localStorage.getItem('csitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('csitems')) {
                    localStorage.removeItem('csitems');
                }
                if (localStorage.getItem('csref')) {
                    localStorage.removeItem('csref');
                }
				if (localStorage.getItem('cscustomer')) {
					localStorage.removeItem('cscustomer');
				}
                if (localStorage.getItem('cswarehouse')) {
                    localStorage.removeItem('cswarehouse');
                }
                if (localStorage.getItem('csnote')) {
                    localStorage.removeItem('csnote');
                }
                if (localStorage.getItem('csdate')) {
                    localStorage.removeItem('csdate');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#csref').change(function (e) {
        localStorage.setItem('csref', $(this).val());
    });
    if (csref = localStorage.getItem('csref')) {
        $('#csref').val(csref);
    }
	
	$('#csexpiry').change(function (e) {
        localStorage.setItem('csexpiry', $(this).val());
    });
    if (csexpiry = localStorage.getItem('csexpiry')) {
        $('#csexpiry').val(csexpiry);
    }
	
	$('#cscustomer').change(function (e) {
        localStorage.setItem('cscustomer', $(this).val());
    });
    if (cscustomer = localStorage.getItem('cscustomer')) {
        $('#cscustomer').select2("val", cscustomer);
    }
	
    $('#cswarehouse').change(function (e) {
        localStorage.setItem('cswarehouse', $(this).val());
    });
    if (cswarehouse = localStorage.getItem('cswarehouse')) {
        $('#cswarehouse').select2("val", cswarehouse);
    }

    //$(document).on('change', '#csnote', function (e) {
        $('#csnote').redactor('destroy');
        $('#csnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('csnote', v);
            }
        });
        if (csnote = localStorage.getItem('csnote')) {
            $('#csnote').redactor('set', csnote);
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


    /* ---------------------- 
     * Delete Row Method 
     * ---------------------- */

    $(document).on('click', '.csdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete csitems[item_id];
        row.remove();
		
        if(csitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('csitems', JSON.stringify(csitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
	var old_row_qty = 0;
    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        csitems[item_id].row.base_quantity = new_qty;
        if(csitems[item_id].row.unit != csitems[item_id].row.base_unit) {
            $.each(csitems[item_id].units, function(){
                if (this.id == csitems[item_id].row.unit) {
                    csitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        csitems[item_id].row.qty = new_qty;		       
        localStorage.setItem('csitems', JSON.stringify(csitems));
        loadItems();
    });
	
	$(document).on("focus", '.rserial', function () {
    }).on("change", '.rserial', function () {
        var row = $(this).closest('tr');
        item_id = row.attr('data-item-id');
		var new_serial = $(this).val();
        csitems[item_id].row.serial = new_serial;
        localStorage.setItem('csitems', JSON.stringify(csitems));
    });


    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
        item_id = row.attr('data-item-id');
        csitems[item_id].row.option = new_opt;
        localStorage.setItem('csitems', JSON.stringify(csitems));
    });
	
	$(document).on('change', '.punit', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');	
		var parent = $(this).parent().parent();
		var rquantity = parent.find(".rquantity").val();
		
		if (!is_numeric(rquantity) || parseFloat(rquantity) < 0) {
			$(this).val(old_row_qty);
			bootbox.alert(lang.unexpected_value);
			return;
		}		
		var unit = $(this).val();
		var base_quantity = rquantity;
		if(unit != csitems[item_id].row.base_unit) {
			$.each(csitems[item_id].units, function() {				
				if (this.id == unit) {
					base_quantity = unitToBaseQty(parseFloat(rquantity), this);
				}
			});
		} 
		csitems[item_id].row.qty = parseFloat(rquantity),
		csitems[item_id].row.base_quantity = parseFloat(base_quantity),
		csitems[item_id].row.unit = unit,
		localStorage.setItem('csitems', JSON.stringify(csitems));
        loadItems();
	});

});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('csitems')) {
        count = 1;
        an = 1;
        $("#csTable tbody").empty();
        csitems = JSON.parse(localStorage.getItem('csitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(csitems, function(o){return [parseInt(o.order)];}) : csitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_qty = item.row.qty, item_option = item.row.option,item_expiry = item.row.expiry, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : '';            			
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
			
            var opt = $("<select id=\"poption\" name=\"variant\[\]\" class=\"form-control select rvariant\" />");
            if(item.options !== false) {
                $.each(item.options, function () {
                    if (item.row.option == this.id)
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt);
                    else
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
                opt = opt.hide();
            }			
			
			var opt2 = $("<select id=\"unit\" name=\"unit\[\]\" class=\"form-control select punit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
                    if (item.row.unit == this.id)
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt2);
                    else
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt2);
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt2);
                opt2 = opt2.hide();
            }
			
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
			tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';
			tr_html += '<td>'+(opt2.get(0).outerHTML)+'<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
            if (site.settings.product_serial == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }
			tr_html += '<td class="text-center"><i class="fa fa-times tip csdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#csTable");
            count += parseFloat(item_qty);
            an++;
        });
		
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.product_serial == 1) { tfoot += '<th></th>'; }
		tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#csTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		if (count > 1) {
            $('#cscustomer').select2("readonly", true);
			$('#cswarehouse').select2("readonly", true);
        }
        set_page_focus();
    }
}


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_cs_item(item) {

    if (count == 1) {
        csitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (csitems[item_id]) {
        var new_qty = parseFloat(csitems[item_id].row.qty) + 1;
        csitems[item_id].row.base_quantity = new_qty;
        if(csitems[item_id].row.unit != csitems[item_id].row.base_unit) {
            $.each(csitems[item_id].units, function(){
                if (this.id == csitems[item_id].row.unit) {
                    csitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        csitems[item_id].row.qty = new_qty;

    } else {
        csitems[item_id] = item;
    }
    csitems[item_id].order = new Date().getTime();
    localStorage.setItem('csitems', JSON.stringify(csitems));
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