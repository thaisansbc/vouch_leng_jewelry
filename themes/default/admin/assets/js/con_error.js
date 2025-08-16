$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('coneritems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('coneritems')) {
					localStorage.removeItem('coneritems');
				}
				if (localStorage.getItem('conerref')) {
					localStorage.removeItem('conerref');
				}
				if (localStorage.getItem('conerwarehouse')) {
					localStorage.removeItem('conerwarehouse');
				}
				if (localStorage.getItem('conernote')) {
					localStorage.removeItem('conernote');
				}
				if (localStorage.getItem('conerdate')) {
					localStorage.removeItem('conerdate');
				}
				if (localStorage.getItem('conerbiller')) {
					localStorage.removeItem('conerbiller');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#conerref').change(function (e) {
        localStorage.setItem('conerref', $(this).val());
    });
    if (conerref = localStorage.getItem('conerref')) {
        $('#conerref').val(conerref);
    }
    $('#conerwarehouse').change(function (e) {
        localStorage.setItem('conerwarehouse', $(this).val());
    });
    if (conerwarehouse = localStorage.getItem('conerwarehouse')) {
        $('#conerwarehouse').select2("val", conerwarehouse);
    }
    $('#conernote').redactor('destroy');
    $('#conernote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('conernote', v);
        }
    });
    if (conernote = localStorage.getItem('conernote')) {
        $('#conernote').redactor('set', conernote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.confudel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete coneritems[item_id];
		row.remove();
		if(coneritems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('coneritems', JSON.stringify(coneritems));
			loadItems();
			return;
		}
	});

    var old_quantity;
    $(document).on("focus", '.rquantity', function () {
        old_quantity = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_quantity);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var quantity = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        coneritems[item_id].row.quantity = quantity;
        localStorage.setItem('coneritems', JSON.stringify(coneritems));
        loadItems();
    });
	$(document).on("change", '.driver_id', function () {
        var row = $(this).closest('tr');
        var driver_id = $(this).val(),
        item_id = row.attr('data-item-id');
        coneritems[item_id].row.driver_id = driver_id;
        localStorage.setItem('coneritems', JSON.stringify(coneritems));
        loadItems();
    });
});



function loadItems() {
    if (localStorage.getItem('coneritems')) {
        count = 1;
        an = 1;
        $("#conErrorTable tbody").empty();
        coneritems = JSON.parse(localStorage.getItem('coneritems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(coneritems, function(o){return [parseInt(o.order)];}) :   coneritems;
        $('#add_error, #edit_error, #add_error_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
			var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            var row_no = item_id;
			var stregth_id = item.row.id;
			var quantity = item.row.quantity;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="stregth_id[]" type="hidden" class="rid" value="' + stregth_id + '">'+item.row.code+'</td>';
			tr_html += '<td>'+item.row.name+'</td>';
			tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + quantity + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer confudel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conErrorTable");
			$('select').select2();
            count += parseFloat(quantity);
            an++;
        });
        var col = 2;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#conErrorTable tfoot').html(tfoot);
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}


function add_invoice_item(item) {
    if (count == 1) {
        coneritems = {};
        if ($('#conerwarehouse').val()) {
            $('#conerwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (coneritems[item_id]) {
        var new_qty = parseFloat(coneritems[item_id].row.quantity) + 1;
        coneritems[item_id].row.quantity = new_qty;
    } else {
        coneritems[item_id] = item;
    }
    coneritems[item_id].order = new Date().getTime();
    localStorage.setItem('coneritems', JSON.stringify(coneritems));
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
