$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('confuitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('confuitems')) {
					localStorage.removeItem('confuitems');
				}
				if (localStorage.getItem('confuref')) {
					localStorage.removeItem('confuref');
				}
				if (localStorage.getItem('confuwarehouse')) {
					localStorage.removeItem('confuwarehouse');
				}
				if (localStorage.getItem('confunote')) {
					localStorage.removeItem('confunote');
				}
				if (localStorage.getItem('confudate')) {
					localStorage.removeItem('confudate');
				}
				if (localStorage.getItem('confubiller')) {
					localStorage.removeItem('confubiller');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#confuref').change(function (e) {
        localStorage.setItem('confuref', $(this).val());
    });
    if (confuref = localStorage.getItem('confuref')) {
        $('#confuref').val(confuref);
    }
    $('#confuwarehouse').change(function (e) {
        localStorage.setItem('confuwarehouse', $(this).val());
    });
    if (confuwarehouse = localStorage.getItem('confuwarehouse')) {
        $('#confuwarehouse').select2("val", confuwarehouse);
    }
    $('#confunote').redactor('destroy');
    $('#confunote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('confunote', v);
        }
    });
    if (confunote = localStorage.getItem('confunote')) {
        $('#confunote').redactor('set', confunote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.confudel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete confuitems[item_id];
		row.remove();
		if(confuitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('confuitems', JSON.stringify(confuitems));
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
        confuitems[item_id].row.quantity = quantity;
        localStorage.setItem('confuitems', JSON.stringify(confuitems));
        loadItems();
    });
	$(document).on("change", '.driver_id', function () {
        var row = $(this).closest('tr');
        var driver_id = $(this).val(),
        item_id = row.attr('data-item-id');
        confuitems[item_id].row.driver_id = driver_id;
        localStorage.setItem('confuitems', JSON.stringify(confuitems));
        loadItems();
    });
});



function loadItems() {
    if (localStorage.getItem('confuitems')) {
        count = 1;
        an = 1;
        $("#conFuTable tbody").empty();
        confuitems = JSON.parse(localStorage.getItem('confuitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(confuitems, function(o){return [parseInt(o.order)];}) :   confuitems;
        $('#add_fuel, #edit_fuel, #add_fuel_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.item_id;
            var row_no = item_id;
			var truck_id = item.item_id;
			var quantity = item.row.quantity;
			var driver_opt = $("<select name=\"driver_id[]\" class=\"form-control driver_id select\" />");
			$.each(item.drivers, function () {
				if(this.id == item.row.driver_id) {
					$("<option />", {value: this.id, text: this.name+' - '+this.name_kh, selected:true}).appendTo(driver_opt);
				} else {
					$("<option />", {value: this.id, text: this.name+' - '+this.name_kh}).appendTo(driver_opt);
				}
			});
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="truck_id[]" type="hidden" class="rid" value="' + truck_id + '">'+item.row.code+'</td>';
			tr_html += '<td class="text-left">'+item.row.plate+'</td>';
			tr_html += '<td>'+(driver_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + quantity + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer confudel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conFuTable");
			$('select').select2();
            count += parseFloat(quantity);
            an++;
        });
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#conFuTable tfoot').html(tfoot);
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
        confuitems = {};
        if ($('#confuwarehouse').val()) {
            $('#confuwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = item.item_id;
    if (!confuitems[item_id]) {
        confuitems[item_id] = item;
		confuitems[item_id].order = new Date().getTime();
		localStorage.setItem('confuitems', JSON.stringify(confuitems));
		loadItems();
    }
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
