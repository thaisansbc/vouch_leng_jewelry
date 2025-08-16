$(document).ready(function () {
    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    if (localStorage.getItem('bomitems')) {
        loadItems();
    }
    if (localStorage.getItem('bomfinitems')) {
        loadFinishItems();
    }
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('bomitems')) {
                    localStorage.removeItem('bomitems');
                }
                if (localStorage.getItem('bomfinitems')) {
                    localStorage.removeItem('bomfinitems');
                }
                if (localStorage.getItem('bomname')) {
                    localStorage.removeItem('bomname');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    $('#bomname').change(function (e) {
        localStorage.setItem('bomname', $(this).val());
    });
    if (bomname = localStorage.getItem('bomname')) {
        $('#bomname').val(bomname);
    }
    
    $('#bomnote').redactor('destroy');
    $('#bomnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('bomnote', v);
        }
    });
    if (bomnote = localStorage.getItem('bomnote')) {
        $('#bomnote').redactor('set', bomnote);
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

    $(document).on('click', '.bomdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete bomitems[item_id];
        row.remove();
        if(bomitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('bomitems', JSON.stringify(bomitems));
            loadItems();
            return;
        }
    });

    $(document).on("focus", '.unit_qty', function () {
        old_unit_qty = $(this).val();
    }).on("change", '.unit_qty', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        item_id = row.attr('data-item-id');
        var new_unit_qty = parseFloat($(this).val());
        var unit = bomitems[item_id].row.unit;
        var quantity = bomitems[item_id].row.quantity;
        if(bomitems[item_id].units !== false) {
            $.each(bomitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(new_unit_qty, this));
                }
            });
        }
        bomitems[item_id].row.quantity = quantity,
        bomitems[item_id].row.unit_qty = new_unit_qty;
        localStorage.setItem('bomitems', JSON.stringify(bomitems));
        loadItems();
    });
    
    $(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');         
        var unit = $(this).val();
        var quantity = bomitems[item_id].row.quantity;
        var unit_qty = bomitems[item_id].row.unit_qty;
        if(bomitems[item_id].units !== false) {
            $.each(bomitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(unit_qty, this));
                }
            });
        }
        bomitems[item_id].row.quantity = quantity,
        bomitems[item_id].row.unit = unit,
        localStorage.setItem('bomitems', JSON.stringify(bomitems));
        loadItems();
    });
    
    $(document).on('click', '.bomfdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete bomfinitems[item_id];
        row.remove();
        if(bomfinitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('bomfinitems', JSON.stringify(bomfinitems));
            loadFinishItems();
            return;
        }
    });
    
    $(document).on("focus", '.funit_qty', function () {
        old_unit_qty = $(this).val();
    }).on("change", '.funit_qty', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        item_id = row.attr('data-item-id');
        var new_unit_qty = parseFloat($(this).val());
        var unit = bomfinitems[item_id].row.unit;
        var quantity = bomfinitems[item_id].row.quantity;
        if(bomfinitems[item_id].units !== false) {
            $.each(bomfinitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(new_unit_qty, this));
                }
            });
        }
        bomfinitems[item_id].row.quantity = quantity,
        bomfinitems[item_id].row.unit_qty = new_unit_qty;
        localStorage.setItem('bomfinitems', JSON.stringify(bomfinitems));
        loadFinishItems();
    });
    
    $(document).on('change', '.funit', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');         
        var unit = $(this).val();
        var quantity = bomfinitems[item_id].row.quantity;
        var unit_qty = bomfinitems[item_id].row.unit_qty;
        if(bomfinitems[item_id].units !== false) {
            $.each(bomfinitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(unit_qty, this));
                }
            });
        }
        bomfinitems[item_id].row.quantity = quantity,
        bomfinitems[item_id].row.unit = unit,
        localStorage.setItem('bomfinitems', JSON.stringify(bomfinitems));
        loadFinishItems();
    });
});


function loadItems() {
    if (localStorage.getItem('bomitems')) {
        count = 1;
        an = 1;
        var total_qty = 0;
        $("#bomRaw tbody").empty();
        bomitems = JSON.parse(localStorage.getItem('bomitems'));
        $.each(bomitems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
            tr_html += '<td><input type="hidden" class="quantity" value="' + (item.row.quantity) + '" name="quantity[]"/><input class="form-control text-center unit_qty"  name="unit_qty[]" type="text" value="' + (item.row.unit_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
            var opt_unit = $("<select name=\"unit[]\" class=\"form-control select sunit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
                    if (item.row.unit == this.id){
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt_unit);
                    }else{
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt_unit);
                    }
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt_unit);
                opt_unit = opt_unit.hide();
            }
            tr_html += '<td>'+(opt_unit.get(0).outerHTML)+'</td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip bomdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#bomRaw");
            count ++;
            an++;
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

function loadFinishItems() {
    if (localStorage.getItem('bomfinitems')) {
        count_finish = 1;
        an_finish = 1;
        var total_qty = 0;
        $("#bomFinished tbody").empty();
        bomfinitems = JSON.parse(localStorage.getItem('bomfinitems'));
        $.each(bomfinitems, function () {
            var item = this;
            var item_id = item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="fproduct_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
            tr_html += '<td><input type="hidden" class="fquantity" value="' + (item.row.quantity) + '" name="fquantity[]"/><input class="form-control text-center funit_qty"  name="funit_qty[]" type="text" value="' + (item.row.unit_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
            var opt_unit = $("<select name=\"funit[]\" class=\"form-control select funit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
                    if (item.row.unit == this.id){
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt_unit);
                    }else{
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt_unit);
                    }
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt_unit);
                opt_unit = opt_unit.hide();
            }
            tr_html += '<td>'+(opt_unit.get(0).outerHTML)+'</td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip bomfdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#bomFinished");
            count_finish ++;
            an_finish++;
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an_finish > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

function add_raw_material_item(item) {
    if (count == 1) {
        bomitems = {};
    }
    if (item == null) return;
    var item_id =  item.id;
    if (bomitems[item_id]) {
        var new_qty = parseFloat(bomitems[item_id].row.quantity) + 1;
        bomitems[item_id].row.base_quantity = new_qty;
        if(bomitems[item_id].row.unit != bomitems[item_id].row.base_unit) {
            $.each(bomitems[item_id].units, function(){
                if (this.id == bomitems[item_id].row.unit) {
                    bomitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        bomitems[item_id].row.quantity = new_qty;
    } else {
        bomitems[item_id] = item;
    }
    bomitems[item_id].order = new Date().getTime();
    localStorage.setItem('bomitems', JSON.stringify(bomitems));
    loadItems();
    return true;
}

function add_finised_good_item(item) {
    if (count_finish == 1) {
        bomfinitems = {};
    }
    if (item == null) return;
    var item_id =  item.id;
    if (bomfinitems[item_id]) {
        var new_qty = parseFloat(bomfinitems[item_id].row.quantity) + 1;
        bomfinitems[item_id].row.base_quantity = new_qty;
        if(bomfinitems[item_id].row.unit != bomfinitems[item_id].row.base_unit) {
            $.each(bomfinitems[item_id].units, function(){
                if (this.id == bomfinitems[item_id].row.unit) {
                    bomfinitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        bomfinitems[item_id].row.quantity = new_qty;
    } else {
        bomfinitems[item_id] = item;
    }
    bomfinitems[item_id].order = new Date().getTime();
    localStorage.setItem('bomfinitems', JSON.stringify(bomfinitems));
    loadFinishItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1 || count_finish > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}