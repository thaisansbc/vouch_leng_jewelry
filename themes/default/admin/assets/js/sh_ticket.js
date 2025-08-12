$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('tklitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
		bootbox.confirm(lang.r_u_sure, function (result) {
			if (result) {
				clearLS()
				$('#modal-loading').show();
				location.reload();
			}
		});
    });
	
	$(document).on('change', '#tkldate', function (e) {
		localStorage.setItem('tkldate', $(this).val());
	});
	if (tkldate = localStorage.getItem('tkldate')) {
		$('#tkldate').val(tkldate);
	}
	$(document).on('change', '#tklbiller', function (e) {
		localStorage.setItem('tklbiller', $(this).val());
	});
	if (tklbiller = localStorage.getItem('tklbiller')) {
		$('#tklbiller').val(tklbiller);
	}
	$(document).on('change', '#tklref', function (e) {
		localStorage.setItem('tklref', $(this).val());
	});
	if (tklref = localStorage.getItem('tklref')) {
		$('#tklref').val(tklref);
	}
	$(document).on('change', '#tklacademic_year', function (e) {
		localStorage.setItem('tklacademic_year', $(this).val());
	});
	if (tklacademic_year = localStorage.getItem('tklacademic_year')) {
		$('#tklacademic_year').val(tklacademic_year);
	}
	$(document).on('change', '#tklphone', function (e) {
		localStorage.setItem('tklphone', $(this).val());
	});
	if (tklphone = localStorage.getItem('tklphone')) {
		$('#tklphone').val(tklphone);
	}
	$(document).on('change', '#tkltype', function (e) {
		localStorage.setItem('tkltype', $(this).val());
	});
	if (tkltype = localStorage.getItem('tkltype')) {
		$('#tkltype').val(tkltype);
	}
	$(document).on('change', '#tklold_ticket', function (e) {
		localStorage.setItem('tklold_ticket', $(this).val());
	});
	if (tklold_ticket = localStorage.getItem('tklold_ticket')) {
		$('#tklold_ticket').val(tklold_ticket);
	}
	$(document).on('change', '#tklresponse_date', function (e) {
		localStorage.setItem('tklresponse_date', $(this).val());
	});
	if (tklresponse_date = localStorage.getItem('tklresponse_date')) {
		$('#tklresponse_date').val(tklresponse_date);
	}
	

	$('#tklnote').redactor('destroy');
	$('#tklnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('tklnote', v);
		}
	});
	if (tklnote = localStorage.getItem('tklnote')) {
		$('#tklnote').redactor('set', tklnote);
	}
	$('#tklbehavior').redactor('destroy');
	$('#tklbehavior').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('tklbehavior', v);
		}
	});
	if (tklbehavior = localStorage.getItem('tklbehavior')) {
		$('#tklbehavior').redactor('set', tklbehavior);
	}
	
	$(document).on('click', '.tkldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete tklitems[item_id];
        row.remove();
        if(tklitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('tklitems', JSON.stringify(tklitems));
            loadItems();
            return;
        }
    });
	
	$('select').select2();
});

function clearLS(){
	if (localStorage.getItem('tkldate')) {
		localStorage.removeItem('tkldate');
	}
	if (localStorage.getItem('tklref')) {
		localStorage.removeItem('tklref');
	}
	if (localStorage.getItem('tklbiller')) {
		localStorage.removeItem('tklbiller');
	}
	if (localStorage.getItem('tklnote')) {
		localStorage.removeItem('tklnote');
	}
	if (localStorage.getItem('tklitems')) {
		localStorage.removeItem('tklitems');
	}
	if (localStorage.getItem('tklstudent')) {
		localStorage.removeItem('tklstudent');
	}
	if (localStorage.getItem('tklfamily')) {
		localStorage.removeItem('tklfamily');
	}
	if (localStorage.getItem('tklacademic_year')) {
		localStorage.removeItem('tklacademic_year');
	}
	if (localStorage.getItem('tklbehavior')) {
		localStorage.removeItem('tklbehavior');
	}
	if (localStorage.getItem('tklphone')) {
		localStorage.removeItem('tklphone');
	}
	if (localStorage.getItem('tkltype')) {
		localStorage.removeItem('tkltype');
	}
	if (localStorage.getItem('tklold_ticket')) {
		localStorage.removeItem('tklold_ticket');
	}
	if (localStorage.getItem('tklresponse_date')) {
		localStorage.removeItem('tklresponse_date');
	}
}


$(document).on("change", '.comment', function () {
	var row = $(this).closest('tr');
	var comment = $(this).val();
	item_id = row.attr('data-item-id');
	tklitems[item_id].row.comment = comment;
	localStorage.setItem('tklitems', JSON.stringify(tklitems));
	loadItems();
});


$(document).on("change", '.feedback', function () {
	var row = $(this).closest('tr');
	var feedback = $(this).val();
	item_id = row.attr('data-item-id');
	tklitems[item_id].row.feedback = feedback;
	localStorage.setItem('tklitems', JSON.stringify(tklitems));
	loadItems();
});

function loadItems() {
    if (localStorage.getItem('tklitems')) {
        count = 1;
        an = 1;
        $("#tklTable tbody").empty();
        tklitems = JSON.parse(localStorage.getItem('tklitems'));
        sortedItems =  tklitems;
        $('#add_ticket, #edit_ticket').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            var row_no = item_id;
			var group_id = item.item_id;
			var feedback = item.row.feedback;
			var feedopt = '<select name="feedback['+item_id+'][]"  class="form-control select feedback" multiple>';
			$.each(item.row.feedbacks, function () {
				if(jQuery.inArray(this.id, feedback) !== -1){
					feedopt += '<option value="'+this.id+'" selected>'+this.name+'</option>';
				}else{
					feedopt += '<option value="'+this.id+'">'+this.name+'</option>';
				}
				
			});
			feedopt += '</select>';
			
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="group_id[]" type="hidden" class="rid" value="' + group_id + '">'+item.row.name+'</td>';
			tr_html += '<td>'+feedopt+'</td>';
			tr_html += '<td><textarea name="comment[]" class="form-control comment">'+item.row.comment+'</textarea></td>';
			newTr.html(tr_html);
            newTr.appendTo("#tklTable");
			$('select').select2();
			count++;
            an++;
        });
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}










