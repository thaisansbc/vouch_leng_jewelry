$(document).ready(function () {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    if (rediscount = localStorage.getItem('rediscount')) {
        $('#rediscount').val(rediscount);
    }
    $('#retax2').change(function (e) {
        localStorage.setItem('retax2', $(this).val());
        $('#retax2').val($(this).val());
    });
    if (retax2 = localStorage.getItem('retax2')) {
        $('#retax2').select2("val", retax2);
    }
    $('#restatus').change(function (e) {
        localStorage.setItem('restatus', $(this).val());
    });
    if (restatus = localStorage.getItem('restatus')) {
        $('#restatus').select2("val", restatus);
    }
    var old_shipping;
    $('#reshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('reshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (reshipping = localStorage.getItem('reshipping')) {
        shipping = parseFloat(reshipping);
        $('#reshipping').val(shipping);
    } else {
        shipping = 0;
    }
    $('#resupplier').change(function (e) {
        localStorage.setItem('resupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if (resupplier = localStorage.getItem('resupplier')) {
        $('#resupplier').val(resupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                deietMillis: 15,
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
        nsSupplier();
    }
    if (localStorage.getItem('reitems')) {
        loadItems();
    }
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('reitems')) {
                    localStorage.removeItem('reitems');
                }
                if (localStorage.getItem('rediscount')) {
                    localStorage.removeItem('rediscount');
                }
                if (localStorage.getItem('retax2')) {
                    localStorage.removeItem('retax2');
                }
                if (localStorage.getItem('reshipping')) {
                    localStorage.removeItem('reshipping');
                }
                if (localStorage.getItem('deref')) {
                    localStorage.removeItem('deref');
                }
                if (localStorage.getItem('rewarehouse')) {
                    localStorage.removeItem('rewarehouse');
                }
                if (localStorage.getItem('renote')) {
                    localStorage.removeItem('renote');
                }
                if (localStorage.getItem('deinnote')) {
                    localStorage.removeItem('deinnote');
                }
                if (localStorage.getItem('recustomer')) {
                    localStorage.removeItem('recustomer');
                }
                if (localStorage.getItem('recurrency')) {
                    localStorage.removeItem('recurrency');
                }
                if (localStorage.getItem('dedate')) {
                    localStorage.removeItem('dedate');
                }
                if (localStorage.getItem('restatus')) {
                    localStorage.removeItem('restatus');
                }
                if (localStorage.getItem('debiller')) {
                    localStorage.removeItem('debiller');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    });
    $('#deref').change(function (e) {
        localStorage.setItem('deref', $(this).val());
    });
    if (deref = localStorage.getItem('deref')) {
        $('#deref').val(deref);
    }
    $('#rewarehouse').change(function (e) {
        localStorage.setItem('rewarehouse', $(this).val());
    });
    if (rewarehouse = localStorage.getItem('rewarehouse')) {
        $('#rewarehouse').select2("val", rewarehouse);
    }
    $('#renote').redactor('destroy');
    $('#renote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('renote', v);
        }
    });
    if (renote = localStorage.getItem('renote')) {
        $('#renote').redactor('set', renote);
    }
    var $customer = $('#recustomer');
    $customer.change(function (e) {
        localStorage.setItem('recustomer', $(this).val());
    });
    if (recustomer = localStorage.getItem('recustomer')) {
        $customer.val(recustomer).select2({
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
                deietMillis: 15,
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
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    if (site.settings.tax2 != 0) {
        $('#retax2').change(function () {
            localStorage.setItem('retax2', $(this).val());
            loadItems();
            return;
        });
    }
    var old_rediscount;
    $('#rediscount').focus(function () {
        old_rediscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('rediscount');
            localStorage.setItem('rediscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_rediscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }
    });

    /* ----------------------
    * Delete Row Method
    * ---------------------- */
    $(document).on('click', '.dedel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete reitems[item_id];
        row.remove();
        if(reitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
            return;
        }
    });

    $(document).on('click', '.dedup', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var copiedItem = $.extend(true, {}, reitems[item_id]);
        copiedItem.id = Math.floor(Math.random() * (9999999 - 100000 + 1)) + 100000;
        add_invoice_item(copiedItem);
    });

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = reitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_cost = formatDecimalRaw(row.children().children('.rucost').val()),
        discount = row.children().children('.rdiscount').val();
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == item.row.option && this.cost != 0 && this.cost != '' && this.cost != null) {
                    unit_cost = parseFloat(item.row.unit_cost)+parseFloat(this.cost);
                }
            });
        }
        var unit_cost = item.row.unit_cost;
        var net_cost = unit_cost;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw(parseFloat(((unit_cost) * parseFloat(pds[0])) / 100));
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_cost -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {
                            if (reitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimalRaw((((net_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))));
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                                net_cost -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimalRaw((((net_cost) * parseFloat(this.rate)) / 100));
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
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
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
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pcost').val(unit_cost);
        $('#punit_cost').val(formatDecimalRaw(parseFloat(unit_cost)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_cost').val(unit_cost);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_cost').text(formatMoney(net_cost-item_discount));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');
    });
	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = reitems[item_id];
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
        reitems[item_id].row.order = parseFloat($('#iorders').val()),
        reitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('reitems', JSON.stringify(reitems));
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
    $(document).on('change', '#pcost, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_cost = parseFloat($('#pcost').val());
        var item = reitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_cost -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_cost -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_cost) * parseFloat(this.rate)) / 100));
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }
        $('#net_cost').text(formatMoney(unit_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });
    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = poitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        if(unit != poitems[item_id].row.base_unit) {
            $.each(item.units, function() {
                if (this.id == unit) {
                    $('#pcost').val(formatDecimalRaw((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))))).change();
                }
            });
        } else {
            $('#pcost').val(formatDecimalRaw(item.row.base_unit_cost)).change();
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
        var cost = parseFloat($('#pcost').val());
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.cost != 0 && this.cost != '' && this.cost != null) {
                    cost = cost-parseFloat(this.cost);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > cost) {
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
        if(unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        reitems[item_id].row.fup = 1,
        reitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        reitems[item_id].row.base_quantity = parseFloat(base_quantity),
        reitems[item_id].row.unit_cost = cost,
        reitems[item_id].row.unit = unit,
        reitems[item_id].row.tax_rate = new_pr_tax,
        reitems[item_id].tax_rate = new_pr_tax_rate,
        reitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        reitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        reitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('reitems', JSON.stringify(reitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* -----------------------
     * Product option change
     ----------------------- */
     $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = reitems[item_id];
        var unit = $('#punit').val(), base_quantity = parseFloat($('#pquantity').val()), unit_cost = item.row.unit_cost;
        if (unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function() {
                if (this.id == unit) {
                    unit_cost = formatDecimalRaw((parseFloat(item.row.unit_cost)*(unitToBaseQty(1, this))))
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.cost != 0 && this.cost != '' && this.cost != null) {
                    $('#pcost').val(parseFloat(unit_cost)+(parseFloat(this.cost))).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            reitems = {};
            if ($('#rewarehouse').val() && $('#recustomer').val()) {
                $('#recustomer').select2("readonly", true);
                $('#rewarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_cost').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

	$(document).on("change", '.slength, .swidth, .sdeare', function () {
        var parent = $(this).parent().parent();
		var row = $(this).closest('tr');
		var slength = parent.find(".slength").val();
		var swidth = parent.find(".swidth").val();
		var sdeare = parent.find(".sdeare").val();
		var quantity = (parseFloat(slength) * parseFloat(swidth)) * sdeare;		
		item_id = row.attr('data-item-id');
		reitems[item_id].row.slength = slength;
		reitems[item_id].row.swidth = swidth;
		reitems[item_id].row.sdeare = sdeare;
		reitems[item_id].row.qty = quantity;
		if (reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
		$.each(reitems[item_id].units, function(){
				if (this.id == reitems[item_id].row.unit) {
					reitems[item_id].row.base_quantity = unitToBaseQty(quantity, this);
				}
			});
		} else {
			reitems[item_id].row.base_quantity = quantity;
		}
        localStorage.setItem('reitems', JSON.stringify(reitems));
        loadItems();		
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */
    var old_row_qty = 0;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
		var item_received = row.find(".received").text() - 0;
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        reitems[item_id].row.base_quantity = new_qty;
        if (reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function() {
                if (this.id == reitems[item_id].row.unit) {
                    reitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
		if (new_qty > item_received || new_qty < 1) {
			$(this).val(old_row_qty);
			bootbox.alert(lang.unexpected_value);
			return;
		}
        reitems[item_id].row.qty = new_qty;
        localStorage.setItem('reitems', JSON.stringify(reitems));
        loadItems();
    });

    

    $(document).on('change', '.rexpiry', function() {
        var row = $(this).closest('tr');
        item_id = row.attr('data-item-id');
        reitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('reitems', JSON.stringify(reitems));
        loadItems();
    })
	$(document).on('change', '.rserial', function() {
        var row = $(this).closest('tr');
        item_id = row.attr('data-item-id');
        reitems[item_id].row.serial_no = $(this).val();
        localStorage.setItem('reitems', JSON.stringify(reitems));
        loadItems();
    })

	var old_sup_qty = 0;
    $(document).on("focus", '.sup_qty', function () {
        old_sup_qty = $(this).val();
    }).on("change", '.sup_qty', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_sup_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var sup_qty = parseFloat($(this).val());
		var item_id = row.attr('data-item-id');
        reitems[item_id].row.sup_qty = sup_qty;
        localStorage.setItem('reitems', JSON.stringify(reitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Cost Method
     -------------------------- */
    var old_cost;
     $(document).on("focus", '.rcost', function () {
        old_cost = $(this).val();
    }).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        poitems[item_id].row.cost = new_cost;
        localStorage.setItem('poitems', JSON.stringify(poitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
       $('#recustomer').select2('readonly', false);
       //$('#rewarehouse').select2('readonly', false);
       return false;
    });
});
/* -----------------------
 * Misc Actions
 ----------------------- */
function nsCustomer() {
    $('#recustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            deietMillis: 15,
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

function nsSupplier() {
    $('#resupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
            dataType: 'json',
            deietMillis: 15,
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

function loadItems() {
    if (localStorage.getItem('reitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $("#reTable tbody").empty();
        reitems = JSON.parse(localStorage.getItem('reitems'));
        sortedItems = _.sortBy(reitems, function(o){return [parseInt(o.item_id)];});
        $('#add_receive, #edit_receive').attr('disabled', false);
        // console.log(sortedItems);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_cost = item.row.cost,item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var item_expiry = (item.row.expiry ? item.row.expiry : "");
			var unit_cost = item.row.unit_cost, item_received = item.row.received;
			var item_comment = item.row.comment ? item.row.comment : '';
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var purchase_item_id = item.row.purchase_item_id;
            var item_weight = item.row.weight;
            if (item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function(){
                    if (this.id == product_unit) {
                        base_quantity = formatDecimalRaw(unitToBaseQty(item.row.qty, this));
                        unit_cost = formatDecimalRaw((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))));
                    }
                });
            }
            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw((((unit_cost) * parseFloat(pds[0])) / 100));
                } else {
                    item_discount = formatDecimalRaw(ds);
                }
            } else {
                 item_discount = formatDecimalRaw(ds);
            }
            product_discount += parseFloat(item_discount * item_qty);
            unit_cost = formatDecimalRaw(unit_cost-item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimalRaw((((unit_cost) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))));
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_cost) * parseFloat(pr_tax.rate)) / 100));
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_cost = item_tax_method == 0 ? formatDecimalRaw(unit_cost-pr_tax_val) : formatDecimalRaw(unit_cost);
            unit_cost = formatDecimalRaw(unit_cost+item_discount);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
			var row_no = item_id;
			var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			tr_html = '<td><input name="purchase_item_id[]" type="hidden" class="rpid" value="' + purchase_item_id + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i> &nbsp;<i class="pull-right fa fa-edit tip pointer edit hidden" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
			if (site.settings.product_expiry == 1) {
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '" autocomplete="off"></td>';
            }
			tr_html += '<input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + formatDecimalRaw(item_cost) + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '">';
			if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<input class="rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '" />';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<input class="rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '" />';
            }
			var cbm = 0;
			if (item.row.total_cbm > 0 && item.row.unit_quantity > 0) {
				cbm = item.row.total_cbm / item.row.unit_quantity;
			}
			tr_html += '<td class="text-center"><input type="hidden" name="cbm[]" value="'+cbm+'"/><span class="received">' + formatDecimalRaw(item_received) + '</span></td>';
			if (site.settings.product_serial == 1) {
				tr_html += '<td><input class="rserial form-control" name="serial_no[]" type="text" value="' + item.row.serial_no + '" id="serial_no_' + row_no + '"/></td>';
            }
			tr_html += '<td><input class="form-control text-center rquantity" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();" style="background-color:#fff;" /><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
			if (site.settings.using_weight == 1) {
				tr_html += '<td><input class="form-control text-center weight" name="weight[]" type="text" value="' + formatDecimalRaw(item_weight) + '" data-id="' + row_no + '" data-item="' + item_id + '"/></td>';
			}
            if (concretes) {
                tr_html += '<td><input readonly="true" class="form-control text-center sup_qty" name="sup_qty[]" type="text" value="' + formatDecimalRaw(item.row.sup_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '"/></td>';
            }
			tr_html += '<td class="text-center">' + 
                        '<i class="fa fa-trash-o tip pointer dedel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i>&emsp;' + 
                        '<i class="fa fa-plus-circle tip pointer dedup" id="' + row_no + '" title="Duplicate" style="cursor:pointer;"></i>' + 
                    '</td>';
			tr_html += '<input type="hidden" name="parent_id[]" value="'+item.row.parent_id+'" />';
			newTr.html(tr_html);
            newTr.prependTo("#reTable");
            total += formatDecimalRaw(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_received)));
            count += parseFloat(item_received);
            an++;
            $('#row_' + row_no).addClass('warning');

            var new_arr = {};
            if (reitems != null) {
                $.each(reitems, function (index, obj) {
                    if (obj.row.purchase_item_id == purchase_item_id) {
                        new_arr[obj.row.purchase_item_id] = 
                        new_arr[obj.row.purchase_item_id] === undefined ? 0 : new_arr[obj.row.purchase_item_id];
                        new_arr[obj.row.purchase_item_id] += parseInt(obj.row.qty);
                    }
                });
                $.each(reitems, function () {
                    if (Object.keys(new_arr) == this.row.purchase_item_id) {
                        if (parseInt(Object.values(new_arr)) > parseInt(this.row.received)) {
                            $('#row_' + row_no).addClass('danger');
                            $('#add_receive, #edit_receive').attr('disabled', true);
                        }
                    }
                });
            }
        });
        var col = (site.settings.using_weight == 1) ? 2:1;

		if (site.settings.product_expiry == 1) { col++; }
		if (site.settings.product_serial == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="' + col + '">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        tfoot += '<th></th>'+(concretes ? '<th></th>' : '')+'<th class="text-center">Actions</th></tr>';
        $('#reTable tfoot').html(tfoot);
        if (rediscount = localStorage.getItem('rediscount')) {
            var ds = rediscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimalRaw((((total) * parseFloat(pds[0])) / 100));
                } else {
                    order_discount = formatDecimalRaw(ds);
                }
            } else {
                order_discount = formatDecimalRaw(ds);
            }
        }
        if (site.settings.tax2 != 0) {
            if (retax2 = localStorage.getItem('retax2')) {
                $.each(tax_rates, function () {
                    if (this.id == retax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimalRaw(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100));
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
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
        reitems = {};
        if ($('#rewarehouse').val() && $('#recustomer').val()) {
            $('#recustomer').select2("readonly", true);
            $('#rewarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) return;
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (reitems[item_id]) {
        var new_qty = parseFloat(reitems[item_id].row.qty) + 1;
        reitems[item_id].row.base_quantity = new_qty;
        if(reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function() {
                if (this.id == reitems[item_id].row.unit) {
                    reitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        reitems[item_id].row.qty = new_qty;
    } else {
        reitems[item_id] = item;
    }
    reitems[item_id].order = new Date().getTime();
    localStorage.setItem('reitems', JSON.stringify(reitems));
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
