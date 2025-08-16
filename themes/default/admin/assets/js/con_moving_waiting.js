$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('conmwitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('conmwitems')) {
					localStorage.removeItem('conmwitems');
				}
				if (localStorage.getItem('conmwref')) {
					localStorage.removeItem('conmwref');
				}
				if (localStorage.getItem('conmwnote')) {
					localStorage.removeItem('conmwnote');
				}
				if (localStorage.getItem('conmwdate')) {
					localStorage.removeItem('conmwdate');
				}
				if (localStorage.getItem('conmwbiller')) {
					localStorage.removeItem('conmwbiller');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#conmwref').change(function (e) {
        localStorage.setItem('conmwref', $(this).val());
    });
    if (conmwref = localStorage.getItem('conmwref')) {
        $('#conmwref').val(conmwref);
    }
    $('#conmwnote').redactor('destroy');
    $('#conmwnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('conmwnote', v);
        }
    });
    if (conmwnote = localStorage.getItem('conmwnote')) {
        $('#conmwnote').redactor('set', conmwnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.conmwdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete conmwitems[item_id];
		row.remove();
		if(conmwitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('conmwitems', JSON.stringify(conmwitems));
			loadItems();
			return;
		}
	});

    var old_times_hours;
    $(document).on("focus", '.rtimes_hours', function () {
        old_times_hours = $(this).val();
    }).on("change", '.rtimes_hours', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_times_hours);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var times_hours = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        conmwitems[item_id].row.times_hours = times_hours;
        localStorage.setItem('conmwitems', JSON.stringify(conmwitems));
        loadItems();
    });
	$(document).on("change", '.driver_id', function () {
        var row = $(this).closest('tr');
        var driver_id = $(this).val(),
        item_id = row.attr('data-item-id');
        conmwitems[item_id].row.driver_id = driver_id;
        localStorage.setItem('conmwitems', JSON.stringify(conmwitems));
        loadItems();
    });
});



function loadItems() {
    if (localStorage.getItem('conmwitems')) {
        count = 1;
        an = 1;
        $("#conmwTable tbody").empty();
        conmwitems = JSON.parse(localStorage.getItem('conmwitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(conmwitems, function(o){return [parseInt(o.order)];}) :   conmwitems;
        $('#add_moving_waiting, #edit_moving_waiting, #add_moving_waiting_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.item_id;
            var row_no = item_id;
			var truck_id = item.item_id;
			var times_hours = item.row.times_hours;
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
			tr_html += '<td><input class="form-control text-center rtimes_hours" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="times_hours[]" type="text" value="' + times_hours + '" data-id="' + row_no + '" data-item="' + item_id + '" id="times_hours_' + row_no + '" onClick="this.select();"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer conmwdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conmwTable");
			$('select').select2();
            count += parseFloat(times_hours);
            an++;
        });
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#conmwTable tfoot').html(tfoot);
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
        conmwitems = {};
    }
    if (item == null)
        return;

    var item_id = item.item_id;
    if (!conmwitems[item_id]) {
        conmwitems[item_id] = item;
		conmwitems[item_id].order = new Date().getTime();
		localStorage.setItem('conmwitems', JSON.stringify(conmwitems));
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
