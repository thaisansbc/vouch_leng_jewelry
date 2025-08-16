$(document).ready(function () {
    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    if (localStorage.getItem('dflsitems')) {
        loadItems();
    }

    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('dflsitems')) {
                    localStorage.removeItem('dflsitems');
                }
                if (localStorage.getItem('dflsnote')) {
                    localStorage.removeItem('dflsnote');
                }
                if (localStorage.getItem('dflsdate')) {
                    localStorage.removeItem('dflsdate');
                }
				if (localStorage.getItem('dflsbiller')) {
					localStorage.removeItem('dflsbiller');
				}
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    $('#dflsbiller').change(function (e) {
        localStorage.setItem('dflsbiller', $(this).val());
    });
    if (dflsbiller = localStorage.getItem('dflsbiller')) {
        $('#dflsbiller').select2("val",dflsbiller);
    }
	
	$('#dflsnote').redactor('destroy');
	$('#dflsnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('dflsnote', v);
		}
	});
	if (dflsnote = localStorage.getItem('dflsnote')) {
		$('#dflsnote').redactor('set', dflsnote);
	}

 
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $(document).on('click', '.tldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete dflsitems[item_id];
        row.remove();
        if(dflsitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('dflsitems', JSON.stringify(dflsitems));
            loadItems();
            return;
        }
    });
	
	$(document).on("change", '.day_off', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
		var day_off = $(this).val();
		dflsitems[item_id].row.day_off = day_off;
		localStorage.setItem('dflsitems', JSON.stringify(dflsitems));
		loadItems();
		
    });

	$(document).on("change", '.description', function () {
        var row = $(this).closest('tr');
        var description = $(this).val(),
        item_id = row.attr('data-item-id');
        dflsitems[item_id].row.description = description;
        localStorage.setItem('dflsitems', JSON.stringify(dflsitems));
        loadItems();
    });

});



function loadItems() {
    if (localStorage.getItem('dflsitems')) {
		count = 1;
        an = 1;
        $("#tlTable tbody").empty();
        dflsitems = JSON.parse(localStorage.getItem('dflsitems'));
        $.each(dflsitems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var employee_id = item.row.id, employee_code = item.row.empcode, item_name = item.row.firstname +' '+item.row.lastname;
            var day_off = item.row.day_off;
			var description = item.row.description;
			var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="employee_id[]" type="hidden" class="rid" value="' + employee_id + '"><span class="sname" id="name_' + row_no + '">' + employee_code +' - ' + item_name +'</span></td>';
			tr_html += '<td><input class="form-control date day_off text-center" name="day_off[]" type="text" value="' + day_off + '" data-id="' + row_no + '" data-item="' + item_id + '" id="to_date_' + row_no + '"></td>';
			tr_html += '<td><input class="form-control description" name="description[]" type="text" value="' + description + '" data-id="' + row_no + '" data-item="' + item_id + '" id="reason_' + row_no + '"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip tldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#tlTable");
            count ++;
            an++;
        });
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total Employee : ' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#tlTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		
		if (count > 1) {
            $('#dflsbiller').select2("readonly", true);
        }else{
			$('#dflsbiller').select2("readonly", false);
		}
        set_page_focus();
    }
}


function add_day_off_employee(item) {
    if (count == 1) {
        dflsitems = {};
    }
    if (item == null)
        return;

    var item_id = item.id;
    dflsitems[item_id] = item;
    dflsitems[item_id].order = new Date().getTime();
    localStorage.setItem('dflsitems', JSON.stringify(dflsitems));
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