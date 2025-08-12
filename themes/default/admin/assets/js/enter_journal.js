$(document).ready(function () {
    if (!localStorage.getItem('jnref')) {
        localStorage.setItem('jnref', '');
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
    if (localStorage.getItem('jnitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('jnitems')) {
                    localStorage.removeItem('jnitems');
                }
                if (localStorage.getItem('jnref')) {
                    localStorage.removeItem('jnref');
                }
                if (localStorage.getItem('jnnote')) {
                    localStorage.removeItem('jnnote');
                }
                if (localStorage.getItem('jndate')) {
                    localStorage.removeItem('jndate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#jnref').change(function (e) {
        localStorage.setItem('jnref', $(this).val());
    });
    if (jnref = localStorage.getItem('jnref')) {
        $('#jnref').val(jnref);
    }


    //$(document).on('change', '#jnnote', function (e) {
        $('#jnnote').redactor('destroy');
        $('#jnnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('jnnote', v);
            }
        });
        if (jnnote = localStorage.getItem('jnnote')) {
            $('#jnnote').redactor('set', jnnote);
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

    $(document).on('click', '.jndel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete jnitems[item_id];
        row.remove();
        if(jnitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('jnitems', JSON.stringify(jnitems));
            loadItems();
            return;
        }
    });
	
	$(document).on("focus", '.credit', function () {
		old_credit = $(this).val();
    }).on("change", '.credit', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_credit);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_credit = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        jnitems[item_id].row.credit = new_credit;
		jnitems[item_id].row.debit = 0;
        localStorage.setItem('jnitems', JSON.stringify(jnitems));
        loadItems();
    });
	
	$(document).on("focus", '.debit', function () {
		old_debit = $(this).val();
    }).on("change", '.debit', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_debit);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_debit = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        jnitems[item_id].row.debit = new_debit;
		jnitems[item_id].row.credit = 0;
        localStorage.setItem('jnitems', JSON.stringify(jnitems));
        loadItems();
    });
	
	$(document).on('change', '.description', function () {
        var row = $(this).closest('tr');
        var description = $(this).val(),
        item_id = row.attr('data-item-id');
        jnitems[item_id].row.description = description;
        localStorage.setItem('jnitems', JSON.stringify(jnitems));
        loadItems();
    });
	
});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
	

    if (localStorage.getItem('jnitems')) {
		count = 1;
        an = 1;
        $("#jnTable tbody").empty();
        jnitems = JSON.parse(localStorage.getItem('jnitems'));
		var total_credit = 0;
		var total_debit = 0;
        $.each(jnitems, function () {
			
            var item = this;
            var item_id = item.id;

            item.order = item.order ? item.order : new Date().getTime();
            var account_id = item.row.id, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var row_no = (new Date).getTime();
			var debit = item.row.debit;
			var credit = item.row.credit;
			if(debit == null){
				debit = 0;
			}
			if(credit == null){
				credit = 0;
			}
			if(item.row.description){
				var  description = item.row.description;
			}else{
				var description = '';
			}
			
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="account_id[]" type="hidden" class="rid" value="' + account_id + '"><input name="account_code[]" type="hidden" class="rid" value="' + item_code + '"><input name="account_name[]" type="hidden" class="rid" value="' + item_name + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
			tr_html += '<td><input class="form-control text-center debit"  name="debit[]" type="text" value="' + debit + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
            tr_html += '<td><input class="form-control text-center credit"  name="credit[]" type="text" value="' + credit + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
			tr_html += '<td><input class="form-control text-left description"  name="description[]" type="text" value="' + description + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip jndel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#jnTable");
            count ++;
            an++;
			total_credit += credit;
			total_debit += debit;
        });
		total_credit = formatDecimal(total_credit);
		total_debit = formatDecimal(total_debit);
		if((total_credit==total_debit) && total_debit > 0){
			$('#submit_journal').prop('disabled', false);
		}else{
			$('#submit_journal').prop('disabled', true);
		}
        var tfoot = '<tr id="tfoot" class="tfoot active"><th>Total</th><th class="text-center">' + total_debit + '</th><th class="text-center">' + total_credit + '</th>';
        tfoot += '<th></th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#jnTable tfoot').html(tfoot);
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
function add_journal_item(item) {
    if (count == 1) {
        jnitems = {};
    }
    if (item == null)
        return;
    var item_id = item.id;
    jnitems[item_id] = item;
    jnitems[item_id].order = new Date().getTime();
    localStorage.setItem('jnitems', JSON.stringify(jnitems));
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