$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('conmsitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				if (localStorage.getItem('conmsitems')) {
					localStorage.removeItem('conmsitems');
				}
				if (localStorage.getItem('conmsref')) {
					localStorage.removeItem('conmsref');
				}
				if (localStorage.getItem('conmsnote')) {
					localStorage.removeItem('conmsnote');
				}
				if (localStorage.getItem('conmsdate')) {
					localStorage.removeItem('conmsdate');
				}
				if (localStorage.getItem('conmsbiller')) {
					localStorage.removeItem('conmsbiller');
				}
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
    $('#conmsref').change(function (e) {
        localStorage.setItem('conmsref', $(this).val());
    });
    if (conmsref = localStorage.getItem('conmsref')) {
        $('#conmsref').val(conmsref);
    }
    $('#conmsnote').redactor('destroy');
    $('#conmsnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('conmsnote', v);
        }
    });
    if (conmsnote = localStorage.getItem('conmsnote')) {
        $('#conmsnote').redactor('set', conmsnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
	$(document).on('click', '.conmsdel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		delete conmsitems[item_id];
		row.remove();
		if(conmsitems.hasOwnProperty(item_id)) { } else {
			localStorage.setItem('conmsitems', JSON.stringify(conmsitems));
			loadItems();
			return;
		}
	});

	$(document).on("change", '.driver_id', function () {
        var row = $(this).closest('tr');
        var driver_id = $(this).val(),
        item_id = row.attr('data-item-id');
        conmsitems[item_id].row.driver_id = driver_id;
        localStorage.setItem('conmsitems', JSON.stringify(conmsitems));
        loadItems();
    });
	$(document).on("change", '.rdate', function () {
        var row = $(this).closest('tr');
        var rdate = $(this).val(),
        item_id = row.attr('data-item-id');
        conmsitems[item_id].row.date = rdate;
        localStorage.setItem('conmsitems', JSON.stringify(conmsitems));
        loadItems();
    });
	
	$(document).on("change", '.rmission_type', function () {
        var row = $(this).closest('tr');
        var rmission_type = $(this).val(),
        item_id = row.attr('data-item-id');
        conmsitems[item_id].row.mission_type_id = rmission_type;
        localStorage.setItem('conmsitems', JSON.stringify(conmsitems));
        loadItems();
    });
});



function loadItems() {
    if (localStorage.getItem('conmsitems')) {
        count = 1;
        an = 1;
        $("#conmsTable tbody").empty();
        conmsitems = JSON.parse(localStorage.getItem('conmsitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(conmsitems, function(o){return [parseInt(o.order)];}) :   conmsitems;
		$.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
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
			
			
			var mission_type_opt = $("<select name=\"mission_type[]\" class=\"form-control rmission_type select\" />");
			$.each(item.mission_types, function () {
				if(this.id == item.row.mission_type_id) {
					$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(mission_type_opt);
				} else {
					$("<option />", {value: this.id, text: this.name}).appendTo(mission_type_opt);
				}
			});
			
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			tr_html = '<td><input name="mission_date[]" type="text" class="rdate date form-control" value="' + item.row.date + '"></td>';
            tr_html += '<td><input name="truck_id[]" type="hidden" class="rid" value="' + truck_id + '">'+item.row.code+'</td>';
			tr_html += '<td class="text-left">'+item.row.plate+'</td>';
			tr_html += '<td>'+(driver_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td>'+(mission_type_opt.get(0).outerHTML)+'</td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer conmsdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#conmsTable");
			$('select').select2();
            count += parseFloat(times_hours);
            an++;
        });

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
        conmsitems = {};
    }
    if (item == null)
        return;

    var item_id = item.id;
    if (!conmsitems[item_id]) {
        conmsitems[item_id] = item;
		conmsitems[item_id].order = new Date().getTime();
		localStorage.setItem('conmsitems', JSON.stringify(conmsitems));
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
