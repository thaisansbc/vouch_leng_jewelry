$(document).ready(function () {
    
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
// Order level and discoutn localStorage
    $('#rcstatus').change(function (e) {
        localStorage.setItem('rcstatus', $(this).val());
    });
    if (rcstatus = localStorage.getItem('rcstatus')) {
        $('#rcstatus').select2("val", rcstatus);
    }
    // If there is any item in localStorage
    if (localStorage.getItem('rcitems')) {
        loadItems();
    }
    // clear localStorage and reload
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('rcitems')) {
                        localStorage.removeItem('rcitems');
                    }
                    if (localStorage.getItem('rcref')) {
                        localStorage.removeItem('rcref');
                    }
                    if (localStorage.getItem('rcwarehouse')) {
                        localStorage.removeItem('rcwarehouse');
                    }
                    if (localStorage.getItem('rcnote')) {
                        localStorage.removeItem('rcnote');
                    }
                    if (localStorage.getItem('rccustomer')) {
                        localStorage.removeItem('rccustomer');
                    }
                    if (localStorage.getItem('rcdate')) {
                        localStorage.removeItem('rcdate');
                    }
                    if (localStorage.getItem('rcstatus')) {
                        localStorage.removeItem('rcstatus');
                    }
					if (localStorage.getItem('rcphone')) {
                        localStorage.removeItem('rcphone');
                    }
					if (localStorage.getItem('rcbrand')) {
                        localStorage.removeItem('rcbrand');
                    }
					if (localStorage.getItem('rcmodel')) {
                        localStorage.removeItem('rcmodel');
                    }
					if (localStorage.getItem('rcmachine_type')) {
                        localStorage.removeItem('rcmachine_type');
                    }
					if (localStorage.getItem('rcimei_number')) {
                        localStorage.removeItem('rcimei_number');
                    }
					if (localStorage.getItem('rctechnician')) {
                        localStorage.removeItem('rctechnician');
                    }
					if (localStorage.getItem('rcreceive_date')) {
                        localStorage.removeItem('rcreceive_date');
                    }
                    if (localStorage.getItem('rcbiller')) {
                        localStorage.removeItem('rcbiller');
                    }
                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

	// save and load the fields in and/or from localStorage

    $('#rcref').change(function (e) {
        localStorage.setItem('rcref', $(this).val());
    });
    if (rcref = localStorage.getItem('rcref')) {
        $('#rcref').val(rcref);
    }
    $('#rcwarehouse').change(function (e) {
        localStorage.setItem('rcwarehouse', $(this).val());
    });
    if (rcwarehouse = localStorage.getItem('rcwarehouse')) {
        $('#rcwarehouse').select2("val", rcwarehouse);
    }

    $('#rcnote').redactor('destroy');
    $('#rcnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('rcnote', v);
        }
    });
    if (rcnote = localStorage.getItem('rcnote')) {
        $('#rcnote').redactor('set', rcnote);
    }
	
    var $customer = $('#rccustomer');
    $customer.change(function (e) {
        localStorage.setItem('rccustomer', $(this).val());
    });
    if (rccustomer = localStorage.getItem('rccustomer')) {
        $customer.val(rccustomer).select2({
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

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        /*if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }*/
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */

     $(document).on('click', '.rdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete rcitems[item_id];
        row.remove();
        if(rcitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('rcitems', JSON.stringify(rcitems));
            loadItems();
            return;
        }
    });

    $(document).on("change", '.rtroubleshooting', function () {
         var row = $(this).closest('tr'),
             troubleshooting = $(this).val();
             item_id = row.attr('data-item-id');
         rcitems[item_id].row.troubleshooting = troubleshooting;
         localStorage.setItem('rcitems', JSON.stringify(rcitems));
         loadItems();
    });

    $(document).on("change", '.rsymptom', function () {
        var row = $(this).closest('tr'),
            symptom = $(this).val();
            item_id = row.attr('data-item-id');
        rcitems[item_id].row.symptom = symptom;
        localStorage.setItem('rcitems', JSON.stringify(rcitems));
        loadItems();
    });

});
/* -----------------------
 * Misc Actions
 ----------------------- */
// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#rccustomer').select2({
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
    if (localStorage.getItem('rcitems')) {
        count = 1;
        an = 1;
        $("#rcTable tbody").empty();
        rcitems = JSON.parse(localStorage.getItem('rcitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(rcitems, function(o){return [parseInt(o.order)];}) :   rcitems;
        $('#add_check, #edit_check, #add_check_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var row_no = item_id;
            var diagnostic_id = item.row.id, item_code = item.row.code, item_name = item.row.name, characteristic = item.row.characteristic, symptom = item.row.symptom, troubleshooting = item.row.troubleshooting;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="diagnostic_id[]" type="hidden" class="rid" value="' + diagnostic_id + '"><input name="name[]" type="hidden" class="rname" value="' + item_name + '"><span class="sname" id="name_' + row_no + '">' + item_name +' </span> </td>';
            tr_html += '<td><input type="text" class="form-control rcharacteristic" name="characteristic[]" value="'+characteristic+'" /></td>';
            tr_html += '<td class="text-right"><input type="text" name="symptom[]" value="'+symptom+'" class="form-control rsymptom" /></td>';
            tr_html += '<td class="text-right"><input type="text" name="troubleshooting[]" value="'+troubleshooting+'" class="form-control rtroubleshooting" /></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer rdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#rcTable");
            count += 1;
            an++;
        });

        var col = 4;
        var tfoot = '<tr id="tfoot" class="tfoot active"></th><th colspan="'+col+'"></th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#rcTable tfoot').html(tfoot);
        $('#total_items').val((parseFloat(count) - 1));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		if(count > 1){
			$('#rcphone').prop("readonly", true);
			$('#rccustomer').prop("readonly", true);
            $('#rcwarehouse').select2("readonly", true);
			$('#rcbrand').select2("readonly", true);
            $('#rcmodel').select2("readonly", true);
			$('#rcmachine_type').select2("readonly", true);
		}else{
			$('#rcphone').prop("readonly", false);
			$('#rccustomer').prop("readonly", false);
            $('#rcwarehouse').select2("readonly", false);
			$('#rcbrand').select2("readonly", false);
            $('#rcmodel').select2("readonly", false);
			$('#rcmachine_type').select2("readonly", false);
		}
        set_page_focus();
    }
}

/* -----------------------------
 * Add Quotation Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {
    if (count == 1) {
        rcitems = {};
        if ($('#rcwarehouse').val() && $('#rccustomer').val()) {
            $('#rccustomer').select2("readonly", true);
            $('#rcwarehouse').select2("readonly", true);
            $('#rcbrand').select2("readonly", true);
            $('#rcmodel').select2("readonly", true);
			$('#rcmachine_type').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }

    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (rcitems[item_id]) {
        var new_qty = parseFloat(rcitems[item_id].row.qty) + 1;
        rcitems[item_id].row.qty = new_qty;
    } else {
        rcitems[item_id] = item;
    }
    rcitems[item_id].order = new Date().getTime();
    localStorage.setItem('rcitems', JSON.stringify(rcitems));
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
