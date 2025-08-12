$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('stlitems')) {
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
	$(document).on('change', '#stldate', function (e) {
		localStorage.setItem('stldate', $(this).val());
	});
	if (stldate = localStorage.getItem('stldate')) {
		$('#stldate').val(stldate);
	}
	$(document).on('change', '#stlbiller', function (e) {
		localStorage.setItem('stlbiller', $(this).val());
	});
	if (stlbiller = localStorage.getItem('stlbiller')) {
		$('#stlbiller').val(stlbiller);
	}
	$(document).on('change', '#stlacademic_year', function (e) {
		localStorage.setItem('stlacademic_year', $(this).val());
	});
	if (stlacademic_year = localStorage.getItem('stlacademic_year')) {
		$('#stlacademic_year').val(stlacademic_year);
	}

	setProgram();
	$(document).on('change', '#stlprogram', function (e) {
		localStorage.setItem('stlprogram', $(this).val());
		setProgram();
	});
	if (stlprogram = localStorage.getItem('stlprogram')) {
		$('#stlprogram').val(stlprogram);
	}
	setGrade();
	$(document).on('change', '#stlgrade', function (e) {
		localStorage.setItem('stlgrade', $(this).val());
		setGrade();
	});
	if (stlgrade = localStorage.getItem('stlgrade')) {
		$('#stlgrade').val(stlgrade);
	}
	$(document).on('change', '#stlclass', function (e) {
		localStorage.setItem('stlclass', $(this).val());
		setClass();
	});
	if (stlclass = localStorage.getItem('stlclass')) {
		$('#stlclass').val(stlclass);
	}
	$(document).on('change', '#stlskill', function (e) {
		localStorage.setItem('stlskill', $(this).val());
	});
	if (stlskill = localStorage.getItem('stlskill')) {
		$('#stlskill').val(stlskill);
	}
	$(document).on('change', '#stlsection', function (e) {
		localStorage.setItem('stlsection', $(this).val());
	});
	if (stlsection = localStorage.getItem('stlsection')) {
		$('#stlsection').val(stlsection);
	}
	$(document).on('change', '#stltimeshift', function (e) {
		localStorage.setItem('stltimeshift', $(this).val());
	});
	if (stltimeshift = localStorage.getItem('stltimeshift')) {
		$('#stltimeshift').val(stltimeshift);
	}
	$('#stlnote').redactor('destroy');
	$('#stlnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('stlnote', v);
		}
	});
	if (stlnote = localStorage.getItem('stlnote')) {
		$('#stlnote').redactor('set', stlnote);
	}
	$(document).on('click', '.stldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete stlitems[item_id];
        row.remove();
        if(stlitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('stlitems', JSON.stringify(stlitems));
            loadItems();
            return;
        }
    });
	$('select').select2();
});

function clearLS(){
	if (localStorage.getItem('stldate')) {
		localStorage.removeItem('stldate');
	}
	if (localStorage.getItem('stlbiller')) {
		localStorage.removeItem('stlbiller');
	}
	if (localStorage.getItem('stlnote')) {
		localStorage.removeItem('stlnote');
	}
	if (localStorage.getItem('stlitems')) {
		localStorage.removeItem('stlitems');
	}
	if (localStorage.getItem('stlacademic_year')) {
		localStorage.removeItem('stlacademic_year');
	}
	if (localStorage.getItem('stlprogram')) {
		localStorage.removeItem('stlprogram');
	}
	if (localStorage.getItem('stlskill')) {
		localStorage.removeItem('stlskill');
	}
	if (localStorage.getItem('stlsection')) {
		localStorage.removeItem('stlsection');
	}
	if (localStorage.getItem('stlgrade')) {
		localStorage.removeItem('stlgrade');
	}
	if (localStorage.getItem('stltimeshift')) {
		localStorage.removeItem('stltimeshift');
	}
	if (localStorage.getItem('stlstatus')) {
		localStorage.removeItem('stlstatus');
	}
	if (localStorage.getItem('stlclass')) {
		localStorage.removeItem('stlclass');
	}
	if (localStorage.getItem('stlbatch')) {
		localStorage.removeItem('stlbatch');
	}
	if (localStorage.getItem('stlgeneration')) {
		localStorage.removeItem('stlgeneration');
	}
}
function loadItems() {
    if (localStorage.getItem('stlitems')) {
        count = 1;
        an = 1;
        $("#stlTable tbody").empty();
        stlitems = JSON.parse(localStorage.getItem('stlitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(stlitems, function(o){return [parseInt(o.order)];}) : stlitems;
        $('#add_student_status, #edit_student_status').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            var row_no = item_id;
			var student_id = item.item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="student_id[]" type="hidden" class="rid" value="' + student_id + '">' + item.row.number + '</td>';
			tr_html += '<td>' + item.row.lastname + ' ' + item.row.firstname + '</td>';
			tr_html += '<td>' + item.row.lastname_other + ' ' + item.row.firstname_other + '</td>';
			tr_html += '<td>' + item.row.gender + '</td>';
			tr_html += '<td>---</td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer stldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
			newTr.html(tr_html);
            newTr.prependTo("#stlTable");
			$('select').select2();
            an++;
			count++;
        });
        $('#titems').text((an - 1));
        $('#total_items').val((parseFloat(count) - 1));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }  
        if (count > 1) {
            // $('#stlprogram').select2('readonly', true);
            // $('#stlskill').select2('readonly', true);
        }
        set_page_focus();
    }
}

function add_invoice_item(item) {
    if (count == 1) {
        stlitems = {};
    }
    if (item == null){
		return;
	}
    var item_id = item.id;
    if (!stlitems[item_id]) {
        stlitems[item_id] = item;
		stlitems[item_id].order = new Date().getTime();
		localStorage.setItem('stlitems', JSON.stringify(stlitems));
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

$(document).ready(function() {
	var academic_year = $("#stlacademic_year").val();
	var biller        = $("#stlbiller").val();
	var program       = $('#stlprogram').val();
	var skill         = $('#stlskill').val();
	var grade         = $('#stlgrade').val();
	var timeshift     = $('#stltimeshift').val();

	var stlsection_   = localStorage.getItem('stlsection');
	var stlclass_     = localStorage.getItem('stlclass');

	setSections(program, skill, grade, stlsection_);
	setClasses(academic_year, biller, program, skill, grade, timeshift, stlclass_);

	$(document).on('change', '#stlprogram, #stlskill, #stlgrade', function() {
		var program = $('#stlprogram').val();
		var skill   = $('#stlskill').val();
		var grade   = $('#stlgrade').val();
		setSections(program, skill, grade, null);
	});

	$(document).on('change', '#stlacademic_year, #stlbiller, #stlprogram, #stlskill, #stlgrade, #stltimeshift', function() {
		var academic_year = $("#stlacademic_year").val();
		var biller        = $("#stlbiller").val();
		var program       = $('#stlprogram').val();
		var skill         = $('#stlskill').val();
		var grade         = $('#stlgrade').val();
		var timeshift     = $('#stltimeshift').val();
		setClasses(academic_year, biller, program, skill, grade, timeshift, null);
	});
});

function setSections(program, skill, grade, section) {
	if (program != null && skill != null && grade != null) {
		$.ajax({
			type: 'GET',
			url: site.base_url + 'schools/getSectionsBy_program_skill_grade',
			data: { program_id: program, skill_id: skill, grade_id: grade },
			dataType: 'json',
			success: function (data) {
				$('#stlsection').find('option').remove().end();
				$('#stlsection').append('<option value="" selected="selected">Please select section</option>');
				if (data) {
					$(data).each(function (element, value){
						$('#stlsection').append('<option value="' + value.id + '">' + value.name + '</option>');
					});
				}
				$("#stlsection").select2('val', section);
			}, 
		});
	}
}

function setClasses(academic_year, biller, program, skill, grade, timeshift, class_id) {
	if (academic_year != null && biller != null && program != null && skill != null && grade != null) {
		$.ajax({
			type: 'GET',
			url: site.base_url + 'schools/getClassesBy_academic_biller_program_skill_grade_timeshift',
			data: { 
				// academic_year: academic_year,
				biller_id: biller,
				program_id: program, 
				skill_id: skill, 
				grade_id: grade, 
				timeshift_id: timeshift 
			},
			dataType: 'json',
			success: function (data) {
				$('#stlclass').find('option').remove().end();
				$('#stlclass').append('<option value="" selected="selected">Please select class</option>');
				if (data) {
					$(data).each(function (element, value){
						$('#stlclass').append('<option value="' + value.id + '">' + value.name + '</option>');
					});
				}
				$("#stlclass").select2('val', class_id);
				setClass();
			}, 
		});
	}
}

function setGrade() {
	var grade = $('#stlgrade option:selected').text();
	$('#grade').val(grade);
}

function setClass() {
	var class_name = $('#stlclass option:selected').text();
	$('#class').val(class_name);
}

function setProgram() {
	var program_name = $('#stlprogram option:selected').text();
	$('#program').val(program_name);
}