$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
	if (site.settings.set_focus != 1) {
		$('#add_item').focus();
	}
    if (localStorage.getItem('cdnitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {

                    if (localStorage.getItem('cdnref')) {
                        localStorage.removeItem('cdnref');
                    }
                    if (localStorage.getItem('cdnwarehouse')) {
                        localStorage.removeItem('cdnwarehouse');
                    }
                    if (localStorage.getItem('cdnnote')) {
                        localStorage.removeItem('cdnnote');
                    }
                    if (localStorage.getItem('cdncustomer')) {
                        localStorage.removeItem('cdncustomer');
                    }
                    if (localStorage.getItem('cdndate')) {
                        localStorage.removeItem('cdndate');
                    }
                    if (localStorage.getItem('cdnbiller')) {
                        localStorage.removeItem('cdnbiller');
                    }
					if (localStorage.getItem('cdndeparture_time')) {
						localStorage.removeItem('cdndeparture_time');
					}
					if (localStorage.getItem('cdnseal_number')) {
						localStorage.removeItem('cdnseal_number');
					}
					if (localStorage.getItem('cdntruck')) {
						localStorage.removeItem('cdntruck');
					}
					if (localStorage.getItem('cdnlocation')) {
						localStorage.removeItem('cdnlocation');
					}
					if (localStorage.getItem('cdnslump')) {
						localStorage.removeItem('cdnslump');
					}
					if (localStorage.getItem('cdncasting_type')) {
						localStorage.removeItem('cdncasting_type');
					}
					if (localStorage.getItem('cdnstregth')) {
						localStorage.removeItem('cdnstregth');
					}
					if (localStorage.getItem('cdnquantity')) {
						localStorage.removeItem('cdnquantity');
					}
					if (localStorage.getItem('cdnpump')) {
						localStorage.removeItem('cdnpump');
					}
					if (localStorage.getItem('cdnpump_move')) {
						localStorage.removeItem('cdnpump_move');
					}
                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

    $('#cdnref').change(function (e) {
        localStorage.setItem('cdnref', $(this).val());
    });
    if (cdnref = localStorage.getItem('cdnref')) {
        $('#cdnref').val(cdnref);
    }
    $('#cdnwarehouse').change(function (e) {
        localStorage.setItem('cdnwarehouse', $(this).val());
    });
    if (cdnwarehouse = localStorage.getItem('cdnwarehouse')) {
        $('#cdnwarehouse').select2("val", cdnwarehouse);
    }
    $('#cdnnote').redactor('destroy');
    $('#cdnnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('cdnnote', v);
        }
    });
    if (cdnnote = localStorage.getItem('cdnnote')) {
        $('#cdnnote').redactor('set', cdnnote);
    }
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
});

