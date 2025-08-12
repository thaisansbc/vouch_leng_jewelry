$(document).ready(function () {
    if (!localStorage.getItem('tlref')) {
        localStorage.setItem('tlref', '');
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
    if (localStorage.getItem('tlitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('tlitems')) {
                    localStorage.removeItem('tlitems');
                }
                if (localStorage.getItem('tlref')) {
                    localStorage.removeItem('tlref');
                }
                if (localStorage.getItem('tlnote')) {
                    localStorage.removeItem('tlnote');
                }
                if (localStorage.getItem('tldate')) {
                    localStorage.removeItem('tldate');
                }
				
				if (localStorage.getItem('tlleavetypes')) {
                    localStorage.removeItem('tlleavetypes');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#tlref').change(function (e) {
        localStorage.setItem('tlref', $(this).val());
    });
    if (tlref = localStorage.getItem('tlref')) {
        $('#tlref').val(tlref);
    }


    //$(document).on('change', '#tlnote', function (e) {
        $('#tlnote').redactor('destroy');
        $('#tlnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('tlnote', v);
            }
        });
        if (tlnote = localStorage.getItem('tlnote')) {
            $('#tlnote').redactor('set', tlnote);
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

    $(document).on('click', '.tldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete tlitems[item_id];
        row.remove();
        if(tlitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('tlitems', JSON.stringify(tlitems));
            loadItems();
            return;
        }
    });
	
	$(document).on("change", '.start_date', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
		var start_date = $(this).val();
		end_date = tlitems[item_id].row.end_date;
		if(new Date(end_date) < new Date(start_date)){
			loadItems();
            bootbox.alert(lang.unexpected_value);
            return;
		}else{
			tlitems[item_id].row.start_date = start_date;
			localStorage.setItem('tlitems', JSON.stringify(tlitems));
			loadItems();
		}
    });
	
	$(document).on("change", '.end_date', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
		var end_date = $(this).val();
		start_date = tlitems[item_id].row.start_date;
		if(new Date(end_date) < new Date(start_date)){
			loadItems();
            bootbox.alert(lang.unexpected_value);
            return;
		}else{
			tlitems[item_id].row.end_date = end_date;
			localStorage.setItem('tlitems', JSON.stringify(tlitems));
			loadItems();
		}
    });
	
	$(document).on("change", '.reason', function () {
        var row = $(this).closest('tr');
        var reason = $(this).val(),
        item_id = row.attr('data-item-id');
        tlitems[item_id].row.reason = reason;
        localStorage.setItem('tlitems', JSON.stringify(tlitems));
        loadItems();
    });
	
	
	$(document).on("change", '.timeshift', function () {
        var row = $(this).closest('tr');
        var timeshift = $(this).val(),
        item_id = row.attr('data-item-id');
        tlitems[item_id].row.timeshift = timeshift;
        localStorage.setItem('tlitems', JSON.stringify(tlitems));
        loadItems();
    });
	
	$(document).on("change", '.leave_type', function () {
        var row = $(this).closest('tr');
        var leave_type = $(this).val(),
        item_id = row.attr('data-item-id');
        tlitems[item_id].row.leave_type = leave_type;
        localStorage.setItem('tlitems', JSON.stringify(tlitems));
        loadItems();
    });
	



});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
	

    if (localStorage.getItem('tlitems')) {
		if (localStorage.getItem('tlleavetypes')) {
			var leave_type = localStorage.getItem('tlleavetypes');
		}else{
			var leave_type = false;
		}
		
		count = 1;
        an = 1;
        $("#tlTable tbody").empty();
        tlitems = JSON.parse(localStorage.getItem('tlitems'));
        $.each(tlitems, function () {
			
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var employee_id = item.row.id, employee_code = item.row.empcode, item_name = item.row.firstname +' '+item.row.lastname;
            var start_date = item.row.start_date, end_date = item.row.end_date;
			var reason = item.row.reason;
			var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			var timeshift = item.row.timeshift;
			var select_leave_type = item.row.leave_type; 
			
			var timeshift_select = '<select name="timeshift[]"  class="form-control select timeshift" style="width:100%;">';
				timeshift_select += '<option '+(timeshift=='full'?'selected':'')+' value="full">'+lang.full+'</option>';
				timeshift_select += '<option '+(timeshift=='morning'?'selected':'')+' value="morning">'+lang.morning+'</option>';
				timeshift_select += '<option '+(timeshift=='afternoon'?'selected':'')+' value="afternoon">'+lang.afternoon+'</option>';
				timeshift_select += '</select>';
			
			
            tr_html = '<td><input name="employee_id[]" type="hidden" class="rid" value="' + employee_id + '"><span class="sname" id="name_' + row_no + '">' + employee_code +' - ' + item_name +'</span></td>';
			if(leave_type){
				tr_html += '<td>'+leave_type+'</td>';
			}
			tr_html += '<td><input class="form-control date start_date text-center" name="start_date[]" type="text" value="' + start_date + '" data-id="' + row_no + '" data-item="' + item_id + '" id="from_date_' + row_no + '"></td>';
			tr_html += '<td><input class="form-control date end_date text-center" name="end_date[]" type="text" value="' + end_date + '" data-id="' + row_no + '" data-item="' + item_id + '" id="to_date_' + row_no + '"></td>';
			tr_html += '<td>'+timeshift_select+'</td>';
			tr_html += '<td><input class="form-control reason" name="reason[]" type="text" value="' + reason + '" data-id="' + row_no + '" data-item="' + item_id + '" id="reason_' + row_no + '"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip tldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#tlTable");
            count ++;
            an++;
			if(select_leave_type){
				var parent = $('#row_' + row_no + '').closest('tr');
				parent.find('.leave_type').val(select_leave_type);
			}
			
        });

        var col = 6;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total Employee : ' + formatNumber(parseFloat(count) - 1) + '</th>';
        
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#tlTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_take_leave_employee(item) {
    if (count == 1) {
        tlitems = {};
    }
    if (item == null)
        return;

    var item_id = item.id;
    tlitems[item_id] = item;
    tlitems[item_id].order = new Date().getTime();
    localStorage.setItem('tlitems', JSON.stringify(tlitems));
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