$(document).ready(function () {
    $('body a, body button').attr('tabindex', -1);
    if (localStorage.getItem('kpitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('kpitems')) {
                    localStorage.removeItem('kpitems');
                }
                if (localStorage.getItem('kpmnote')) {
                    localStorage.removeItem('kpmnote');
                }
				if (localStorage.getItem('kpenote')) {
                    localStorage.removeItem('kpenote');
                }
                if (localStorage.getItem('kpdate')) {
                    localStorage.removeItem('kpdate');
                }
				if (localStorage.getItem('kpemployee')) {
					localStorage.removeItem('kpemployee');
				}
				if (localStorage.getItem('kpkpi_type')) {
					localStorage.removeItem('kpkpi_type');
				}
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

	$('#kpmnote').redactor('destroy');
	$('#kpmnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('kpmnote', v);
		}
	});
	if (kpmnote = localStorage.getItem('kpmnote')) {
		$('#kpmnote').redactor('set', kpmnote);
	}
	$('#kpenote').redactor('destroy');
	$('#kpenote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('kpenote', v);
		}
	});
	if (kpenote = localStorage.getItem('kpenote')) {
		$('#kpenote').redactor('set', kpenote);
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

    $(document).on('click', '.kpdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete kpitems[item_id];
        row.remove();
        if(kpitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('kpitems', JSON.stringify(kpitems));
            loadItems();
            return;
        }
    });
	
	$(document).on('change', '.comment', function () {
		var row = $(this).closest('tr');
		item_id = row.attr('data-item-id');
		var comment = $(this).val();
		kpitems[item_id].row.comment = comment;
        localStorage.setItem('kpitems', JSON.stringify(kpitems));
        loadItems();
	});
	
	$(document).on('change', '.rate', function () {
		var row = $(this).closest('tr');
		item_id = row.attr('data-item-id');
		var rate = $(this).val();
		kpitems[item_id].row.rate = rate;
        localStorage.setItem('kpitems', JSON.stringify(kpitems));
        loadItems();
	});


});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
    if (localStorage.getItem('kpitems')) {
		count = 1;
		var t_rate = 0;
		var t_result = 0;
		var t_value = 0;
        $("#kpTable tbody").empty();
        kpitems = JSON.parse(localStorage.getItem('kpitems'));
        $.each(kpitems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var row_no = (new Date).getTime();			
			var rate_opt = $("<select name=\"rate\[\]\" class=\"form-control select text-center rate\" />");
			
			var q_value = (item.row.max_rate * item.row.value_percentage) / 100;
			var q_result = (((item.row.rate * 100) / item.row.max_rate) * q_value) / 100;
			t_rate += (item.row.rate - 0);
			t_value  += q_value;
			t_result += q_result;
			
			if(item.row.min_rate > 0 && item.row.max_rate > 0 && item.row.max_rate >= item.row.min_rate){
				for(var i = item.row.min_rate; i <= item.row.max_rate;i++ ){
					if (item.row.rate == i){
                        $("<option />", {value: i, text: i, selected: 'selected', class: 'text-center'}).appendTo(rate_opt);
                    }else{
                        $("<option />", {value: i, text: i, class: 'text-center'}).appendTo(rate_opt);
					}
				}
			}else{
				$("<option />", {value: 0, text: 'n/a'}).appendTo(rate_opt);
                rate_opt = rate_opt.hide();
			}
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="question_id[]" type="hidden" class="rid" value="' + item.row.id + '"><span>' + item.row.question  +'</span></td>';
			tr_html += '<td><input type="hidden" class="value_percentage" value="'+item.row.value_percentage+'"/><span>'+item.row.question_kh+'</span></td>';
			tr_html += '<td><input value="' +item.row.comment+'" type="text" class="comment form-control" name="comment[]"/></td>';
            tr_html += '<td>'+(rate_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip kpdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#kpTable");
            count ++;
        });
		var g_result = (t_result * 100) / t_value;
		$('#result').val(g_result);
		$('#t_question').text((count - 1));
		$('#t_rate').text(formatDecimal(t_rate));
		$('#t_result').text(formatDecimal(g_result)+'%');
        $('select.select').select2({minimumResultsForSearch: 7});
        set_page_focus();
    }
}


if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}