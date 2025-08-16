$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
// Order level and discoutn localStorage
    if (rpdiscount = localStorage.getItem('rpdiscount')) {
        $('#rpdiscount').val(rpdiscount);
    }
    $('#rptax2').change(function (e) {
        localStorage.setItem('rptax2', $(this).val());
        $('#rptax2').val($(this).val());
    });
    if (rptax2 = localStorage.getItem('rptax2')) {
        $('#rptax2').select2("val", rptax2);
    }
    $('#rpstatus').change(function (e) {
        localStorage.setItem('rpstatus', $(this).val());
    });
    if (rpstatus = localStorage.getItem('rpstatus')) {
        $('#rpstatus').select2("val", rpstatus);
    }
    // If there is any item in localStorage
    if (localStorage.getItem('rpitems')) {
        loadItems();
    }
    // clear localStorage and reload
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('rpitems')) {
                        localStorage.removeItem('rpitems');
                    }
                    if (localStorage.getItem('rpdiscount')) {
                        localStorage.removeItem('rpdiscount');
                    }
                    if (localStorage.getItem('rptax2')) {
                        localStorage.removeItem('rptax2');
                    }
                    if (localStorage.getItem('rpref')) {
                        localStorage.removeItem('rpref');
                    }
                    if (localStorage.getItem('rpwarehouse')) {
                        localStorage.removeItem('rpwarehouse');
                    }
                    if (localStorage.getItem('rpnote')) {
                        localStorage.removeItem('rpnote');
                    }
                    if (localStorage.getItem('rpinnote')) {
                        localStorage.removeItem('rpinnote');
                    }
                    if (localStorage.getItem('rpcustomer')) {
                        localStorage.removeItem('rpcustomer');
                    }
                    if (localStorage.getItem('rpcurrency')) {
                        localStorage.removeItem('rpcurrency');
                    }
                    if (localStorage.getItem('rpdate')) {
                        localStorage.removeItem('rpdate');
                    }
                    if (localStorage.getItem('rpstatus')) {
                        localStorage.removeItem('rpstatus');
                    }
					if (localStorage.getItem('rpphone')) {
                        localStorage.removeItem('rpphone');
                    }
					if (localStorage.getItem('rpbrand')) {
                        localStorage.removeItem('rpbrand');
                    }
					if (localStorage.getItem('rpmodel')) {
                        localStorage.removeItem('rpmodel');
                    }
					if (localStorage.getItem('rpmachine_type')) {
                        localStorage.removeItem('rpmachine_type');
                    }
					if (localStorage.getItem('rpimei_number')) {
                        localStorage.removeItem('rpimei_number');
                    }
					if (localStorage.getItem('rptechnician')) {
                        localStorage.removeItem('rptechnician');
                    }
					if (localStorage.getItem('rpreceive_date')) {
                        localStorage.removeItem('rpreceive_date');
                    }
					if (localStorage.getItem('rpstaff_note')) {
                        localStorage.removeItem('rpstaff_note');
                    }
                    if (localStorage.getItem('rpbiller')) {
                        localStorage.removeItem('rpbiller');
                    }
                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

	// save and load the fields in and/or from localStorage

    $('#rpref').change(function (e) {
        localStorage.setItem('rpref', $(this).val());
    });
    if (rpref = localStorage.getItem('rpref')) {
        $('#rpref').val(rpref);
    }
    $('#rpwarehouse').change(function (e) {
        localStorage.setItem('rpwarehouse', $(this).val());
    });
    if (rpwarehouse = localStorage.getItem('rpwarehouse')) {
        $('#rpwarehouse').select2("val", rpwarehouse);
    }

    $('#rpnote').redactor('destroy');
    $('#rpnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('rpnote', v);
        }
    });
    if (rpnote = localStorage.getItem('rpnote')) {
        $('#rpnote').redactor('set', rpnote);
    }
	
	$('#rpstaff_note').redactor('destroy');
    $('#rpstaff_note').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('rpstaff_note', v);
        }
    });
    if (rpstaff_note = localStorage.getItem('rpstaff_note')) {
        $('#rpstaff_note').redactor('set', rpstaff_note);
    }
	
    var $customer = $('#rpcustomer');
    $customer.change(function (e) {
        localStorage.setItem('rpcustomer', $(this).val());
    });
    if (rpcustomer = localStorage.getItem('rpcustomer')) {
        $customer.val(rpcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsCustomer();
    }

    // Change Machine Type
    $(document).on('change', '#rpmachine_type', function () {
        var machine_type_id = $(this).val();
        $.ajax({
            url : site.base_url + 'repairs/get_machine_type',
            type : 'get',
            dataType : 'json',
            data : { machine_type_id : machine_type_id},
            success : function(data){
                if (localStorage.getItem('rpitems')) {
                    var mts = JSON.parse(JSON.stringify(data));
                    if(mts){
                        var machine_types = [];
                        $.each(mts,function(i,e){
                            machine_types[e.product_id] = parseFloat(e.price);
                        });
                        $("#rpTable tbody tr").each(function(i,e){
                            var row = $(this).closest('tr');
                            var item_id = row.attr('data-item-id'); item = rpitems[item_id];
                            var new_price = machine_types[item.row.id]?machine_types[item.row.id]:item.row.real_unit_price;
                            rpitems[item_id].row.unit_price = parseFloat(new_price);
                        });
                    }
                    localStorage.setItem('rpitems', JSON.stringify(rpitems));
                    loadItems();
                }
            }
        });
    });

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        /*if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }*/
    });

// Order tax calculation
if (site.settings.tax2 != 0) {
    $('#rptax2').change(function () {
        localStorage.setItem('rptax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calculation
var old_rpdiscount;
$('#rpdiscount').focus(function () {
    old_rpdiscount = $(this).val();
}).change(function () {
    var new_discount = $(this).val() ? $(this).val() : '0';
    if (is_valid_discount(new_discount)) {
        localStorage.removeItem('rpdiscount');
        localStorage.setItem('rpdiscount', new_discount);
        loadItems();
        return;
    } else {
        $(this).val(old_rpdiscount);
        bootbox.alert(lang.unexpected_value);
        return;
    }

});

/* ----------------------
 * Delete Row Method
 * ---------------------- */
$(document).on('click', '.rpdel', function () {
    var row = $(this).closest('tr');
    var item_id = row.attr('data-item-id');
    delete rpitems[item_id];
    row.remove();
    if(rpitems.hasOwnProperty(item_id)) { } else {
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        loadItems();
        return;
    }
});

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = rpitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimalRaw(row.children().children('.ruprice').val()),
        discount = row.children().children('.rdiscount').val();
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price)+parseFloat(this.price);
                }
            });
        }
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (rpitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }

        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

        if(site.settings.product_formulation != 0){
            if(item.product_formulations){
                opt_formulation = $("<select id=\"opt_formulation\" name=\"opt_formulation\" class=\"form-control select\" multiple/>");
                var formu = row.children().children('.r_product_formulation').val();
                var myarray = formu.split(",");
                $.each(item.product_formulations, function () {
                    if(jQuery.inArray(this.for_product_id, myarray) !== -1){
                        $("<option />", { value: this.for_product_id, text: this.name, selected:true}).appendTo(opt_formulation);
                    }else{
                        $("<option />", { value: this.for_product_id, text: this.name}).appendTo(opt_formulation);
                    }
                });
                
            }else{
                opt_formulation = '<p style="margin: 12px 0 0 0;">n/a</p>';
            }
            $('#pformulation-div').html(opt_formulation);
        }
		
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price-item_discount));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');

    });
	
	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = rpitems[item_id];
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#iordered').val(item.row.ordered);
        $('#iordered').select2('val', item.row.ordered);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });

    $(document).on('click', '#editComment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        rpitems[item_id].row.order = parseFloat($('#iorders').val()),
        rpitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        $('#cmModal').modal('hide');
        loadItems();
        return;
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = rpitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = rpitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(),  aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
		
        if(unit != rpitems[item_id].row.base_unit) {
            $.each(item.units, function(){
                if (this.id == unit) {
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						$('#pprice').val((this.unit_price - (this.unit_price * ppercent)) + aprice *  (unitToBaseQty(1, this))).change();
					}else{
						$('#pprice').val((item.row.real_unit_price+aprice) * unitToBaseQty(1, this)).change();
					}
				}
            });
        } else {
            $('#pprice').val(formatDecimalRaw(item.row.real_unit_price) + aprice).change();
        }
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != rpitems[item_id].row.base_unit) {
            $.each(rpitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }

        rpitems[item_id].row.fup = 1,
        rpitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        rpitems[item_id].row.base_quantity = parseFloat(base_quantity),
		rpitems[item_id].row.unit_price = price,
        rpitems[item_id].row.unit = unit,
        rpitems[item_id].row.tax_rate = new_pr_tax,
        rpitems[item_id].tax_rate = new_pr_tax_rate,
        rpitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        rpitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '';
        rpitems[item_id].row.product_formulation = $('#opt_formulation').val();
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        $('#prModal').modal('hide');

        loadItems();
        return;
    });

    /* -----------------------
     * Repair technician change
     ----------------------- */
     $(document).on('change', '.rtechnician', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var technician = $(this).val();
        rpitems[item_id].row.technician_id = technician;
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        loadItems();
    });

    /* -----------------------
     * Product option change
     ----------------------- */
     $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = rpitems[item_id];
        var unit = $('#punit').val(), real_unit_price = item.row.real_unit_price;
        if(unit != rpitems[item_id].row.base_unit) {
            $.each(rpitems[item_id].units, function(){
                if (this.id == unit) {
                    real_unit_price = formatDecimalRaw((parseFloat(item.row.real_unit_price)*(unitToBaseQty(1, this))), 4)
                }
            });
        }
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(real_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            rpitems = {};
            if ($('#rpwarehouse').val() && $('#rpcustomer').val()) {
                $('#rpcustomer').select2("readonly", true);
                $('#rpwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / 100);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        rpitems[item_id].row.base_quantity = new_qty;
        if(rpitems[item_id].row.unit != rpitems[item_id].row.base_unit) {
            $.each(rpitems[item_id].units, function(){
                if (this.id == rpitems[item_id].row.unit) {
                    rpitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        rpitems[item_id].row.qty = new_qty;
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        loadItems();
    });
	
	$(document).on('change', '#sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = rpitems[item_id];
		var qty = item.row.qty;
		var new_unit = parseFloat($(this).val());
		var base_quantity = qty;
		if(new_unit != item.row.base_unit) {
            $.each(item.units, function(){
                if (this.id == new_unit) {
                    base_quantity = unitToBaseQty(qty, this);
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						unit_price = this.unit_price - (this.unit_price * ppercent);
					}else{
						unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
					}
					
				}
            });
        }else{
			unit_price = item.row.real_unit_price;
		}
		rpitems[item_id].row.base_quantity = base_quantity;
		rpitems[item_id].row.unit_price = unit_price;
		rpitems[item_id].row.unit = new_unit;
		localStorage.setItem('rpitems', JSON.stringify(rpitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        rpitems[item_id].row.price = new_price;
        localStorage.setItem('rpitems', JSON.stringify(rpitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
       $('#rpcustomer').select2('readonly', false);
       //$('#rpwarehouse').select2('readonly', false);
       return false;
    });


});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#rpcustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}


//localStorage.clear();
function loadItems() {

    if (localStorage.getItem('rpitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $("#rpTable tbody").empty();
        rpitems = JSON.parse(localStorage.getItem('rpitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(rpitems, function(o){return [parseInt(o.order)];}) :   rpitems;
        $('#add_repair, #edit_repair, #add_repair_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_price = item.row.unit_price;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var item_comment = item.row.comment ? item.row.comment : '';
			
			if(item.row.fup != 1 && product_unit != item.row.base_unit) {
				$.each(item.units, function(){
					if (this.id == product_unit) {
						base_quantity = unitToBaseQty(item.row.qty, this);
						if(this.unit_price != null && this.unit_price > 0){
							var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
							unit_price = this.unit_price - (this.unit_price * ppercent);
						}else{
							unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
						}
						
					}
				});
			}

            if(item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = unit_price+(parseFloat(this.price));
                        unit_price = item_price;
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw((((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = formatDecimalRaw(ds);
                }
            } else {
                 item_discount = formatDecimalRaw(ds);
            }
			
			if(item_discount>0){
				var item_discount_percent = '('+formatDecimal((item_discount * 100)/unit_price)+'%)';
			}else{
				var item_discount_percent = '';
			}
            product_discount += parseFloat(item_discount * item_qty);
            unit_price = formatDecimalRaw(unit_price-item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimalRaw(unit_price-pr_tax_val, 4) : formatDecimalRaw(unit_price);
            unit_price = formatDecimalRaw(unit_price+item_discount, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="pbname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i></td>';
            
            technician_opt = $("<select id=\"rtechnician\" name=\"technician[]\" class=\"form-control rtechnician select\" />");
            $.each(item.technicians, function () {
                var name = this.first_name + ' ' + this.last_name;
                if(this.id == item.row.technician_id) {
                    $("<option />", {value: this.id, text: name, selected:true}).appendTo(technician_opt);
                } else {
                    $("<option />", {value: this.id, text: name}).appendTo(technician_opt);
                }
            });
            tr_html +='<td>'+(technician_opt.get(0).outerHTML)+'</td>';
			if(site.settings.show_qoh == 1){
				var qoh = item_aqty;
				tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
			}
			tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimalRaw(item_price) + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<input type="hidden" class="currency_rate" name="currency_rate[]"  value="' + item.row.currency_rate + '"/>';
			tr_html += '<input type="hidden" class="currency_code" name="currency_code[]"  value="' + item.row.currency_code + '"/>';
			var squantity = '';
			tr_html += '<td style="display:none;"><input ' + squantity + ' class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
			if (site.settings.show_unit == 1) {
				uopt = $("<select id=\"sunit\" name=\"sunit\" class=\"form-control select\" />");
				$.each(item.units, function () {
					if(this.id == item.row.unit) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(uopt);
					}
				});
				tr_html +='<td>'+(uopt.get(0).outerHTML)+'</td>';
			}
			if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + (item_discount_percent)+'</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer rpdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            tr_html += '<input type="hidden" name="warranty[]" value="'+item.row.warranty+'" />';
			newTr.html(tr_html);
            newTr.prependTo("#rpTable");
			$('select').select2();
            var currency_rate = (item.row.currency_rate?item.row.currency_rate:1);
            total += formatDecimalRaw(((parseFloat(item_price / currency_rate) + parseFloat(pr_tax_val / currency_rate)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                    }
                });
            } else if(item_type == 'standard' && base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
            }
        });
        var col = 3;
		if (site.settings.show_qoh == 1) { col += 1 }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center" style="display:none;">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.show_unit == 1) { 
			tfoot += '<th></th>';	
		}
		if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#rpTable tfoot').html(tfoot);
        // Order level discount calculations
        if (rpdiscount = localStorage.getItem('rpdiscount')) {
            var ds = rpdiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimalRaw((((total) * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimalRaw(ds);
                }
            } else {
                order_discount = formatDecimalRaw(ds);
            }
        }
        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (rptax2 = localStorage.getItem('rptax2')) {
                $.each(tax_rates, function () {
                    if (this.id == rptax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimalRaw(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount));
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		if(count > 1){
			$('#rpphone').prop("readonly", true);
			$('#rpcustomer').prop("readonly", true);
            $('#rpwarehouse').select2("readonly", true);
			$('#rpbrand').select2("readonly", true);
            $('#rpmodel').select2("readonly", true);
		}else{
			$('#rpphone').prop("readonly", false);
			$('#rpcustomer').prop("readonly", false);
            $('#rpwarehouse').select2("readonly", false);
			$('#rpbrand').select2("readonly", false);
            $('#rpmodel').select2("readonly", false);
		}
        set_page_focus();
    }
}

/* -----------------------------
 * Add Quotation Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {

    if (count == 1) {
        rpitems = {};
        if ($('#rpwarehouse').val() && $('#rpcustomer').val()) {
            $('#rpcustomer').select2("readonly", true);
            $('#rpwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (rpitems[item_id]) {
        var new_qty = parseFloat(rpitems[item_id].row.qty) + 1;
        rpitems[item_id].row.base_quantity = new_qty;
        if(rpitems[item_id].row.unit != rpitems[item_id].row.base_unit) {
            $.each(rpitems[item_id].units, function(){
                if (this.id == rpitems[item_id].row.unit) {
                    rpitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        rpitems[item_id].row.qty = new_qty;

    } else {
        rpitems[item_id] = item;
    }
    rpitems[item_id].order = new Date().getTime();
    localStorage.setItem('rpitems', JSON.stringify(rpitems));
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
