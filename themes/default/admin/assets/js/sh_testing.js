$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('tslitems')) {
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
	
	$(document).on('change', '#tsldate', function (e) {
		localStorage.setItem('tsldate', $(this).val());
	});
	if (tsldate = localStorage.getItem('tsldate')) {
		$('#tsldate').val(tsldate);
	}
	$(document).on('change', '#tslbiller', function (e) {
		localStorage.setItem('tslbiller', $(this).val());
	});
	if (tslbiller = localStorage.getItem('tslbiller')) {
		$('#tslbiller').val(tslbiller);
	}
	
	$(document).on('change', '#tslcustomer', function (e) {
		localStorage.setItem('tslcustomer', $(this).val());
	});
	if (tslcustomer = localStorage.getItem('tslcustomer')) {
		$('#tslcustomer').val(tslcustomer);
	}
	$(document).on('change', '#tslgender', function (e) {
		localStorage.setItem('tslgender', $(this).val());
	});
	if (tslgender = localStorage.getItem('tslgender')) {
		$('#tslgender').val(tslgender);
	}
	$(document).on('change', '#tslethnicity', function (e) {
		localStorage.setItem('tslethnicity', $(this).val());
	});
	if (tslethnicity = localStorage.getItem('tslethnicity')) {
		$('#tslethnicity').val(tslethnicity);
	}
	$(document).on('change', '#tslnationality', function (e) {
		localStorage.setItem('tslnationality', $(this).val());
	});
	if (tslnationality = localStorage.getItem('tslnationality')) {
		$('#tslnationality').val(tslnationality);
	}
	$(document).on('change', '#tslphone', function (e) {
		localStorage.setItem('tslphone', $(this).val());
	});
	if (tslphone = localStorage.getItem('tslphone')) {
		$('#tslphone').val(tslphone);
	}
	$(document).on('change', '#tslgroup', function (e) {
		localStorage.setItem('tslgroup', $(this).val());
	});
	if (tslgroup = localStorage.getItem('tslgroup')) {
		$('#tslgroup').val(tslgroup);
	}
	$(document).on('change', '#tslrelationship', function (e) {
		localStorage.setItem('tslrelationship', $(this).val());
	});
	if (tslrelationship = localStorage.getItem('tslrelationship')) {
		$('#tslrelationship').val(tslrelationship);
	}
	
	
	$(document).on('change', '#tslstname', function (e) {
		localStorage.setItem('tslstname', $(this).val());
	});
	if (tslstname = localStorage.getItem('tslstname')) {
		$('#tslstname').val(tslstname);
	}
	
	$(document).on('change', '#tslstname_latin', function (e) {
		localStorage.setItem('tslstname_latin', $(this).val());
	});
	if (tslstname_latin = localStorage.getItem('tslstname_latin')) {
		$('#tslstname_latin').val(tslstname_latin);
	}
	
	$(document).on('change', '#tslstdob', function (e) {
		localStorage.setItem('tslstdob', $(this).val());
	});
	if (tslstdob = localStorage.getItem('tslstdob')) {
		$('#tslstdob').val(tslstdob);
	}
	
	$(document).on('change', '#tslstgender', function (e) {
		localStorage.setItem('tslstgender', $(this).val());
	});
	if (tslstgender = localStorage.getItem('tslstgender')) {
		$('#tslstgender').val(tslstgender);
	}
	
	$(document).on('change', '#tslstethnicity', function (e) {
		localStorage.setItem('tslstethnicity', $(this).val());
	});
	if (tslstethnicity = localStorage.getItem('tslstethnicity')) {
		$('#tslstethnicity').val(tslstethnicity);
	}

	$(document).on('change', '#tslacademic_year', function (e) {
		localStorage.setItem('tslacademic_year', $(this).val());
	});
	if (tslacademic_year = localStorage.getItem('tslacademic_year')) {
		$('#tslacademic_year').val(tslacademic_year);
	}
	
	$('#tslnote').redactor('destroy');
	$('#tslnote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('tslnote', v);
		}
	});
	if (tslnote = localStorage.getItem('tslnote')) {
		$('#tslnote').redactor('set', tslnote);
	}
	
	$(document).on('click', '.tsldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete tslitems[item_id];
        row.remove();
        if(tslitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('tslitems', JSON.stringify(tslitems));
            loadItems();
            return;
        }
    });
	
	$(document).on("change", '.o_grade', function () {
        var row = $(this).closest('tr');
        var o_grade = $(this).val(),
        item_id = row.attr('data-item-id');
        tslitems[item_id].row.o_grade = o_grade;
        localStorage.setItem('tslitems', JSON.stringify(tslitems));
        loadItems();
    });
	
	$(document).on("change", '.o_academic_year', function () {
        var row = $(this).closest('tr');
        var o_academic_year = $(this).val(),
        item_id = row.attr('data-item-id');
        tslitems[item_id].row.o_academic_year = o_academic_year;
        localStorage.setItem('tslitems', JSON.stringify(tslitems));
        loadItems();
    });  
	
	$(document).on("change", '.o_school', function () {
        var row = $(this).closest('tr');
        var o_school = $(this).val(),
        item_id = row.attr('data-item-id');
        tslitems[item_id].row.o_school = o_school;
        localStorage.setItem('tslitems', JSON.stringify(tslitems));
        loadItems();
    });  
	
	  
	$(document).on("change", '.n_grade', function () {
        var row = $(this).closest('tr');
        var n_grade = $(this).val(),
        item_id = row.attr('data-item-id');
        tslitems[item_id].row.n_grade = n_grade;
        localStorage.setItem('tslitems', JSON.stringify(tslitems));
        loadItems();
    });
	
	
	$('select').select2();
});

function clearLS(){
	if (localStorage.getItem('tsldate')) {
		localStorage.removeItem('tsldate');
	}
	if (localStorage.getItem('tslbiller')) {
		localStorage.removeItem('tslbiller');
	}
	if (localStorage.getItem('tslacademic_year')) {
		localStorage.removeItem('tslacademic_year');
	}
	if (localStorage.getItem('tslnote')) {
		localStorage.removeItem('tslnote');
	}
	if (localStorage.getItem('tslitems')) {
		localStorage.removeItem('tslitems');
	}
	if (localStorage.getItem('tslcustomer')) {
		localStorage.removeItem('tslcustomer');
	}
	if (localStorage.getItem('tslgender')) {
		localStorage.removeItem('tslgender');
	}
	if (localStorage.getItem('tslethnicity')) {
		localStorage.removeItem('tslethnicity');
	}
	if (localStorage.getItem('tslnationality')) {
		localStorage.removeItem('tslnationality');
	}
	if (localStorage.getItem('tslphone')) {
		localStorage.removeItem('tslphone');
	}
	if (localStorage.getItem('tslrelationship')) {
		localStorage.removeItem('tslrelationship');
	}
	if (localStorage.getItem('tslstname')) {
		localStorage.removeItem('tslstname');
	}
	if (localStorage.getItem('tslstname_latin')) {
		localStorage.removeItem('tslstname_latin');
	}
	if (localStorage.getItem('tslstdob')) {
		localStorage.removeItem('tslstdob');
	}
	if (localStorage.getItem('tslstgender')) {
		localStorage.removeItem('tslstgender');
	}
	if (localStorage.getItem('tslstethnicity')) {
		localStorage.removeItem('tslstethnicity');
	}
	if (localStorage.getItem('tslgroup')) {
		localStorage.removeItem('tslgroup');
	}
}


function loadItems() {
    if (localStorage.getItem('tslitems')) {
        count = 1;
        an = 1;
        $("#tslTable tbody").empty();
        tslitems = JSON.parse(localStorage.getItem('tslitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(tslitems, function(o){return [parseInt(o.order)];}) :   tslitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = item.id;
            var row_no = item_id;
			var grade_opt = $("<select name=\"o_grade[]\" class=\"form-control o_grade select\" />");
			var n_grade_opt = $("<select name=\"n_grade[]\" class=\"form-control n_grade select\" />");
			if(grades != false){
				if(item.o_grade == 0) {
					$("<option />", {value: 0, text: 'N/A', selected:true}).appendTo(grade_opt);
				} else {
					$("<option />", {value: 0, text: 'N/A'}).appendTo(grade_opt);
				}
				$.each(grades, function () {
					if(this.id == item.row.o_grade) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(grade_opt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(grade_opt);
					}
					if(this.id == item.row.n_grade) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(n_grade_opt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(n_grade_opt);
					}
				});
			}
			
			var o_schools = "<input list='o_schools' name='o_school[]' class='o_school form-control' value='"+item.row.o_school+"'>";
			if(other_schools){
				o_schools += "<datalist id='o_schools'>";
				$.each(other_schools, function () {
					o_schools += "<option value='"+this.name+"'>";
				});
				o_schools += "</datalist>";
			}
			
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input type="hidden" value="'+item.row.id+'" name="program_id[]"/>'+item.row.name+'</td>';
			tr_html += '<td>'+(grade_opt.get(0).outerHTML)+'</td>';
			tr_html += '<td><input name="o_academic_year[]" class="o_academic_year form-control year" type="text" value="'+item.row.o_academic_year+'"/></td>';
			tr_html += '<td>'+o_schools+'</td></td>';
			tr_html += '<td>'+(n_grade_opt.get(0).outerHTML)+'</td>';
			newTr.html(tr_html);
            newTr.prependTo("#tslTable");
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

