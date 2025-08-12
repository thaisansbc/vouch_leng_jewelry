$(document).ready(function () {
    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_student').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_student').focus();
    }
    if (localStorage.getItem('exitems')) {
        loadItems();
    }
	
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('exitems')) {
                    localStorage.removeItem('exitems');
                }
                if (localStorage.getItem('exnote')) {
                    localStorage.removeItem('exnote');
                }
                if (localStorage.getItem('exdate')) {
                    localStorage.removeItem('exdate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });




    //$(document).on('change', '#exnote', function (e) {
        $('#exnote').redactor('destroy');
        $('#exnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('exnote', v);
            }
        });
        if (exnote = localStorage.getItem('exnote')) {
            $('#exnote').redactor('set', exnote);
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

    $(document).on('click', '.exdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete exitems[item_id];
        row.remove();
        if(exitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('exitems', JSON.stringify(exitems));
            loadItems();
            return;
        }
    });
	$(document).on("focus", '.score', function () {
        old_new_cost = $(this).val();
    }).on("change", '.score', function () {

        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_new_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_new_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        exitems[item_id].row.score = new_new_cost;
        localStorage.setItem('exitems', JSON.stringify(exitems));
        loadItems();
    });
	
	$(document).on('change', '.subject', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');				
		var subject = $(this).val();
		exitems[item_id].row.subject = subject,
		localStorage.setItem('exitems', JSON.stringify(exitems));
        loadItems();
	});
	



});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
    if (localStorage.getItem('exitems')) {
		count = 1;
        an = 1;
        $("#exTable tbody").empty();
        exitems = JSON.parse(localStorage.getItem('exitems'));
        $.each(exitems, function () {
			
            var item = this;
            var item_id = item.id;

            item.order = item.order ? item.order : new Date().getTime();
            var student_id = item.row.id, student_code = item.row.code, 
            lastname = item.row.lastname, firstname = item.row.firstname;
            var score_by_subject = item.row.score_by_subject;
            var subject_name = item.row.subject_name;
           
            

            var row_no = (new Date).getTime();
			if (typeof(item.row.score) === "undefined") {
				item.row.score = 0;
			}
			var sub_opt = $("<select id=\"subject\" name=\"subject\[\]\" class=\"form-control select subject\" />");
            if(item.subjects != false) {
                $.each(item.subjects, function () {
                    if (item.row.subject == this.id){
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(sub_opt);
                    }else{
                        $("<option />", {value: this.id, text: this.name}).appendTo(sub_opt);
					}
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(sub_opt);
                sub_opt = sub_opt.hide();
            }
			
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="student_id[]" type="hidden" class="rid" value="' + student_id + '"><span class="sname" id="name_' + row_no + '">' + student_code  +'</span></td>';
			tr_html += '<td><span class="sname" id="name_' + row_no + '">' + lastname +' '+ firstname  +'</span></td>';
          
            var json_score = JSON.parse(score_by_subject);
            var i=0;
            var datasub = "";
            json_score.forEach(function(object) {
                i++;
                tr_html += "<td><input type='text' name='score_"+student_id+object.subject_id+"' value='"+object.score+"'></td>";
                
                
                if(subject_name != false) {
                    $.each(subject_name, function () {
                        if (object.subject_id == this.subject_id){
                           
                           datasub += "<td>"+this.name+"</td>";
                       }
                    });
                }

            });
            $("#subject").html(datasub);
            document.getElementById("score_by_subject").setAttribute("colspan", i);

            tr_html += '<td class="hide">'+(sub_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td class="hide"><input class="form-control text-center score"  name="score[]" type="text" value="' + (item.row.score) + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip exdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);

            newTr.prependTo("#exTable");
            count ++;
            an++;
        });
        
        

        var col = 5;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total Student : ' + formatNumber(parseFloat(count) - 1) + '</th>';
        
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#exTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		if (count > 1) {
            $('#class').select2("readonly", true);
			$('#section').select2("readonly", true);
        }else{
			$('#class').select2("readonly", false);
			$('#section').select2("readonly", false);
		}
        set_page_focus();
    }
}


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_examination_student(item) {
    if (count == 1) {
        exitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (exitems[item_id]) {
        var new_qty = parseFloat(exitems[item_id].row.qty) + 1;
        exitems[item_id].row.base_quantity = new_qty;
        if(exitems[item_id].row.unit != exitems[item_id].row.base_unit) {
            $.each(exitems[item_id].units, function(){
                if (this.id == exitems[item_id].row.unit) {
                    exitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        exitems[item_id].row.qty = new_qty;
    } else {
        exitems[item_id] = item;
    }
    exitems[item_id].order = new Date().getTime();
    localStorage.setItem('exitems', JSON.stringify(exitems));
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