$(window).load(function() {
    $('#loading').fadeOut('slow');
//    $.cookie('bpas_theme_fixed', 'yes', { path: '/' });
//    cssStyle();
});

function cssStyle() {
    if ($.cookie('bpas_style') == 'light') {
        $('link[href="' + site.assets + 'styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.assets + 'styles/blue.css"]').remove();
        $('<link>')
            .appendTo('head')
            .attr({ type: 'text/css', rel: 'stylesheet' })
            .attr('href', site.assets + 'styles/light.css');
    } else if ($.cookie('bpas_style') == 'blue') {
        $('link[href="' + site.assets + 'styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.assets + 'styles/light.css"]').remove();
        $('<link>')
            .appendTo('head')
            .attr({ type: 'text/css', rel: 'stylesheet' })
            .attr('href', '' + site.assets + 'styles/blue.css');
    } else {
        $('link[href="' + site.assets + 'styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.assets + 'styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.assets + 'styles/light.css"]').remove();
        $('link[href="' + site.assets + 'styles/blue.css"]').remove();
    }

    if ($('#sidebar-left').hasClass('minified')) {
        $.cookie('bpas_theme_fixed', 'no', { path: '/' });
        $('#content, #sidebar-left, #header').removeAttr('style');
        $('#sidebar-left').removeClass('sidebar-fixed');
        $('#content').removeClass('content-with-fixed');
        $('#fixedText').text('Fixed');
        $('#main-menu-act')
            .addClass('full visible-md visible-lg')
            .show();
        $('#fixed').removeClass('fixed');

    } else {
        if (site.settings.rtl == 1) {
            $.cookie('bpas_theme_fixed', 'no', { path: '/' });
        }
        if ($.cookie('bpas_theme_fixed') == 'yes') {
            
            $('#content-con').css('z-index',-1);
            $('#content').css('z-index',-1);
            
            $('#content').addClass('content-with-fixed');
            $('#sidebar-left')
                .addClass('sidebar-fixed')
                .css('z-index',0)
                .css('height', $(window).height() - 80);
            $('#header')
                .css('position', 'fixed')
                .css('top', '0')
                .css('width', '100%');
            $('#fixedText').text('Static');
            $('#main-menu-act')
                .removeAttr('class')
                .hide();
            $('#fixed').addClass('fixed');
            $('#sidebar-left').css('overflow', 'hidden');
            $('#sidebar-left').perfectScrollbar({ suppressScrollX: true });
        } else {
            $('#content, #sidebar-left, #header').removeAttr('style');
            $('#sidebar-left').removeClass('sidebar-fixed');
            $('#content').removeClass('content-with-fixed');
            $('#fixedText').text('Fixed');
            $('#main-menu-act')
                .addClass('full visible-md visible-lg')
                .show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
            $('.lt td.sidebar-con')
                .css('padding-top', '40px');
        }
    }
    widthFunctions();
}
$('#csv_file').change(function(e) {
    v = $(this).val();
    if (v != '') {
        var validExts = new Array('.csv');
        var fileExt = v;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            e.preventDefault();
            bootbox.alert('Invalid file selected. Only .csv file is allowed.');
            $(this).val('');
            $(this).fileinput('clear');
            $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'csv_file', 'NOT_VALIDATED');
            return false;
        } else return true;
    }
});

$(document).ready(function() {
    $('#suggest_product').autocomplete({
        source: site.base_url + 'reports/suggestions',
        select: function(event, ui) {
            $('#report_product_id').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function(event, ui) {
            if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this)
                    .data('ui-autocomplete')
                    ._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    $(document).on('blur', '#suggest_product', function(e) {
        if (!$(this).val()) {
            $('#report_product_id').val('');
        }
    });
    $('#suggest_product2').autocomplete({
        source: site.base_url + 'reports/suggestions',
        select: function(event, ui) {
            $('#report_product_id2').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function(event, ui) {
            if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this)
                    .data('ui-autocomplete')
                    ._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    $(document).on('blur', '#suggest_product2', function(e) {
        if (!$(this).val()) {
            $('#report_product_id').val('');
        }
    });
    // $('#random_num').click(function() {
    //     $(this)
    //         .parent('.input-group')
    //         .children('input')
    //         .val(generateCardNo(8));
    // });

    $('body').on('click','#random_num',function(){
        var unit_id = $("#unit").val();
         $(this).parent('.input-group').children('input').val(generateCardNo(8));
        $("#unit_id_"+unit_id+"").val($("#code").val());
        $("#unit_code_"+unit_id+"").val($("#code").val());
    });
    
    
    $('#toogle-customer-read-attr').click(function() {
        var icus = $(this)
            .closest('.input-group')
            .find("input[name='customer']");
        var nst = icus.is('[readonly]') ? false : true;
        icus.select2('readonly', nst);
        return false;
    });
    $('.top-menu-scroll').perfectScrollbar();
    $('#fixed').click(function(e) {
        e.preventDefault();
        if ($('#sidebar-left').hasClass('minified')) {
            bootbox.alert('Unable to fix minified sidebar');
        } else {
            if ($(this).hasClass('fixed')) {
                $.cookie('bpas_theme_fixed', 'no', { path: '/' });
            } else {
                $.cookie('bpas_theme_fixed', 'yes', { path: '/' });
            }
            cssStyle();
        }
    });
});

function widthFunctions(e) {
    var l = $('#sidebar-left').outerHeight(true),
        c = $('#content').height(),
        co = $('#content').outerHeight(),
        h = $('header').height(),
        f = $('footer').height(),
        wh = $(window).height(),
        ww = $(window).width();
    if (ww < 992) {
        $('#main-menu-act')
            .removeClass('minified')
            .addClass('full')
            .find('i')
            .removeClass('fa-angle-double-right')
            .addClass('fa-angle-double-left');
        $('body').removeClass('sidebar-minified');
        $('#content').removeClass('sidebar-minified');
        $('#sidebar-left').removeClass('minified');
        if ($.cookie('bpas_theme_fixed') == 'yes') {
            $.cookie('bpas_theme_fixed', 'no', { path: '/' });
            $('#content, #sidebar-left, #header').removeAttr('style');
            $('#sidebar-left').css('overflow-y', 'visible');
            $('#fixedText').text('Fixed');
            $('#main-menu-act')
                .addClass('full visible-md visible-lg')
                .show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
        }
    }
    if (ww < 998 && ww > 750) {
        $('#main-menu-act').hide();
        $('body').addClass('sidebar-minified');
        $('#content').addClass('sidebar-minified');
        $('#sidebar-left').addClass('minified');
        $('.dropmenu > .chevron')
            .removeClass('opened')
            .addClass('closed');
        $('.dropmenu')
            .parent()
            .find('ul')
            .hide();
        $('#sidebar-left > div > ul > li > a > .chevron')
            .removeClass('closed')
            .addClass('opened');
        $('#sidebar-left > div > ul > li > a').addClass('open');
        $('#fixed').hide();
    }
    if (ww > 1024 && $.cookie('bpas_sidebar') != 'minified') {
        $('#main-menu-act')
            .removeClass('minified')
            .addClass('full')
            .find('i')
            .removeClass('fa-angle-double-right')
            .addClass('fa-angle-double-left');
        $('body').removeClass('sidebar-minified');
        $('#content').removeClass('sidebar-minified');
        $('#sidebar-left').removeClass('minified');
        $('#sidebar-left > div > ul > li > a > .chevron')
            .removeClass('opened')
            .addClass('closed');
        $('#sidebar-left > div > ul > li > a').removeClass('open');
        $('#fixed').show();
    }
    if ($.cookie('bpas_theme_fixed') == 'yes') {
        $('#content').addClass('content-with-fixed');
        $('#sidebar-left')
            .addClass('sidebar-fixed')
            .css('height', $(window).height() - 80);
    }
    if (ww > 767) {
        wh - 80 > l && $('#sidebar-left').css('min-height', wh - h - f - 30);
        wh - 80 > c && $('#content').css('min-height', wh - h - f - 30);
    } else {
        $('#sidebar-left').css('min-height', '0px');
        $('.content-con').css('max-width', ww);
    }
    //$(window).scrollTop($(window).scrollTop() + 1);
}

jQuery(document).ready(function(e) {
    window.location.hash ? e('#myTab a[href="' + window.location.hash + '"]').tab('show') : e('#myTab a:first').tab('show');
    e('#myTab2 a:first, #dbTab a:first').tab('show');
    e('#myTab a, #myTab2 a, #dbTab a').click(function(t) {
        t.preventDefault();
        e(this).tab('show');
    });
    e('[rel="popover"],[data-rel="popover"],[data-toggle="popover"]').popover();
    e('#toggle-fullscreen')
        .button()
        .click(function() {
            var t = e(this),
                n = document.documentElement;
            if (!t.hasClass('active')) {
                e('#thumbnails').addClass('modal-fullscreen');
                n.webkitRequestFullScreen ?
                    n.webkitRequestFullScreen(window.Element.ALLOW_KEYBOARD_INPUT) :
                    n.mozRequestFullScreen && n.mozRequestFullScreen();
            } else {
                e('#thumbnails').removeClass('modal-fullscreen');
                (document.webkitCancelFullScreen || document.mozCancelFullScreen || e.noop).apply(document);
            }
        });
    e('.btn-close').click(function(t) {
        t.preventDefault();
        e(this)
            .parent()
            .parent()
            .parent()
            .fadeOut();
    });
    e('.btn-minimize').click(function(t) {
        t.preventDefault();
        var n = e(this)
            .parent()
            .parent()
            .next('.box-content');
        n.is(':visible') ?
            e('i', e(this))
            .removeClass('fa-chevron-up')
            .addClass('fa-chevron-down') :
            e('i', e(this))
            .removeClass('fa-chevron-down')
            .addClass('fa-chevron-up');
        n.slideToggle('slow', function() {
            widthFunctions();
        });
    });
});

jQuery(document).ready(function(e) {
    e('#main-menu-act').click(function() {
        if (e(this).hasClass('full')) {
            $.cookie('bpas_sidebar', 'minified', { path: '/' });
            e(this)
                .removeClass('full')
                .addClass('minified')
                .find('i')
                .removeClass('fa-angle-double-left')
                .addClass('fa-angle-double-right');
            e('body').addClass('sidebar-minified');
            e('#content').addClass('sidebar-minified');
            e('#sidebar-left').addClass('minified');
            e('.dropmenu > .chevron')
                .removeClass('opened')
                .addClass('closed');
            e('.dropmenu')
                .parent()
                .find('ul')
                .hide();
            e('#sidebar-left > div > ul > li > a > .chevron')
                .removeClass('closed')
                .addClass('opened');
            e('#sidebar-left > div > ul > li > a').addClass('open');
            $('#fixed').hide();
            $('#sidebar-left #logo').hide();

        } else {
            $.cookie('bpas_sidebar', 'full', { path: '/' });
            e(this)
                .removeClass('minified')
                .addClass('full')
                .find('i')
                .removeClass('fa-angle-double-right')
                .addClass('fa-angle-double-left');
            e('body').removeClass('sidebar-minified');
            e('#content').removeClass('sidebar-minified');
            e('#sidebar-left').removeClass('minified');
            e('#sidebar-left > div > ul > li > a > .chevron')
                .removeClass('opened')
                .addClass('closed');
            e('#sidebar-left > div > ul > li > a').removeClass('open');
            $('#fixed').show();
            $('#sidebar-left #logo').show();
        }
        return false;
    });
    e('.dropmenu').click(function(t) {

        t.preventDefault();
        if (e('#sidebar-left').hasClass('minified')) {
            if (!e(this).hasClass('open')) {
                e(this)
                    .parent()
                    .find('ul')
                    .first()
                    .slideToggle();
                e(this)
                    .find('.chevron')
                    .hasClass('closed') ?
                    e(this)
                    .find('.chevron')
                    .removeClass('closed')
                    .addClass('opened') :
                    e(this)
                    .find('.chevron')
                    .removeClass('opened')
                    .addClass('closed');
            }
        } else {
            // $('.dropmenu > .chevron')
            //     .removeClass('opened')
            //     .addClass('closed');
            // $('.dropmenu')
            //     .parent()
            //     .find('ul')
            //     .hide();
            e(this).parent().find('ul').first().slideToggle();
            e(this).find('.chevron').hasClass('closed') ? e(this).find('.chevron').removeClass('closed').addClass('opened') :
                e(this).find('.chevron').removeClass('opened').addClass('closed');
        }
    });
    if (e('#sidebar-left').hasClass('minified')) {
        e('#sidebar-left > div > ul > li > a > .chevron')
            .removeClass('closed')
            .addClass('opened');
        e('#sidebar-left > div > ul > li > a').addClass('open');
        e('body').addClass('sidebar-minified');
    }
});

$(document).ready(function() {
    cssStyle();
    $('select, .select')
        .not('.skip')
        .select2({ minimumResultsForSearch: 7 });

        $('#supplier, #rsupplier, .rsupplier').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + 'suppliers/suggestions',
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: '', text: 'No Match Found' }] };
                    }
                },
            },
        });
        $('#customer, #rcustomer, .ssr-customer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + 'customers/suggestions',
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: '', text: 'No Match Found' }] };
                    }
                },
            },
        });
       
    $('.input-tip').tooltip({
        placement: 'top',
        html: true,
        trigger: 'hover focus',
        container: 'body',
        title: function() {
            return $(this).attr('data-tip');
        },
    });
    $('.input-pop').popover({
        placement: 'top',
        html: true,
        trigger: 'hover',
        container: 'body',
        content: function() {
            return $(this).attr('data-tip');
        },
        title: function() {
            return '<b>' + $('label[for="' + $(this).attr('id') + '"]').text() + '</b>';
        },
    });
});

$(document).on('click', '*[data-toggle="lightbox"]', function(event) {
    event.preventDefault();
    $(this).ekkoLightbox();
});
$(document).on('click', '*[data-toggle="popover"]', function(event) {
    event.preventDefault();
    $(this).popover();
});

$(document)
    .ajaxStart(function() {
        $('#ajaxCall').show();
    })
    .ajaxStop(function() {
        $('#ajaxCall').hide();
    });

$(document).ready(function() {
    $('input[type="checkbox"],[type="radio"]')
        .not('.skip')
        .iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%',
        });
    $('textarea')
        .not('.skip')
        .redactor({
            buttons: [
                'formatting',
                '|',
                'alignleft',
                'aligncenter',
                'alignright',
                'justify',
                '|',
                'bold',
                'italic',
                'underline',
                '|',
                'unorderedlist',
                'orderedlist',
                '|',
                /*'image', 'video',*/
                'link',
                '|',
                'html',
            ],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function(e) {
                var editor = this.$editor.next('textarea');
                if ($(editor).attr('required')) {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', $(editor).attr('name'));
                }
            },
        });
    $(document).on('click', '.file-caption', function() {
        $(this)
            .next('.input-group-btn')
            .children('.btn-file')
            .children('input.file')
            .trigger('click');
    });
});

function suppliers(ele) {
    $(ele).select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + 'suppliers/suggestions',
            dataType: 'json',
            quietMillis: 15,
            data: function(term, page) {
                return {
                    term: term,
                    limit: 10,
                };
            },
            results: function(data, page) {
                if (data.results != null) {
                    return { results: data.results };
                } else {
                    return { results: [{ id: '', text: 'No Match Found' }] };
                }
            },
        },
    });
}

$(function() {
    $('.datetime').datetimepicker({
        format: site.dateFormats.js_ldate,
        fontAwesome: true,
        language: 'bpas',
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0,
    });
    $('.date').datetimepicker({
        format: site.dateFormats.js_sdate,
        fontAwesome: true,
        language: 'bpas',
        todayBtn: 1,
        autoclose: 1,
        minView: 2,
    });
    $(document).on('focus', '.date', function(t) {
        $(this).datetimepicker({ format: site.dateFormats.js_sdate, fontAwesome: true, todayBtn: 1, autoclose: 1, minView: 2 });
    });
    $(document).on('focus', '.datetime', function() {
        $(this).datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0,
        });
    });
    var startDate = moment()
        .subtract(89, 'days')
        .format('YYYY-MM-DD');
    var endDate = moment().format('YYYY-MM-DD');
    $('#log-date').datetimepicker({
        startDate: startDate,
        endDate: endDate,
        format: site.dateFormats.js_sdate,
        fontAwesome: true,
        language: 'bpas',
        todayBtn: 1,
        autoclose: 1,
        minView: 2,
    });
    $(document).on('focus', '#log-date', function(t) {
        $(this).datetimepicker({
            startDate: startDate,
            endDate: endDate,
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            todayBtn: 1,
            autoclose: 1,
            minView: 2,
        });
    });
    $('#log-date').on('changeDate', function(ev) {
        var date = moment(ev.date.valueOf()).format('YYYY-MM-DD');
        refreshPage(date);
    });
    //--------------month------------
    $('.date_time').datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'bms', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
    $(document).on('focus','.date_time', function() {
        $(this).datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
    });
    $(document).on('focus','.month', function(t) {
        $(this).datetimepicker({format: "mm/yyyy", fontAwesome: true, autoclose: 1,startView: 3, minView: 3 });
    });

    $(document).on('focus','.year', function(t) {
        $(this).datetimepicker({format: "yyyy", fontAwesome: true, autoclose: 1,startView: 4, minView: 4 });
    });
    
    $(document).on('focus','.month_only', function(t) {
        $(this).datetimepicker({format: "mm", fontAwesome: true, autoclose: 1,startView: 3, minView: 5 });
    });
    $('.timepicker').datetimepicker({ format: 'hh:ii:ss', fontAwesome: true, autoclose: 1, startView: 0,todayBtn: 1});
});

$(document).ready(function() {
    $('#dbTab a').on('shown.bs.tab', function(e) {
        var newt = $(e.target).attr('href');
        var oldt = $(e.relatedTarget).attr('href');
        $(oldt).hide();
        //$(newt).hide().fadeIn('slow');
        $(newt)
            .hide()
            .slideDown('slow');
    });
    $('.dropdown').on('show.bs.dropdown', function(e) {
        $(this)
            .find('.dropdown-menu')
            .first()
            .stop(true, true)
            .slideDown('fast');
    });
    $('.dropdown').on('hide.bs.dropdown', function(e) {
        $(this)
            .find('.dropdown-menu')
            .first()
            .stop(true, true)
            .slideUp('fast');
    });
    $('.hideComment').click(function() {
        $.ajax({ url: site.base_url + 'welcome/hideNotification/' + $(this).attr('id') });
    });
    $('.tip').tooltip();
    $('body').on('click', '#delete', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form').submit();
    });
    $('body').on('click', '#sync_quantity', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#sync_account', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });

    $('body').on('click', '#preview', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#excel', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#pdf', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#labelProducts', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#barcodeProducts', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#combine', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
});

$(document).ready(function() {
    $('#product-search').click(function() {
        $('#product-search-form').submit();
    });
    //feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'},
    $('form[data-toggle="validator"]').bootstrapValidator({
        message: 'Please enter/select a value',
        submitButtons: 'input[type="submit"]',
    });
    fields = $('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#' + id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function() {
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('body').on('click', 'label', function(e) {
        var field_id = $(this).attr('for');
        if (field_id) {
            if ($('#' + field_id).hasClass('select')) {
                $('#' + field_id).select2('open');
                return false;
            }
        }
    });
    $('body').on('focus', 'select', function(e) {
        var field_id = $(this).attr('id');
        if (field_id) {
            if ($('#' + field_id).hasClass('select')) {
                $('#' + field_id).select2('open');
                return false;
            }
        }
    });
    $('#myModal').on('hidden.bs.modal', function() {
        $(this)
            .find('.modal-dialog')
            .empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
    });
    $('#myModal2').on('hidden.bs.modal', function() {
        $(this)
            .find('.modal-dialog')
            .empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
        $('#myModal').css('zIndex', '1050');
        $('#myModal').css('overflow-y', 'scroll');
    });
    $('#myModal2').on('show.bs.modal', function() {
        $('#myModal').css('zIndex', '1040');
    });
    $('.modal')
        .on('show.bs.modal', function() {
            $('#modal-loading').show();
            $('.blackbg').css('zIndex', '1041');
            $('.loader').css('zIndex', '1042');
        })
        .on('hide.bs.modal', function() {
            $('#modal-loading').hide();
            $('.blackbg').css('zIndex', '3');
            $('.loader').css('zIndex', '4');
        });
    $(document).on('click', '.po', function(e) {
        e.preventDefault();
        $('.po')
            .popover({ html: true, placement: 'left', trigger: 'manual' })
            .popover('show')
            .not(this)
            .popover('hide');
        return false;
    });
    $(document).on('click', '.po-close', function() {
        $('.po').popover('hide');
        return false;
    });
    $(document).on('click', '.po-delete', function(e) {
        var row = $(this).closest('tr');
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        console.log(link);
        var return_id = $(this).attr('data-return-id');
        $.ajax({
            type: 'get',
            url: link,
            dataType: 'json',
            success: function(data) {
                if (data.error == 1) {
                    addAlert(data.msg, 'danger');
                } else {
                    addAlert(data.msg, 'success');
                    if (oTable != '') {
                        oTable.fnDraw();
                    }
                }
            },
            error: function(data) {
                addAlert('Ajax call failed', 'danger');
            },
        });
        return false;
    });
    $(document).on('click', '.po-delete1', function(e) {
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var s = $(this).attr('id');
        var sp = s.split('__');
        $.ajax({
            type: 'get',
            url: link,
            dataType: 'json',
            success: function(data) {
                if (data.error == 1) {
                    addAlert(data.msg, 'danger');
                } else {
                    addAlert(data.msg, 'success');
                    if (oTable != '') {
                        oTable.fnDraw();
                    }
                }
            },
            error: function(data) {
                addAlert('Ajax call failed', 'danger');
            },
        });
        return false;
    });
    $('body').on('click', '.bpo', function(e) {
        e.preventDefault();
        $(this)
            .popover({ html: true, trigger: 'manual' })
            .popover('toggle');
        return false;
    });
    $('body').on('click', '.bpo-close', function(e) {
        $('.bpo').popover('hide');
        return false;
    });
    $('#genNo').click(function() {
        var no = generateCardNo();
        $(this)
            .parent()
            .parent('.input-group')
            .children('input')
            .val(no);
        return false;
    });
    $('#inlineCalc').calculator({ layout: ['_%+-CABS', '_7_8_9_/', '_4_5_6_*', '_1_2_3_-', '_0_._=_+'], showFormula: true });
    $('.calc').click(function(e) {
        e.stopPropagation();
    });
    $(document).on('click', '.sname', function(e) {
        var row = $(this).closest('tr');
        var itemid = row.find('.rid').val();
        $('#myModal').modal({ remote: site.base_url + 'products/modal_view/' + itemid });
        $('#myModal').modal('show');
    });


});

function addAlert(message, type) {
    $('.alerts-con')
        .empty()
        .append(
            '<div class="alert alert-' +
            type +
            '">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '&times;</button>' +
            message +
            '</div>'
        );
}

$(document).ready(function() {
    if ($.cookie('bpas_sidebar') == 'minified') {
        $('#main-menu-act')
            .removeClass('full')
            .addClass('minified')
            .find('i')
            .removeClass('fa-angle-double-left')
            .addClass('fa-angle-double-right');
        $('body').addClass('sidebar-minified');
        $('#content').addClass('sidebar-minified');
        $('#sidebar-left').addClass('minified');
        $('.dropmenu > .chevron')
            .removeClass('opened')
            .addClass('closed');
        $('.dropmenu')
            .parent()
            .find('ul')
            .hide();
        $('#sidebar-left > div > ul > li > a > .chevron')
            .removeClass('closed')
            .addClass('opened');
        $('#sidebar-left > div > ul > li > a').addClass('open');
        $('#fixed').hide();
        $('#sidebar-left #logo').hide();
       
    } else {
        $('#main-menu-act')
            .removeClass('minified')
            .addClass('full')
            .find('i')
            .removeClass('fa-angle-double-right')
            .addClass('fa-angle-double-left');
        $('body').removeClass('sidebar-minified');
        $('#content').removeClass('sidebar-minified');
        $('#sidebar-left').removeClass('minified');
        $('#sidebar-left > div > ul > li > a > .chevron')
            .removeClass('opened')
            .addClass('closed');
        $('#sidebar-left > div > ul > li > a').removeClass('open');
        $('#fixed').show();
    }
});

$(document).ready(function() {
    $('#daterange').daterangepicker({
            timePicker: true,
            format: site.dateFormats.js_sdate.toUpperCase() + ' HH:mm',
            ranges: {
                Today: [
                    moment()
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment(),
                ],
                Yesterday: [
                    moment()
                    .subtract('days', 1)
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment()
                    .subtract('days', 1)
                    .hours(23)
                    .minutes(59)
                    .seconds(59),
                ],
                'Last 7 Days': [
                    moment()
                    .subtract('days', 6)
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment()
                    .hours(23)
                    .minutes(59)
                    .seconds(59),
                ],
                'Last 30 Days': [
                    moment()
                    .subtract('days', 29)
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment()
                    .hours(23)
                    .minutes(59)
                    .seconds(59),
                ],
                'This Month': [
                    moment()
                    .startOf('month')
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment()
                    .endOf('month')
                    .hours(23)
                    .minutes(59)
                    .seconds(59),
                ],
                'Last Month': [
                    moment()
                    .subtract('month', 1)
                    .startOf('month')
                    .hours(0)
                    .minutes(0)
                    .seconds(0),
                    moment()
                    .subtract('month', 1)
                    .endOf('month')
                    .hours(23)
                    .minutes(59)
                    .seconds(59),
                ],
            },
        },
        function(start, end) {
            refreshPage(start.format('YYYY-MM-DD HH:mm'), end.format('YYYY-MM-DD HH:mm'));
        }
    );
});

function refreshPage(start, end) {
    if (end) {
        window.location.replace(CURI + '/' + encodeURIComponent(start) + '/' + encodeURIComponent(end));
    } else {
        window.location.replace(CURI + '/' + encodeURIComponent(start));
    }
}

function retina() {
    retinaMode = window.devicePixelRatio > 1;
    return retinaMode;
}

$(document).ready(function() {
    $('#cssLight').click(function(e) {
        e.preventDefault();
        $.cookie('bpas_style', 'light', { path: '/' });
        cssStyle();
        return true;
    });
    $('#cssBlue').click(function(e) {
        e.preventDefault();
        $.cookie('bpas_style', 'blue', { path: '/' });
        cssStyle();
        return true;
    });
    $('#cssBlack').click(function(e) {
        e.preventDefault();
        $.cookie('bpas_style', 'black', { path: '/' });
        cssStyle();
        return true;
    });
    $('#toTop').click(function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 100);
    });
    $(document).on('click', '.delimg', function(e) {
        e.preventDefault();
        var ele = $(this),
            id = $(this).attr('data-item-id');
        bootbox.confirm(lang.r_u_sure, function(result) {
            if (result == true) {
                $.get(site.base_url + 'products/delete_image/' + id, function(data) {
                    if (data.error === 0) {
                        addAlert(data.msg, 'success');
                        ele.parent('.gallery-image').remove();
                    }
                });
            }
        });
        return false;
    });
});
$(document).ready(function() {
    $(document).on('click', '.row_status', function(e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('invoice_link')) {
            $('#myModal').modal({ remote: site.base_url + 'sales/update_status/' + id });
            $('#myModal').modal('show');
        } else if (row.hasClass('purchase_link')) {
            $('#myModal').modal({ remote: site.base_url + 'purchases/update_status/' + id });
            $('#myModal').modal('show');
        } else if (row.hasClass('quote_link')) {
            $('#myModal').modal({ remote: site.base_url + 'quotes/update_status/' + id });
            $('#myModal').modal('show');
        } else if (row.hasClass('transfer_link')) {
            $('#myModal').modal({ remote: site.base_url + 'transfers/update_status/' + id });
            $('#myModal').modal('show');
        }else if (row.hasClass('repair_link')) {
            $('#myModal').modal({remote: site.base_url + 'repairs/view_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('item_link')) {
            $('#myModal').modal({remote: site.base_url + 'repairs/update_status/' + id});
            $('#myModal').modal('show');
        } else if (row.hasClass('sale_order_link')) {
            $('#myModal').modal({remote: site.base_url + 'sale_orders/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('rental_link')) {
            $('#myModal').modal({remote: site.base_url + 'rentals/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('con_delivery_link')) {
            $('#myModal').modal({remote: site.base_url + 'concretes/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('hr_contract_link')) {
            var employee_id = row.attr('class');
            employee_id = employee_id.replace('hr_contract_link ','');
            $('#myModal').modal({remote: site.base_url + 'hr/add_contract/' + employee_id});
            $('#myModal').modal('show');
        } else if (row.hasClass('clr_trucking_link')) {
            $('#myModal').modal({remote: site.base_url + 'clearances/update_trucking_status/' + id});
            $('#myModal').modal('show');
        }

        return false;
    });
});
$(document).ready(function () {
    $(document).on('click', '.approved_status3', function (e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('sale_order_link')) {
            $('#myModal').modal({ remote: site.base_url + 'sales_order/approved_status/' + id });
            $('#myModal').modal('show');
        }
        return false;
    });
});
$(document).ready(function () {
    $(document).on('click', '.approved_status2', function (e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('purchase_order_link')) {
            $('#myModal').modal({ remote: site.base_url + 'purchases_order/approved_status/' + id });
            $('#myModal').modal('show');
        }
        return false;
    });
});
$(document).ready(function() {
    $(document).on('click', '.approved_status', function(e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('purchase_request_link')) {
            $('#myModal').modal({ remote: site.base_url + 'purchases_request/approved_status/' + id });
            $('#myModal').modal('show');
        } else if (row.hasClass('project_link')) {
            $('#myModal').modal({remote: site.base_url + 'projects/update_status/' + id});
            $('#myModal').modal('show');
        }
        return false;
    });
});
/*
 $(window).scroll(function() {
    if ($(this).scrollTop()) {
        $('#toTop').fadeIn();
    } else {
        $('#toTop').fadeOut();
    }
 });
*/
$(document).on('ifChecked', '.checkth, .checkft', function(event) {
    $('.checkth, .checkft').iCheck('check');
    $('.multi-select').each(function() {
        $(this).iCheck('check');
    });
});
$(document).on('ifUnchecked', '.checkth, .checkft', function(event) {
    $('.checkth, .checkft').iCheck('uncheck');
    $('.multi-select').each(function() {
        $(this).iCheck('uncheck');
    });
});
$(document).on('ifUnchecked', '.multi-select', function(event) {
    $('.checkth, .checkft').attr('checked', false);
    $('.checkth, .checkft').iCheck('update');
});

function check_add_item_val() {
    $('#add_item').bind('keypress', function(e) {
        if (e.keyCode == 13 || e.keyCode == 9) {
            e.preventDefault();
            $(this).autocomplete('search');
        }
    });
}
function show_hide(e) {
    var values = " " + e;
    if (values.length > 80) {
        return "<span class='text'>" + values.substr(1, 80) + "....</span><a text='" + values + "' class='clview'>View</a>";
    } else {
        return values;
    }
}
$('body').on('click', '.clview', function () {
    var text = $(this).attr("text");
    if ($(this).text() == "View") {
        $(this).parent().find(".text").text(text);
        $(this).text("Hide");
    } else {
        $(this).parent().find(".text").text(text.substr(1, 80) + "....");
        $(this).text("View");
    }
});
function fd1(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('/');
        var bDate = aDate[2].split(' ');
        year = bDate[0], month = aDate[1], day = aDate[0];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + yea;
        else
            return oObj;
    } else {
        return '';
    }
}
function fd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + yea;
        else
            return oObj;
    } else {
        return '';
    }
}

function fldt(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year + " " + time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + year + " " + time;
        else
            return oObj;

    } else {
        return '';
    }
}
function fld(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        (year = aDate[0]), (month = aDate[1]), (day = bDate[0]), (time = bDate[1]);
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return day + '-' + month + '-' + year + ' ' + time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return day + '/' + month + '/' + year + ' ' + time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return day + '.' + month + '.' + year + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return month + '/' + day + '/' + year + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return month + '-' + day + '-' + year + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return month + '.' + day + '.' + year + ' ' + time;
        else return oObj;
    } else {
        return '';
    }
}

function fsd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return aDate[2] + '-' + aDate[1] + '-' + aDate[0];
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return aDate[2] + '/' + aDate[1] + '/' + aDate[0];
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return aDate[2] + '.' + aDate[1] + '.' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return aDate[1] + '/' + aDate[2] + '/' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return aDate[1] + '-' + aDate[2] + '-' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return aDate[1] + '.' + aDate[2] + '.' + aDate[0];
        else return oObj;
    } else {
        return '';
    }
}

function fldl(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        (year = aDate[0]), (month = aDate[1]), (day = bDate[0]), (time = bDate[1]);
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return year + '-' + month + '-' + day + ' ' + time;
        else return oObj;
    } else {
        return '';
    }
}

function fldd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        (year = aDate[0]), (month = aDate[1]), (day = bDate[0]), (time = bDate[1]);
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return day + '-' + month + '-' + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return day + '/' + month + '/' + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return day + '.' + month + '.' + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return month + '/' + day + '/' + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return month + '-' + day + '-' + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return month + '.' + day + '.' + year;
        else return oObj;
    } else {
        return '';
    }
}

function fldp(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        (year = aDate[0]), (month = aDate[1]), (day = bDate[0]), (time = bDate[1]);
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return time;
        else return oObj;
    } else {
        return '';
    }
}

function fsd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return aDate[2] + '-' + aDate[1] + '-' + aDate[0];
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return aDate[2] + '/' + aDate[1] + '/' + aDate[0];
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return aDate[2] + '.' + aDate[1] + '.' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return aDate[1] + '/' + aDate[2] + '/' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return aDate[1] + '-' + aDate[2] + '-' + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return aDate[1] + '.' + aDate[2] + '.' + aDate[0];
        else return oObj;
    } else {
        return '';
    }
}

function generateCardNo(x) {
    if (!x) {
        x = 16;
    }
    chars = '1234567890';
    no = '';
    for (var i = 0; i < x; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        no += chars.substring(rnum, rnum + 1);
    }
    return no;
}

function roundNumber(num, nearest) {
    if (!nearest) {
        nearest = 0.05;
    }
    return Math.round((num / nearest) * nearest);
}

function getNumber(x) {
    return accounting.unformat(x);
}

function formatQuantity(x) {
    return x != null ? '<div class="text-center">' + formatNumber(x, site.settings.qty_decimals) + '</div>' : '';
}

function formatQuantity2(x) {
    return x != null ? formatQuantityNumber(x, site.settings.qty_decimals) : '';
}

function formatQuantityNumber(x, d) {
    if (!d) {
        d = site.settings.qty_decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}

function formatQty(x) {
    return x != null ? formatNumber(x, site.settings.qty_decimals) : '';
}

function formatNumber(x, d) {
    if (!d && d != 0) {
        d = site.settings.decimals;
    }
    if (site.settings.sac == 1) {
        return formatSA(parseFloat(x).toFixed(d));
    }
    return accounting.formatNumber(x, d, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep);
}

function formatMoney(x, symbol) {
    if (!symbol) {
        symbol = '';
    }
    if (site.settings.sac == 1) {
        return (
            (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
            '' +
            formatSA(parseFloat(x).toFixed(site.settings.decimals)) +
            (site.settings.display_symbol == 2 ? site.settings.symbol : '')
        );
    }
    var fmoney = accounting.formatMoney(
        x,
        symbol,
        site.settings.decimals,
        site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep,
        site.settings.decimals_sep,
        '%s%v'
    );
    return (
        (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        fmoney +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '')
    );
}

function is_valid_discount(mixed_var) {
    if(/[0-9]%[^a-z]/gi.test(mixed_var) || /[0-9]%[^0-9]/gi.test(mixed_var)) return false;
    return is_numeric(mixed_var) || /([0-9]%)/i.test(mixed_var) ? true : false;
}

function is_numeric(mixed_var) {
    var whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
    return (
        (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -1)) &&
        mixed_var !== '' &&
        !isNaN(mixed_var)
    );
}

function is_float(mixed_var) {
    return +mixed_var === mixed_var && (!isFinite(mixed_var) || !!(mixed_var % 1));
}

function decimalFormat(x) {
    return '<div class="text-center">' + formatNumber(x != null ? x : 0) + '</div>';
}

function currencyFormat(x) {
    return '<div class="text-right">' + formatMoney(x != null ? x : 0) + '</div>';
}

function currencyFormatLeft(x) {
    return '<div class="text-left">' + formatMoney(x != null ? x : 0) + '</div>';
}

function formatDecimal(x, d) {
    if (!d) {
        d = site.settings.decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}
function formatDecimalRaw(x) {
    return parseFloat(accounting.formatNumber(x, 16, '', '.'));
}
function formatDecimals(x, d) {
    if (!d) {
        d = site.settings.decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.')).toFixed(d);
}
function concatFormat(x) {
    if (x != null) {
        var d = '',
            pqc = x.split('___');
        for (index = 0; index < pqc.length; ++index) {
            var pq = pqc[index];
            var v = pq.split('__');
            d += v[0]+'('+v[1]+')<br>';
        }
        return d;
    } else {
        return '';
    }
}
function pqFormat(x) {
    if (x != null) {
        var d = '',
            pqc = x.split('___');
        for (index = 0; index < pqc.length; ++index) {
            var pq = pqc[index];
            var v = pq.split('__');
            d += v[0] + ' (' + formatQuantity2(v[1]) + ')<br>';
        }
        return d;
    } else {
        return '';
    }
}
function QtyFormat(x) {
    if (x != null) {
        var qty = 0,
            pqc = x.split('___');
        for (index = 0; index < pqc.length; ++index) {
            var pq = pqc[index];
            var v = pq.split('__');
            qty += formatQuantity2(v[1]);
        }
        return qty;
    } else {
        return '';
    }
}

function hide(x) {
    return '<span class="hide">' + x + '</span>';
}
function checkbox(x) {
    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
}

function decode_html(value) {
    return $('<div/>')
        .html(value)
        .text();
}

function img_hl(x) {
    var image_link = x == null || x == '' ? 'no_image.png' : x;
    return (
        '<div class="text-center"><a href="' +
        site.url +
        'assets/uploads/' +
        image_link +
        '" data-toggle="lightbox"><img src="' +
        site.url +
        'assets/uploads/thumbs/' +
        image_link +
        '" alt="" style="width:30px; height:30px;" /></a></div>'
    );
}

function img_hl_x(x) {
    // var image_link = (x == null || x == '') ? 'no_image.png' : x;
    
    if (x == null || x == ''){
        return '';
    }else{
        return (
            '<div class="text-center"><a href="' +
            site.url +
            'assets/uploads/' +
            x +
            '" data-toggle="lightbox"><img src="' +
            site.url +
            'assets/uploads/' +
            x +
            '" alt="" style="width:30px; height:30px;" /></a></div>'
        );
    }
    
}

function attachment(x) {
    return x == null ?
        '' :
        '<div class="text-center"><a href="' +
        site.base_url +
        'welcome/download/' +
        x +
        '" class="tip" title="' +
        lang.download +
        '"><i class="fa fa-file"></i></a></div>';
}

function attachment2(x) {
    return x == null ?
        '' :
        '<div class="text-center"><a href="' +
        site.base_url +
        'welcome/download/' +
        x +
        '" class="tip" title="' +
        lang.download +
        '"><i class="fa fa-file-o"></i></a></div>';
}

function user_status(x) {
    var y = x.split('__');
    return y[0] == 1 ?
        '<a href="' +
        site.base_url +
        'auth/deactivate/' +
        y[1] +
        '" data-toggle="modal" data-target="#myModal"><span class="label label-success"><i class="fa fa-check"></i> ' +
        lang['active'] +
        '</span></a>' :
        '<a href="' +
        site.base_url +
        'auth/activate/' +
        y[1] +
        '"><span class="label label-danger"><i class="fa fa-times"></i> ' +
        lang['inactive'] +
        '</span><a/>';
}
function student_status(x) {
    if(x == null) {
        return '';
    } else if(x == 'suspend') {
        return '<div class="text-center"><span class="row_status label label-warning">'+lang[x]+'</span></div>';
    } else if(x == 'reconfirm') {
        return '<div class="text-center"><span class="row_status label label-other" style="background-color:#c4c718">'+lang[x]+'</span></div>';
    } else if(x == 'active') {
        return '<div class="text-center"><span class="row_status label label-success">'+lang[x]+'</span></div>';
    } else if(x == 'drop_out') {
        return '<div class="text-center"><span class="row_status label label-danger">'+lang[x]+'</span></div>';
    } else if(x == 'black_list') {
        return '<div class="text-center"><span class="row_status label label-other" style="background-color:#2e2e2d">'+lang[x]+'</span></div>';
    } else if(x == 'graduate') {
        return '<div class="text-center"><span class="row_status label label-other" style="background-color:#081775">'+lang[x]+'</span></div>';
    } else {
        return '<div class="text-center"><span class="row_status label label-default">'+x+'</span></div>';
    }
}
function currency_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'USD' || x == 'usd') {
        return '<div class="text-center"><span class="label label-warning">' + [x] + '</span></div>';
    } else if (x == 'KHR' || x == 'khr') {
        return '<div class="text-center"><span class="label label-success">' + [x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-default">' + x + '</span></div>';
    }
}
function row_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'shortlist' || x == 'order' || x == 'permission' || x == 'pending' || x == 'deposited' || x == 'cleared' || x == 'booked' || x == 'requested' || x == 'applied' || x == 'reservation' || x == 'repairing') {
        return '<div class="text-center"><span class="row_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'employee' || x == 'accepted' || x == 'present' || x == 'verified' || x == 'expense' || x == 'enrolled' || x == 'completed' || x == 'public'|| x == 'paid' || x == 'sent' || x == 'received' || x == 'active' || x == 'pawn_rate' || x == 'pawn_received' || x == 'yes' || x == 'checked_in') {
        return '<div class="text-center"><span class="row_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'interview' || x == 'used' || x == 'reenrolled' || x == 'assigned' || x == 'follow_up' || x == 'partial' || x == 'transferring' || x == 'ordered' || x == 'approved' || x == 'packaging' || x == 'fixed' || x == 'disbursed' || x == 'done') {
        return '<div class="text-center"><span class="row_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'candidate' || x == 'voided' || x == 'absent' || x == 'checked'  || x == 'unpublic'|| x == 'spoiled' || x == 'difference' || x == 'due' || x == 'returned' || x == 'rejected' || x == 'inactive' || x == 'payoff' || x == 'pawn_sent' || x == 'closed' || x == 'no' || x == 'expired' || x == 'deleted' || x == 'take_away' || x == 'checked_out' || x == 'declined' || x == 'suspended' || x == 'cancelled' || x == 'not_done') {
        return '<div class="text-center"><span class="row_status label label-danger">' + lang[x] + '</span></div>';
    } else if (x == 'black_list') {
        return '<div class="text-center"><span class="row_status label label-other" style="background-color:#2e2e2d">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="row_status label label-default">' + x + '</span></div>';
    }
}
function actived_status(x) {
    if (x == 0) {
        return '<div class="text-center"><span class="label label-warning">Inactived</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-success">Actived</span></div>';
    }
}
function yesno_status(x) {
    if (x == 0) {
        return '<div class="text-center"><span class="label label-warning">No</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-success">Yes</span></div>';
    }
}
function pay_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'pending' || x == 'requested') {
        return '<div class="text-center"><span class="payment_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received' || x == 'approved') {
        return '<div class="text-center"><span class="payment_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="payment_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned'|| x == 'voided' || x == 'reject' || x == 'rejected') {
        return '<div class="text-center"><span class="payment_status label label-danger">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="payment_status label label-default">' + x + '</span></div>';
    }
}
function approved_status3(x) {
    if (x == null) {
        return '';
    } else if (x == 'pending' || x == 'requested') {
        return '<div class="text-center"><span class="approved_status3 label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received' || x == 'approved') {
        return '<div class="text-center"><span class="approved_status3 label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="approved_status3 label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'reject' || x == 'rejected') {
        return '<div class="text-center"><span class="approved_status3 label label-danger">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="approved_status3 label label-default">' + x + '</span></div>';
    }
}
function approved_status2(x) {
    if (x == null) {
        return '';
    } else if (x == 'pending' || x == 'requested') {
        return '<div class="text-center"><span class="approved_status2 label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received' || x == 'approved') {
        return '<div class="text-center"><span class="approved_status2 label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="approved_status2 label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'reject' || x == 'rejected') {
        return '<div class="text-center"><span class="approved_status2 label label-danger">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="approved_status2 label label-default">' + x + '</span></div>';
    }
}
function approved_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'pending' || x == 'requested') {
        return '<div class="text-center"><span class="approved_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received' || x == 'approved') {
        return '<div class="text-center"><span class="approved_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="approved_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'reject' || x == 'rejected') {
        return '<div class="text-center"><span class="approved_status label label-danger">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="approved_status label label-default">' + x + '</span></div>';
    }
}
function authorize_status(x) {
    if (x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' + lang['pending'] + '</span></div>';
    } else if (x == 'approved') {
        return '<div class="text-center"><span class="label label-success">' + lang['approved'] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-danger">' + lang['rejected'] + '</span></div>';
    }
}

function formatSA(x) {
    x = x.toString();
    var afterPoint = '';
    if (x.indexOf('.') > 0) afterPoint = x.substring(x.indexOf('.'), x.length);
    x = Math.floor(x);
    x = x.toString();
    var lastThree = x.substring(x.length - 3);
    var otherNumbers = x.substring(0, x.length - 3);
    if (otherNumbers != '') lastThree = ',' + lastThree;
    var res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree + afterPoint;

    return res;
}

function unitToBaseQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function baseToUnitQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function set_page_focus() {
    if (site.settings.set_focus == 1) {
        $('#add_item').attr('tabindex', an);
        $('[tabindex=' + (an - 1) + ']')
            .focus()
            .select();
    } else {
        $('#add_item').attr('tabindex', 1);
        $('#add_item').focus();
    }
    $('.rquantity').bind('keypress', function(e) {
        if (e.keyCode == 13) {
            $('#add_item').focus();
        }
    });
}

function calculateTax(tax, amt, met) {
    if (tax && tax_rates) {
        tax_val = 0;
        tax_rate = '';
        $.each(tax_rates, function() {
            if (this.id == tax) {
                tax = this;
                return false;
            }
        });
        if (tax.type == 1) {
            if (met == '0') {
                tax_val = formatDecimal((amt * parseFloat(tax.rate)) / (100 + parseFloat(tax.rate)), 4);
                tax_rate = formatDecimal(tax.rate) + '%';
            } else {
                tax_val = formatDecimal((amt * parseFloat(tax.rate)) / 100, 4);
                tax_rate = formatDecimal(tax.rate) + '%';
            }
        } else if (tax.type == 2) {
            tax_val = parseFloat(tax.rate);
            tax_rate = formatDecimal(tax.rate);
        }
        return [tax_val, tax_rate];
    }
    return false;
}

function calculateDiscount(val, amt) {
    if (val.indexOf('%') !== -1) {
        var pds = val.split('%');
        return formatDecimal(parseFloat((amt * parseFloat(pds[0])) / 100), 4);
    }
    return formatDecimal(val);
}

$(document).ready(function () {
    $('#choose-customer').click(function () {
        if ($('input[name=customer]').val()) {
            $('#myModal').modal({ remote: site.base_url + 'customers/choose/' + $('input[name=customer]').val() });
            $('#myModal').modal('show');
        }
    });
    $('#view-customer').click(function() {
        if ($('input[name=customer]').val()) {
            $('#myModal').modal({ remote: site.base_url + 'customers/view/' + $('input[name=customer]').val() });
            $('#myModal').modal('show');
        }
    });
    $('#view-supplier').click(function() {
        if ($('input[name=supplier]').val()) {
            $('#myModal').modal({ remote: site.base_url + 'suppliers/view/' + $('input[name=supplier]').val() });
            $('#myModal').modal('show');
        }
    });
    $('body').on('click', '.application_schedule_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'loans/application_agreement/' + $(this).closest('tr').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.loan_schedule_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'loans/payment_schedule/' + $(this).closest('tr').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.repair_link td:not(:first-child, :nth-last-child(4), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'repairs/modal_view/' + $(this).parent('.repair_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.fuel_sale_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_fuel_sale/' + $(this).parent('.fuel_sale_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.fuel_customer_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_fuel_customer/' + $(this).parent('.fuel_customer_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.check_link td:not(:first-child, :nth-last-child(4), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'repairs/modal_view_check/' + $(this).parent('.check_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.consignment_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/modal_view_consignment/' + $(this).parent('.consignment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.installment_link', function () {
        window.open(site.base_url + 'installments/view/' + $(this).closest('tr').attr('id'));
    });
    $('body').on('click', '.installment_schedule_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'installments/payment_schedule/' + $(this).parent('.installment_schedule_link').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'expenses/expense_note/' + $(this).parent('.expense_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.progress_note_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'clinic/view_progress_note/' + $(this).parent('.progress_note_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.operation_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'clinic/modal_view_operation/' + $(this).parent('.operation_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.medicaldose_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'clinic/modal_medication_dose/' + $(this).parent('.medicaldose_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.pathology_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'clinic/modal_view_pathology/' + $(this).parent('.pathology_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'customers/view/' +
                $(this)
                .parent('.customer_details_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.lead_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'leads/view/' +
                $(this)
                .parent('.lead_details_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.supplier_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'suppliers/view/' +
                $(this)
                .parent('.supplier_details_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.saleman_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'salemans/view/' +
                $(this)
                .parent('.saleman_details_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.promos_link td:not(:first-child, :nth-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'promos/modal_view/' +
                $(this)
                .parent('.promos_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'products/view/' + $(this).parent('.promos_link').attr('id');
    });
    $('body').on('click', '.product_link td:not(:first-child, :nth-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/modal_view/' +
                $(this)
                .parent('.product_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'products/view/' + $(this).parent('.product_link').attr('id');
    });
    $('body').on('click', '.asset_link td:not(:first-child, :nth-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/modal_view1/' +
                $(this)
                .parent('.asset_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'products/view/' + $(this).parent('.product_link').attr('id');
    });


    $('body').on('click', '.product_link2 td:first-child, .product_link2 td:nth-child(2)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/modal_view/' +
                $(this)
                .closest('tr')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/modal_view/' +
                $(this)
                .parent('.purchase_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.debit_note_link td:not(:first-child,:nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'account/modal_view_debit_note/' +
                $(this)
                .parent('.debit_note_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_request_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases_request/view/' +
                $(this)
                .parent('.purchase_request_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
     $('body').on('click', '.purchase_order_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases_order/view/' +
                $(this)
                .parent('.purchase_order_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });


    $('body').on('click', '.stock_received_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/modal_view_stock_received/' +
                $(this)
                .parent('.stock_received_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.reward_stock_received_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/modal_view_reward_stock_received/' +
                $(this)
                .parent('.reward_stock_received_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.stock_received_detail_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/view_stock_received/' +
                $(this)
                .parent('.stock_received_detail_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_link2 td', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/modal_view/' +
                $(this)
                .closest('tr')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_request_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'expenses/expense_request_note/' + $(this).parent('.expense_request_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'expenses/expense_note/' + $(this).parent('.expense_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.transfer_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'transfers/view/' +
                $(this)
                .parent('.transfer_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $("body").on("click", ".multi_transfer_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)", function() {
        $("#myModal").modal({
            remote: site.base_url +
                "reports/view_multi_transfer/" +
                $(this).parent(".multi_transfer_link").attr("id"),
        });
        $("#myModal").modal("show");
    });
    $('body').on('click', '.transfer_link2', function() {
        $('#myModal').modal({ remote: site.base_url + 'transfers/view/' + $(this).attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.oreturn_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'returns/view/' +
                $(this)
                .parent('.oreturn_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.invoice_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/modal_view/' +
                $(this)
                .parent('.invoice_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.return_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_return/' + $(this).parent('.return_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.return_link').attr('id');
    });
    $('body').on('click', '.receive_link2 td:not(:first-child,:last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/receive_note/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.declare_invoice_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/modal_view/' +$(this).parent('.declare_invoice_link').attr('id') +
                '/0/1',
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.invoice_rental_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/modal_view/' +
                $(this)
                .parent('.invoice_rental_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.reward_exchange_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/modal_view_reward_exchange/' +
                $(this)
                .parent('.reward_exchange_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.booking_ticket_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url +
                'tickets/modal_view/' +
                $(this)
                .parent('.booking_ticket_link')
                    .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.invoice_order_link td:not(:first-child, :nth-child(7), :nth-last-child(11), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales_order/modal_view/' +
                $(this)
                .parent('.invoice_order_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sale_order_link td:not(:first-child, :nth-child(7), :nth-last-child(11), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales_order/modal_view/' +
                $(this)
                .parent('.sale_order_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.invoice_link2 td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/modal_view/' +
                $(this)
                .closest('tr')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.receipt_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'pos/view/' +
                $(this)
                .parent('.receipt_link')
                .attr('id') +
                '/1',
        });
    });
    // $('body').on('click', '.return_link td', function() {
    //     // window.location.href = site.base_url + 'sales/view_return/' + $(this).parent('.return_link').attr('id');
    //     $('#myModal').modal({
    //         remote: site.base_url +
    //             'sales/view_return/' +
    //             $(this)
    //             .parent('.return_link')
    //             .attr('id'),
    //     });
    //     $('#myModal').modal('show');
    // });
    $('body').on('click', '.return_purchase_link td', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/view_return/' +
                $(this)
                .parent('.return_purchase_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });



    $('body').on('click', '.payment_link td', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/payment_note/' +
                $(this)
                .parent('.payment_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link2 td', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/payment_note/' +
                $(this)
                .parent('.payment_link2')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_link2 td:not(:last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'purchases/expense_note/' +
                $(this)
                .closest('tr')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.quote_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'quotes/modal_view/' +
                $(this)
                .parent('.quote_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'quotes/view/' + $(this).parent('.quote_link').attr('id');
    });
    $('body').on('click', '.quote_link2', function() {
        $('#myModal').modal({ remote: site.base_url + 'quotes/modal_view/' + $(this).attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.delivery_link td:not(:first-child, :nth-last-child(2), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'sales/delivery_note1/' +
                $(this)
                .parent('.delivery_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.credit_note_link td:not(:first-child, :nth-last-child(2), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'account/view_credit_note/' +
                $(this)
                .parent('.credit_note_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.prescription_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'clinic/view_prescription/' +
                $(this)
                .parent('.prescription_link')
                .attr('id') +
                '/1',
        });
    });
    $('body').on('click', '.customer_link td:not(:first-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'customers/edit/' +
                $(this)
                .parent('.customer_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_stock_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({ remote: site.base_url + 'pos/view_customer_stock/' + $(this).parent('.customer_stock_link').attr('id') + '/1' });
    });
    $('body').on('click', '.supplier_link td:not(:first-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'suppliers/edit/' +
                $(this)
                .parent('.supplier_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'products/view_adjustment/' +
                $(this)
                .parent('.adjustment_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.enter_journal_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'account/view_enterjournal/' + $(this).parent('.enter_journal_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.event_view_link td:not(:first-child,:nth-child(2), :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'calendar/modal_view/' +
                $(this)
                .parent('.event_view_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.view_schedule_link td:not(:first-child,:nth-child(2), :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'calendar/view_schedule/' +
                $(this)
                .parent('.view_schedule_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });
    $('body').on('click', '.view_ticket_link td:not(:first-child, :nth-child(10), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({
            remote: site.base_url +
                'calendar/view_ticket/' +
                $(this)
                .parent('.view_ticket_link')
                .attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id');
    });

    $('body').on('click', '.audit_trail_link td:not(:nth-child(4), :nth-child(5), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'reports/audit_trail_view/' + $(this).parent('.audit_trail_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.adjustment_link2', function() {
        $('#myModal').modal({ remote: site.base_url + 'products/view_adjustment/' + $(this).attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.journal_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'account/view_enterjournal/' + $(this).parent('.journal_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.blocking_link2', function() {
        $('#myModal').modal({ remote: site.base_url + 'property/view_blocking/' + $(this).attr('id') });
        $('#myModal').modal('show');
    });

    $('body').on('click', '.cost_adjustment_link2', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_cost_adjustment/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.using_stock_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_using_stock/' + $(this).parent('.using_stock_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/modal_view/' + $(this).parent('.pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.pur_pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/purchase_modal_view/' + $(this).parent('.pur_pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.return_pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/return_modal_view/' + $(this).parent('.return_pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.pawn_payment td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/modal_payment/' + $(this).parent('.pawn_payment').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.accounting_link', function() {
        $('#myModal').modal({remote: site.base_url + 'account/modal_view/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.take_leave_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/view_take_leave/' + $(this).parent('.take_leave_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.day_off_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/modal_view_day_off/' + $(this).parent('.day_off_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.montly_time_card_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/monthly_time_card/' + $(this).parent('.montly_time_card_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.daily_time_card_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/daily_time_card/' + $(this).parent('.daily_time_card_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.convert_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'workorder/view_convert/' + $(this).parent('.convert_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.department_time_card_link', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/department_time_card/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.employee_detail_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/employee_details/' + $(this).parent('.employee_detail_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.travel_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/modal_view_travel/' + $(this).parent('.travel_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.id_card_link td:not(:first-child, :last-child)', function() {
        window.open(site.base_url + 'hr/view_employee_id_card/' + $(this).parent('.id_card_link').attr('id')); 
    });
    
    $('body').on('click', '.employee_leave_link', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/view_employee_leave/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.kpi_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/modal_view_kpi/' + $(this).parent('.kpi_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_review_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/modal_view_salary_review/' + $(this).parent('.salary_review_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.cash_advance_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_cash_advance/' + $(this).parent('.cash_advance_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.benefit_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_benefit/' + $(this).parent('.benefit_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary/' + $(this).parent('.salary_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_teacher_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary_teacher/' + $(this).parent('.salary_teacher_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.pre_salary_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_pre_salary/' + $(this).parent('.pre_salary_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_13_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary_13/' + $(this).parent('.salary_13_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_employee_13_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary_employee_13/' + $(this).parent('.salary_employee_13_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payslip_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_payslip/' + $(this).parent('.payslip_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payroll_payment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_payment/' + $(this).parent('.payroll_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_delivery_link td:not(:first-child,:nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_delivery/' + $(this).parent('.con_delivery_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_delivery_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_delivery/' + $(this).parent('.con_delivery_link2').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_sale_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_sale/' + $(this).parent('.con_sale_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel/' + $(this).parent('.con_fuel_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel/' + $(this).parent('.con_fuel_link2').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_moving_waiting_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_moving_waiting/' + $(this).parent('.con_moving_waiting_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_mission_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_mission/' + $(this).parent('.con_mission_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_absent_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_absent/' + $(this).parent('.con_absent_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_expense_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel_expense/' + $(this).parent('.con_fuel_expense_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_commission_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_commission/' + $(this).parent('.con_commission_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_adjustment/' + $(this).parent('.con_adjustment_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_error_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_error/' + $(this).parent('.con_error_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_error_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_error/' + $(this).parent('.con_error_link2').attr('id')});
        $('#myModal').modal('show');
    });
    //--------school-------
    $('body').on('click', '.student_admission_link td:not(:first-child, :nth-child(2), :nth-last-child(2), :nth-last-child(3), :nth-last-child(4), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_student_admisssion/' + $(this).parent('.student_admission_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_link td:not(:first-child, :nth-child(2), :nth-last-child(2), :nth-last-child(3), :nth-last-child(4), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_student/' + $(this).parent('.student_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.study_info_link td:not(:first-child, :nth-last-child(2),  :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_study/' + $(this).parent('.study_info_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sh_waiting_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_waiting/' + $(this).parent('.sh_waiting_link').attr('id')});
        $('#myModal').modal('show');
    }); 
    $('body').on('click', '.student_ask_permission td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/student_ask_permission/' + $(this).parent('.student_ask_permission').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_ask_do_sarana td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_do_sarana/' + $(this).parent('.student_ask_do_sarana').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_ask_sarana_out td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_sarana_out/' + $(this).parent('.student_ask_sarana_out').attr('id') });
        $('#myModal').modal('show');
    });
     $('body').on('click', '.student_ask_time td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_time/' + $(this).parent('.student_ask_time').attr('id') });
        $('#myModal').modal('show');
    });

     $('body').on('click', '.student_delay_study td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_delay_study/' + $(this).parent('.student_delay_study').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_change_major td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_change_major/' + $(this).parent('.student_change_major').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_request_cetificate td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_request_cetificate/' + $(this).parent('.student_request_cetificate').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_request_cetificate_fy td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_request_cetificate_fy/' + $(this).parent('.student_request_cetificate_fy').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_repuest_for_cetificate td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_repuest_for_cetificate/' + $(this).parent('.student_repuest_for_cetificate').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_request_transcript td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_request_transcript/' + $(this).parent('.student_request_transcript').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_change_time td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_change_time/' + $(this).parent('.student_change_time').attr('id') });
        $('#myModal').modal('show');
    });
     $('body').on('click', '.student_contract_student td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_contract_student/' + $(this).parent('.student_contract_student').attr('id') });
        $('#myModal').modal('show');
    });
       $('body').on('click', '.student_ask_exam td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_exam/' + $(this).parent('.student_ask_exam').attr('id') });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.student_ask_in_class td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_in_class/' + $(this).parent('.student_ask_in_class').attr('id') });
    $('#myModal').modal('show');
    });
     $('body').on('click', '.student_mistake td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_mistake/' + $(this).parent('.student_mistake').attr('id') });
    $('#myModal').modal('show');
    });
      $('body').on('click', '.student_request_ask_stay_at_pcu td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_request_ask_stay_at_pcu/' + $(this).parent('.student_request_ask_stay_at_pcu').attr('id') });
    $('#myModal').modal('show');
    });
        $('body').on('click', '.student_ask_re_exam td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_re_exam/' + $(this).parent('.student_ask_re_exam').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.student_ask_stop td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_stop/' + $(this).parent('.student_ask_stop').attr('id') });
    $('#myModal').modal('show');
    });
      $('body').on('click', '.student_ask_stop_study td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_stop_study/' + $(this).parent('.student_ask_stop_study').attr('id') });
    $('#myModal').modal('show');
    });
        $('body').on('click', '.student_request td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_request/' + $(this).parent('.student_request').attr('id') });
    $('#myModal').modal('show');
    });
          $('body').on('click', '.student_invite td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_invite/' + $(this).parent('.student_invite').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.student_ask_add_time td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_ask_add_time/' + $(this).parent('.student_ask_add_time').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.student_lect_ask_permission td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_lect_ask_permission/' + $(this).parent('.student_lect_ask_permission').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.student_bangkanday_examination td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_bangkanday_examination/' + $(this).parent('.student_bangkanday_examination').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.student_recomendation td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
    $('#myModal').modal({ remote: site.base_url + 'schools/student_recomendation/' + $(this).parent('.student_recomendation').attr('id') });
    $('#myModal').modal('show');
    });
    $('body').on('click', '.sh_testing_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_testing/' + $(this).parent('.sh_testing_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sh_ticket_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_ticket/' + $(this).parent('.sh_ticket_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sh_sale_link td:not(:first-child, :nth-last-child(2),  :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_sale/' + $(this).parent('.sh_sale_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.tax_link td:not(:first-child,  :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'taxs/modal_view/' + $(this).parent('.tax_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.sh_student_status_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_student_status/' + $(this).parent('.sh_student_status_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sh_student_ask_scholarship td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/student_ask_scholarship/' + $(this).parent('.sh_student_ask_scholarship').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.view_maintenance_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_maintenance_schedule/' + $(this).parent('.view_maintenance_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $(document).on('click', '.weight_status', function(e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        $('#myModal').modal({remote: site.base_url + 'concretes/update_weight/' + id});
        $('#myModal').modal('show');
        return false;
    });


    $('#clearLS').click(function(event) {
        bootbox.confirm(lang.r_u_sure, function(result) {
            if (result == true) {
                localStorage.clear();
                location.reload();
            }
        });
        return false;
    });
    $('body').on('click', '.booking_link2', function() {
        $('#myModal').modal({ remote: site.base_url + 'property/view_booking/' + $(this).attr('id') });
        $('#myModal').modal('show');
    });
    $('#clearLS').click(function(event) {
        bootbox.confirm(lang.r_u_sure, function(result) {
            if (result == true) {
                localStorage.clear();
                location.reload();
            }
        });
        return false;
    });
    $(document).on('click', '[data-toggle="ajax"]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $.get(href, function(data) {
            $('#myModal')
                .html(data)
                .modal();
        });
    });
    $('.sortable_rows')
        .sortable({
            items: '> tr',
            appendTo: 'parent',
            helper: 'clone',
            placeholder: 'ui-sort-placeholder',
            axis: 'x',
            update: function(event, ui) {
                var item_id = $(ui.item).attr('data-item-id');
                console.log(ui.item.index());
            },
        })
        .disableSelection();
});

function fixAddItemnTotals() {
    var ai = $('#sticker');
    var aiTop = ai.position().top + 250;
    var bt = $('#bottom-total');
    $(window).scroll(function() {
        var windowpos = $(window).scrollTop();
        if (windowpos >= aiTop) {
            ai.addClass('stick')
                .css('width', ai.parent('form').width())
                .css('zIndex', 2);
            if ($.cookie('bpas_theme_fixed') == 'yes') {
                ai.css('top', '40px');
            } else {
                ai.css('top', 0);
            }
            $('#add_item').removeClass('input-lg');
            $('.addIcon').removeClass('fa-2x');
        } else {
            ai.removeClass('stick')
                .css('width', bt.parent('form').width())
                .css('zIndex', 2);
            if ($.cookie('bpas_theme_fixed') == 'yes') {
                ai.css('top', 0);
            }
            $('#add_item').addClass('input-lg');
            $('.addIcon').addClass('fa-2x');
        }
        if (windowpos <= $(document).height() - $(window).height() - 120) {
            bt.css('position', 'fixed')
                .css('bottom', 0)
                .css('width', bt.parent('form').width())
                .css('zIndex', 2);
        } else {
            bt.css('position', 'static')
                .css('width', ai.parent('form').width())
                .css('zIndex', 2);
        }
    });
}

function ItemnTotals() {
    fixAddItemnTotals();
    $(window).bind('resize', fixAddItemnTotals);
}

function getSlug(title, type) {
    var slug_url = site.base_url + 'welcome/slug';
    $.get(slug_url, { title: title, type: type }, function(slug) {
        $('#slug')
            .val(slug)
            .change();
    });
}

function openImg(img) {
    var imgwindow = window.open('', 'bpas_pos_img');
    imgwindow.document.write('<html><head><title>Screenshot</title>');
    imgwindow.document.write('<link rel="stylesheet" href="' + site.assets + 'styles/helpers/bootstrap.min.css" type="text/css" />');
    imgwindow.document.write('</head><body style="display:flex;align-items:center;justify-content:center;">');
    imgwindow.document.write('<img src="' + img + '" class="img-thumbnail"/>');
    imgwindow.document.write('</body></html>');
    return true;
}

if (site.settings.auto_detect_barcode == 1) {
    $(document).ready(function() {
        var pressed = false;
        var chars = [];
        $(window).keypress(function(e) {
            chars.push(String.fromCharCode(e.which));
            if (pressed == false) {
                setTimeout(function() {
                    if (chars.length >= 8) {
                        var barcode = chars.join('');
                        $('#add_item')
                            .focus()
                            .autocomplete('search', barcode);
                    }
                    chars = [];
                    pressed = false;
                }, 200);
            }
            pressed = true;
        });
    });
}
$('.sortable_table tbody').sortable({
    containerSelector: 'tr',
});
$(window).bind('resize', widthFunctions);
$(window).load(widthFunctions);

$("#suggest_employee").autocomplete({
    source: site.base_url+'hr/suggestions',
    select: function (event, ui) {
        $('#suggest_employee_id').val(ui.item.id);
    },
    minLength: 1,
    autoFocus: false,
    delay: 250,
    response: function (event, ui) {
        if(ui.content == null){
            return false;
        }else if (ui.content.length == 1 && ui.content[0].id != 0) {
            ui.item = ui.content[0];
            $(this).val(ui.item.label);
            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
            $(this).autocomplete('close');
            $(this).removeClass('ui-autocomplete-loading');
        }
    },
});
$(document).on('blur', '#suggest_employee', function(e) {
    if (! $(this).val()) {
        $('#suggest_employee_id').val('');
    }
});
function secToHour(x){
    if(x > 0){
        var hour = x / 3600;
        return hour;
    }
    return '';
}
function secTotime(x) {
    var format = '%02d:%02d:%02d';
    if (x < 1) {
        return '';
    }
    var hours = parseInt(x / 3600);
    var minutes = parseInt(x / 60 % 60);
    var seconds = parseInt(x % 60);
    return ("0" + hours).slice(-2) + ':' + ("0" + minutes).slice(-2) + ':'+ ("0" + seconds).slice(-2);
}

function d_secTotime(x) {
    var format = '%02d:%02d:%02d';
    if (x < 1) {
        return '';
    }
    var hours = parseInt(x / 3600);
    var minutes = parseInt(x / 60 % 60);
    var seconds = parseInt(x % 60);
    return '<div class="text-center">'+ ("0" + hours).slice(-2) + ':' + ("0" + minutes).slice(-2) + ':'+ ("0" + seconds).slice(-2) + '</div>';
}
function text_right(x){
    return '<div class="text-right">'+x+'</div>';
}
function text_left(x){
    return '<div class="text-left">'+x+'</div>';
}
function text_center(x){
    return '<div class="text-center">'+x+'</div>';
}
function colorBox(x){
    return (x != null) ? '<div style="background-color:'+x+'" class="text-center">&nbsp;</div>' : '';
}
function truefalse_status(x) {
    if (x == 0) {
        return '<div class="text-center"><span class="payment_status label label-warning">pending</span></div>';
    } else{
        return '<div class="text-center"><span class="payment_status label label-success">completed</span></div>';
    }
}
function gender_status(x){
    return '<div class="text-center">'+lang[x]+'</div>';
}
function formatMoneyKH(x, symbol) {
    if(!symbol) { symbol = ""; }
    if(site.settings.sac == 1) {
        return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
            ''+formatSA(parseFloat(x).toFixed(site.settings.decimals)) +
            (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, 0, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    return (site.settings.display_symbol == 1 ? '៛' : '') +
        fmoney +
        (site.settings.display_symbol == 2 ? '៛' : '');
}
function currencyFormatKH(x) {
    return '<div class="text-right">'+formatMoneyKH(x != null ? x : 0)+'</div>';
}

$(document).on('cut copy paste', '.input_decimal, .input_decimal_percentage', function(e) {
    e.preventDefault();
});
$(document).on('keypress', '.input_decimal', function(e) {
    if ((e.which != 46 || $(this).val().indexOf('.') != -1) && (e.which < 48 || e.which > 57)) {
        e.preventDefault();
    }
});
$(document).on('keypress', '.input_decimal_percentage', function(e) {
    if ((e.which != 46 || $(this).val().indexOf('.') != -1) && (e.which < 48 || e.which > 57) && (e.which != 37 || $(this).val().indexOf('%') != -1)) {
        e.preventDefault();
    }
});
$(document).on('input', '.input_decimal_percentage', function() {
    if ($(this).val().indexOf('%') != -1) {
        $(this).val(function(i, v) {
            var value = v.replace('%', '');
            if (value == '') {
                value = 0;
            }
            if (value > 100) {
                value = 100;
            }
            if (value < 0) {
                value = 0;
            }
            return (parseFloat(value) + '%');  
        });
    }
});

/* ---prevent back----
function preventBack() { window.history.forward(); }
setTimeout("preventBack()", 0);
window.onunload = function () { null };
*/