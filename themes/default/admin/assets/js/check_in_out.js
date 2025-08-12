$(document).ready(function () {
    if (!localStorage.getItem('ioref')) {
        localStorage.setItem('ioref', '');
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
    if (localStorage.getItem('ioitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('ioitems')) {
                    localStorage.removeItem('ioitems');
                }
                if (localStorage.getItem('ionote')) {
                    localStorage.removeItem('ionote');
                }
			
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#ioref').change(function (e) {
        localStorage.setItem('ioref', $(this).val());
    });

    //$(document).on('change', '#ionote', function (e) {
        $('#ionote').redactor('destroy');
        $('#ionote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('ionote', v);
            }
        });
        if (ionote = localStorage.getItem('ionote')) {
            $('#ionote').redactor('set', ionote);
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
        delete ioitems[item_id];
        row.remove();
        if(ioitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('ioitems', JSON.stringify(ioitems));
            loadItems();
            return;
        }
    });
	
	$(document).on("change", '.check_time', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
		var check_time = $(this).val();
		ioitems[item_id].row.check_time = check_time;
		localStorage.setItem('ioitems', JSON.stringify(ioitems));
		loadItems(); 
    });


});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
    if (localStorage.getItem('ioitems')) {
		count = 1;
        an = 1;
        $("#ioTable tbody").empty();
        ioitems = JSON.parse(localStorage.getItem('ioitems'));
        $.each(ioitems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var employee_id = item.row.id, employee_code = item.row.empcode, item_name = item.row.firstname +' '+item.row.lastname;
            if(item.row.check_time){
				var check_time = item.row.check_time;
			}else{
				var check_time = '';
			}
			var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="employee_id[]" type="hidden" class="rid" value="' + employee_id + '"><span class="sname" id="name_' + row_no + '">' + employee_code +' - ' + item_name +'</span></td>';
			tr_html += '<td><input class="form-control date_time check_time text-center" name="check_time[]" type="text" value="' + check_time + '" data-id="' + row_no + '" data-item="' + item_id + '" id="date_time_' + row_no + '"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip tldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#ioTable");
            count ++;
            an++;
			
        });

        var col = 2;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total Check In/Out : ' + formatNumber(parseFloat(count) - 1) + '</th>';
        
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#ioTable tfoot').html(tfoot);
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
function add_check_in_out_employee(item) {
    if (count == 1) {
        ioitems = {};
    }
    if (item == null)
        return;

    var item_id = item.id;
    ioitems[item_id] = item;
    ioitems[item_id].order = new Date().getTime();
    localStorage.setItem('ioitems', JSON.stringify(ioitems));
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