$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('wtlitems')) {
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
	
	$(document).on('change', '#wtldate', function (e) {
		localStorage.setItem('wtldate', $(this).val());
	});
	if (wtldate = localStorage.getItem('wtldate')) {
		$('#wtldate').val(wtldate);
	}
	$(document).on('change', '#wtlbiller', function (e) {
		localStorage.setItem('wtlbiller', $(this).val());
	});
	if (wtlbiller = localStorage.getItem('wtlbiller')) {
		$('#wtlbiller').val(wtlbiller);
	}
	$(document).on('change', '#wtlname', function (e) {
		localStorage.setItem('wtlname', $(this).val());
	});
	if (wtlname = localStorage.getItem('wtlname')) {
		$('#wtlname').val(wtlname);
	}
	$(document).on('change', '#wtlgender', function (e) {
		localStorage.setItem('wtlgender', $(this).val());
	});
	if (wtlgender = localStorage.getItem('wtlgender')) {
		$('#wtlgender').val(wtlgender);
	}
	$(document).on('change', '#wtlethnicity', function (e) {
		localStorage.setItem('wtlethnicity', $(this).val());
	});
	if (wtlethnicity = localStorage.getItem('wtlethnicity')) {
		$('#wtlethnicity').val(wtlethnicity);
	}
	$(document).on('change', '#wtlnationality', function (e) {
		localStorage.setItem('wtlnationality', $(this).val());
	});
	if (wtlnationality = localStorage.getItem('wtlnationality')) {
		$('#wtlnationality').val(wtlnationality);
	}
	$(document).on('change', '#wtlphone', function (e) {
		localStorage.setItem('wtlphone', $(this).val());
	});
	if (wtlphone = localStorage.getItem('wtlphone')) {
		$('#wtlphone').val(wtlphone);
	}
	$(document).on('change', '#wtlrelationship', function (e) {
		localStorage.setItem('wtlrelationship', $(this).val());
	});
	if (wtlrelationship = localStorage.getItem('wtlrelationship')) {
		$('#wtlrelationship').val(wtlrelationship);
	}
	
	$('#wtlnote').redactor('destroy');
	$('#wtlnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('wtlnote', v);
		}
	});
	if (wtlnote = localStorage.getItem('wtlnote')) {
		$('#wtlnote').redactor('set', wtlnote);
	}
	
	$(document).on('click', '.wtldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete wtlitems[item_id];
        row.remove();
        if(wtlitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
            loadItems();
            return;
        }
    });
	
	$(document).on('click', '.add_student', function (e) {
		if (count == 1) {
			wtlitems = {};
		}
		var item_id = new Date().getTime();
		var item = {id:item_id,s_student:"",s_student_latin:"",s_gender:"",s_acedemic_year:"",s_old_school:"",s_grade:"",s_o_grade:"",s_o_acedemic_year:""};
		wtlitems[item_id] = item;
		wtlitems[item_id].order = item_id;
		localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
		loadItems();
		return true;
	});
	
	$(document).on("change", '.s_student', function () {
        var row = $(this).closest('tr');
        var s_student = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_student = s_student;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	$(document).on("change", '.s_student_latin', function () {
        var row = $(this).closest('tr');
        var s_student_latin = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_student_latin = s_student_latin;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    }); 
	
	$(document).on("change", '.s_gender', function () {
        var row = $(this).closest('tr');
        var s_gender = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_gender = s_gender;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	$(document).on("change", '.s_acedemic_year', function () {
        var row = $(this).closest('tr');
        var s_acedemic_year = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_acedemic_year = s_acedemic_year;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	$(document).on("change", '.s_old_school', function () {
        var row = $(this).closest('tr');
        var s_old_school = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_old_school = s_old_school;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	$(document).on("change", '.s_grade', function () {
        var row = $(this).closest('tr');
        var s_grade = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_grade = s_grade;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	$(document).on("change", '.s_o_grade', function () {
        var row = $(this).closest('tr');
        var s_o_grade = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_o_grade = s_o_grade;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });
	
	$(document).on("change", '.s_o_acedemic_year', function () {
        var row = $(this).closest('tr');
        var s_o_acedemic_year = $(this).val(),
        item_id = row.attr('data-item-id');
        wtlitems[item_id].s_o_acedemic_year = s_o_acedemic_year;
        localStorage.setItem('wtlitems', JSON.stringify(wtlitems));
        loadItems();
    });  
	
	
	$('select').select2();
});

function clearLS(){
	if (localStorage.getItem('wtldate')) {
		localStorage.removeItem('wtldate');
	}
	if (localStorage.getItem('wtlbiller')) {
		localStorage.removeItem('wtlbiller');
	}
	if (localStorage.getItem('wtlnote')) {
		localStorage.removeItem('wtlnote');
	}
	if (localStorage.getItem('wtlitems')) {
		localStorage.removeItem('wtlitems');
	}
	if (localStorage.getItem('wtlname')) {
		localStorage.removeItem('wtlname');
	}
	if (localStorage.getItem('wtlgender')) {
		localStorage.removeItem('wtlgender');
	}
	if (localStorage.getItem('wtlethnicity')) {
		localStorage.removeItem('wtlethnicity');
	}
	if (localStorage.getItem('wtlnationality')) {
		localStorage.removeItem('wtlnationality');
	}
	if (localStorage.getItem('wtlphone')) {
		localStorage.removeItem('wtlphone');
	}
	if (localStorage.getItem('wtlrelationship')) {
		localStorage.removeItem('wtlrelationship');
	}
}


function loadItems() {
    if (localStorage.getItem('wtlitems')) {
        count = 1;
        an = 1;
        $("#wtlTable tbody").empty();
        wtlitems = JSON.parse(localStorage.getItem('wtlitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(wtlitems, function(o){return [parseInt(o.order)];}) :   wtlitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            var row_no = item_id;
			var grade_opt = $("<select name=\"s_grade[]\" class=\"form-control s_grade select\" />");
			var o_grade_opt = $("<select name=\"s_o_grade[]\" class=\"form-control s_o_grade select\" />");
			if(grades != false){
				if(item.s_o_grade == 0) {
					$("<option />", {value: 0, text: 'N/A', selected:true}).appendTo(o_grade_opt);
				} else {
					$("<option />", {value: 0, text: 'N/A'}).appendTo(o_grade_opt);
				}
				$.each(grades, function () {
					if(this.id == item.s_grade) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(grade_opt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(grade_opt);
					}
					if(this.id == item.s_o_grade) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(o_grade_opt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(o_grade_opt);
					}
				});
			}
			var o_schools = "<input list='o_schools' name='s_old_school[]' class='s_old_school form-control' value='"+item.s_old_school+"'>";
			if(other_schools){
				o_schools += "<datalist id='o_schools'>";
				$.each(other_schools, function () {
					o_schools += "<option value='"+this.name+"'>";
				});
				o_schools += "</datalist>";
			}
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="s_student[]" class="s_student form-control khmer_text" type="text" value="'+item.s_student+'"/></td>';
			tr_html += '<td><input name="s_student_latin[]" class="s_student_latin form-control uppercase_text english_text" type="text" value="'+item.s_student_latin+'"/></td>';
			tr_html += '<td><select name="s_gender[]" class="s_gender form-control"><option '+(item.s_gender == "male" ? 'selected' : '')+' value="male">Male</option><option '+(item.s_gender == "female" ? 'selected' : '')+' value="female">Female</option></select></td>';
			tr_html += '<td>'+(o_grade_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td><input name="s_o_acedemic_year[]" class="s_o_acedemic_year form-control year" type="text" value="'+item.s_o_acedemic_year+'"/></td>';
			tr_html += '<td>'+o_schools+'</td></td>';
			tr_html += '<td>'+(grade_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td><input name="s_acedemic_year[]" class="s_acedemic_year form-control year" type="text" value="'+item.s_acedemic_year+'"/></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer wtldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#wtlTable");
			$('select').select2();
			count++;
            an++;
        });
        $('#titems').text((an - 1));
        $('#total_items').val((parseFloat(count) - 1));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

