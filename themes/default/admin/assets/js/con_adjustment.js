$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('conadjitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('conadjitems')) {
					localStorage.removeItem('conadjitems');
				}
				if (localStorage.getItem('conadjref')) {
					localStorage.removeItem('conadjref');
				}
				if (localStorage.getItem('conadjwarehouse')) {
					localStorage.removeItem('conadjwarehouse');
				}
				if (localStorage.getItem('conadjnote')) {
					localStorage.removeItem('conadjnote');
				}
				if (localStorage.getItem('conadjdate')) {
					localStorage.removeItem('conadjdate');
				}
				if (localStorage.getItem('conadjbiller')) {
					localStorage.removeItem('conadjbiller');
				}
				if (localStorage.getItem('conadjfrom_date')) {
					localStorage.removeItem('conadjfrom_date');
				}
				if (localStorage.getItem('conadjto_date')) {
					localStorage.removeItem('conadjto_date');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#conadjref').change(function (e) {
        localStorage.setItem('conadjref', $(this).val());
    });
    if (conadjref = localStorage.getItem('conadjref')) {
        $('#conadjref').val(conadjref);
    }
    $('#conadjwarehouse').change(function (e) {
        localStorage.setItem('conadjwarehouse', $(this).val());
    });
    if (conadjwarehouse = localStorage.getItem('conadjwarehouse')) {
        $('#conadjwarehouse').select2("val", conadjwarehouse);
    }
    $('#conadjnote').redactor('destroy');
    $('#conadjnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('conadjnote', v);
        }
    });
    if (conadjnote = localStorage.getItem('conadjnote')) {
        $('#conadjnote').redactor('set', conadjnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.conadjdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete conadjitems[item_id];
		row.remove();
		if(conadjitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('conadjitems', JSON.stringify(conadjitems));
			loadItems();
			return;
		}
	});

    var old_machine_qty;
    $(document).on("focus", '.rmachine_qty', function () {
        old_machine_qty = $(this).val();
    }).on("change", '.rmachine_qty', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_machine_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var machine_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        conadjitems[item_id].row.machine_qty = machine_qty;
        localStorage.setItem('conadjitems', JSON.stringify(conadjitems));
        loadItems();
    });

});



function loadItems() {
    if (localStorage.getItem('conadjitems')) {
        count = 1;
        an = 1;
        $("#conadjrorTable tbody").empty();
        conadjitems = JSON.parse(localStorage.getItem('conadjitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(conadjitems, function(o){return [parseInt(o.order)];}) :   conadjitems;
        $('#add_error, #edit_error, #add_error_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
			var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            var row_no = item_id;
			var product_id = item.row.id;
			var system_qty = item.row.system_qty;
			var machine_qty = item.row.machine_qty;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_code[]" type="hidden" value="' + item.row.code + '"><input name="product_name[]" type="hidden" value="' + item.row.name + '">'+item.row.code+' - '+item.row.name+'</td>';
			tr_html += '<td><input type="hidden" name="system_qty[]" value="'+formatDecimal(system_qty)+'"/>'+formatQuantity(system_qty)+'</td>';
			tr_html += '<td><input class="form-control text-center rmachine_qty" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="machine_qty[]" type="text" value="' + machine_qty + '" data-id="' + row_no + '" data-item="' + item_id + '" id="machine_qty_' + row_no + '" onClick="this.select();"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer conadjdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conadjrorTable");
			$('select').select2();
            count += parseFloat(machine_qty);
            an++;
        });
        var col = 2;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#conadjrorTable tfoot').html(tfoot);
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
        conadjitems = {};
        if ($('#conadjwarehouse').val()) {
            $('#conadjwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (conadjitems[item_id]) {
        var new_qty = parseFloat(conadjitems[item_id].row.machine_qty) + 1;
        conadjitems[item_id].row.machine_qty = new_qty;
    } else {
        conadjitems[item_id] = item;
    }
    conadjitems[item_id].order = new Date().getTime();
    localStorage.setItem('conadjitems', JSON.stringify(conadjitems));
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
