$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('conabitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('conabitems')) {
					localStorage.removeItem('conabitems');
				}
				if (localStorage.getItem('conabref')) {
					localStorage.removeItem('conabref');
				}
				if (localStorage.getItem('conabnote')) {
					localStorage.removeItem('conabnote');
				}
				if (localStorage.getItem('conabdate')) {
					localStorage.removeItem('conabdate');
				}
				if (localStorage.getItem('conabbiller')) {
					localStorage.removeItem('conabbiller');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#conabref').change(function (e) {
        localStorage.setItem('conabref', $(this).val());
    });
    if (conabref = localStorage.getItem('conabref')) {
        $('#conabref').val(conabref);
    }
    $('#conabnote').redactor('destroy');
    $('#conabnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('conabnote', v);
        }
    });
    if (conabnote = localStorage.getItem('conabnote')) {
        $('#conabnote').redactor('set', conabnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.conabdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete conabitems[item_id];
		row.remove();
		if(conabitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('conabitems', JSON.stringify(conabitems));
			loadItems();
			return;
		}
	});

	$(document).on("change", '.rdate', function () {
        var row = $(this).closest('tr');
        var rdate = $(this).val(),
        item_id = row.attr('data-item-id');
        conabitems[item_id].row.date = rdate;
        localStorage.setItem('conabitems', JSON.stringify(conabitems));
        loadItems();
    });
});



function loadItems() {
    if (localStorage.getItem('conabitems')) {
        count = 1;
        an = 1;
        $("#conabTable tbody").empty();
        conabitems = JSON.parse(localStorage.getItem('conabitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(conabitems, function(o){return [parseInt(o.order)];}) :   conabitems;
        $('#add_fuel, #edit_fuel, #add_fuel_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            var row_no = item_id;
			var officer_id = item.item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="officer_id[]" type="hidden" class="rid" value="' + officer_id + '">'+item.row.full_name_kh+'</td>';
			tr_html += '<td class="text-left">'+item.row.full_name+'</td>';
			tr_html += '<td>'+item.row.position+'</td>';
			tr_html += '<td><input name="absent_date[]" type="text" class="rdate date form-control" value="' + item.row.date + '"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer conabdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conabTable");
			$('select').select2();
			count++;
            an++;
        });
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#conabTable tfoot').html(tfoot);
        $('#titems').text((an - 1));
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
        conabitems = {};
        if ($('#conabbiller').val()) {
            $('#conabbiller').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = item.id;
    if (!conabitems[item_id]) {
        conabitems[item_id] = item;
		conabitems[item_id].order = new Date().getTime();
		localStorage.setItem('conabitems', JSON.stringify(conabitems));
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
