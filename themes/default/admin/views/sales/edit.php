<?php defined('BASEPATH') or exit('No direct script access allowed');
    $allow_discount = ($Owner || $Admin || $this->session->userdata('allow_discount') || $inv->order_discount_id);
?>
<style>
    ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }
</style>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, allow_discount = <?= $allow_discount ? 1 : 0; ?>,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
    var quote = null;
    var is_editing = true;
    $(document).ready(function () {
        <?php if ($inv) { ?>
            localStorage.setItem('sldate', '<?= $this->bpas->hrld($inv->date) ?>');
            localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
            localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
            localStorage.setItem('slwarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
            localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
            localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
            localStorage.setItem('slnote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($inv->note)); ?>');
            localStorage.setItem('slinnote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($inv->staff_note)); ?>');
            localStorage.setItem('sldiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('slshipping_request', '<?= $inv->shipping_request ?>');
            localStorage.setItem('slshipping_request_phone', '<?= $inv->shipping_request_phone ?>');
            localStorage.setItem('slshipping_request_address', '<?= $inv->shipping_request_address ?>');
            localStorage.setItem('slshipping_request_note', '<?= $inv->shipping_request_note ?>');
            localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>

        // var cur_date = new Date(new Date().setHours(23, 59, 59, 999));
        // Date.prototype.addDays = function(days) {
        //     var date = new Date(this.valueOf());
        //     date.setDate(date.getDate() + days);
        //     return date;
        // }
        // var tmr_date = cur_date.addDays(1);
        // $("#sldate").datetimepicker({
        //     format: site.dateFormats.js_ldate,
        //     fontAwesome: true,
        //     language: 'sma',
        //     weekStart: 1,
        //     todayBtn: 1,
        //     autoclose: 1,
        //     todayHighlight: 1,
        //     startView: 1,
        //     forceParse: 0,
        //     endDate: tmr_date
        // });
        // <?php if ($Owner || $Admin || $GP['change_date']) { ?>
        //     var old_date = $('#sldate').val();
        //     $(document).on('change', '#sldate', function (e) {
        //         const new_date = moment($(this).val(), 'DD-MM-YYYY HH:mm').toDate();
        //         if(new Date(new_date) > new Date(tmr_date)) {
        //             setDate(old_date);
        //             localStorage.setItem('sldate', old_date);
        //         } else {
        //             setDate($(this).val());
        //             localStorage.setItem('sldate', $(this).val());
        //         }
        //     });
        //     if (sldate = localStorage.getItem('sldate')) {
        //         $('#sldate').val(sldate);
        //     }
        // <?php } ?>
        // function setDate(date_set) {
        //     $("#sldate").datetimepicker({
        //         format: site.dateFormats.js_ldate,
        //         fontAwesome: true,
        //         language: 'sma',
        //         weekStart: 1,
        //         todayBtn: 1,
        //         autoclose: 1,
        //         todayHighlight: 1,
        //         startView: 1,
        //         forceParse: 0,
        //         endDate: tmr_date
        //     }).datetimepicker('update', date_set);
        // }

        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            $(document).on('change', '#sldate', function (e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
        <?php } ?>

        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                var Settings_ = jQuery.parseJSON('<?php echo json_encode($Settings); ?>');
                if (ui.item.id !== 0) {
                    if (ui.item.combo_items !== false && Settings_['product_combo'] == 0) {
                        var wh = $("#slwarehouse").val();
                        $.ajax({
                            type: "get",
                            url: "<?= admin_url('sales/getProductCombo'); ?>",
                            data: { product_id: ui.item.row.id, warehouse_id: wh, },
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    for (var i = 0; i < data.length; i++) {
                                        data.free = true;
                                        data.parent = ui.item.row.id;
                                        add_invoice_item(data[i]);
                                    }
                                }
                                $("#add_item").removeClass('ui-autocomplete-loading');
                            }
                        }).done(function () {
                            $('#add_item').val('');
                            $('#modal-loading').hide();
                        });
                        if (row) $(this).val('');
                    } else {
                        var row = add_invoice_item(ui.item);
                        if (row) $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $(window).bind('beforeunload', function (e) {
            localStorage.setItem('remove_slls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        // $('#edit_sale').click(function () {
        //     $(window).unbind('beforeunload');
        //     $('form.edit-so-form').submit();
        // });
        if($('#zone_id').val() == ''){
            $(document).ready(setZoneSelector);
        } else {
            $(document).ready(setZoneSelector2);
        }
        $('#saleman_by').change(setZoneSelector);
        function setZoneSelector(){
            var saleman_id =  $('#saleman_by').val() ?  $('#saleman_by').val() : '';
            if(saleman_id != ""){
                $.ajax({
                    type: "get",
                    url: site.base_url + "sales/getZonesBySaleman_ajax/" + saleman_id,
                    dataType: "json",
                    success: function (data) {
                        if(data != false){
                            $("#zone_id").find('option').remove().end();
                            if(data['z_b_user']['multi_zone'] != null){
                                $("#zone_id").append("<option selected='selected'>Select Zone</option>");
                                mz_id = data['z_b_user']['multi_zone'].split(',');
                                mz_id.forEach((element, index, array) => {
                                    let zone = data['z_all'].find(x => x.id === element);
                                    $("#zone_id").append("<option value='" + zone.id + "'>" + zone.zone_name + "</option>");
                                    if(zone.parent_id == 0){
                                        data['z_all'].forEach(element => {
                                            if(element.parent_id == zone.id){
                                                $("#zone_id").append("<option value='" + element.id + "'>" + "&emsp;" + element.zone_name + "</option>");
                                            }
                                        });
                                    } 
                                });
                            } else {
                                $("#zone_id").append("<option selected='selected'>No matches found</option>");
                            }
                            $("#zone_id option:first").attr('selected','selected').trigger('change');
                        }
                    },
                }).fail(function(xhr, error){
                    console.debug(xhr); 
                    console.debug(error);
                });
            }
        }
        function setZoneSelector2() {
            var saleman_id =  $('#saleman_by').val() ?  $('#saleman_by').val() : '';
            if(saleman_id != ""){
                $.ajax({
                    type: "get",
                    url: site.base_url + "sales/getZonesBySaleman_ajax/" + saleman_id,
                    dataType: "json",
                    success: function (data) {
                        if(data != false){
                            $("#zone_id").find('option').remove().end();
                            if(data['z_b_user']['multi_zone'] != null){
                                $("#zone_id").append("<option selected='selected'>Select Zone</option>");
                                mz_id = data['z_b_user']['multi_zone'].split(',');
                                mz_id.forEach((element, index, array) => {
                                    let zone = data['z_all'].find(x => x.id === element);
                                    $("#zone_id").append("<option value='" + zone.id + "'>" + zone.zone_name + "</option>");
                                    if(zone.parent_id == 0){
                                        data['z_all'].forEach(element => {
                                            if(element.parent_id == zone.id){
                                                $("#zone_id").append("<option value='" + element.id + "'>" + "&emsp;" + element.zone_name + "</option>");
                                            }
                                        });
                                    } 
                                });
                            } else {
                                $("#zone_id").append("<option selected='selected'>No matches found</option>");
                            }
                            // $("#zone_id option:first").attr('selected','selected').trigger('change');
                        }
                    },
                }).fail(function(xhr, error){
                    console.debug(xhr); 
                    console.debug(error);
                });
            }
        }
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_sale'); ?></h2>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form'];
                    echo admin_form_open_multipart('sales/edit/' . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'sldate'); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($inv->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'slref'); ?>
                                <?php 
                                if ($Owner || $Admin || $GP['change_invoiceNo']) {
                                    echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" required="required"'); ?>
                                <?php }else{
                                    echo form_input('reference_no1', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" disabled="disabled" required="required"'); 
                                    echo form_hidden('reference_no',$inv->reference_no , 'required="required"');
                                } 
                                ?>
                            </div>
                        </div>
                        <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } elseif (count($user_billers) > 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        foreach ($user_billers as $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type'  => 'hidden',
                                'name'  => 'biller',
                                'id'    => 'slbiller',
                                'value' => $inv->biller_id,
                            );
                            echo form_input($biller_input);
                        } ?>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <?php if ($this->Settings->project) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("project", "poproject"); ?>
                                                <?php
                                                if(isset($inv)){
                                                    $project_id=  $inv->project_id;
                                                }else{
                                                    $project_id=  "";
                                                }
                                                $pro[""] = "";
                                                foreach ($projects as $project) {
                                                    $pro[$project->project_id] = $project->project_name;
                                                }
                                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('warehouse', 'slwarehouse'); ?>
                                                <?php
                                                $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                            </div>
                                        </div>
                                    <?php } elseif (count($count)>1) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('warehouse', 'powarehouse'); ?>
                                                <?php
                                                $wh[''] = '';
                                                if (!empty($warehouses)) {
                                                    foreach ($warehouses as $warehouse) {
                                                        foreach ($count as $key => $value) {
                                                            if ($warehouse->id==$value) {
                                                                $wh[$warehouse->id] = $warehouse->name;
                                                            }
                                                        }
                                                    }
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                            </div>
                                        </div>
                                    <?php } else {
                                        $warehouse_input = [
                                            'type'  => 'hidden',
                                            'name'  => 'warehouse',
                                            'id'    => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        ];
                                        echo form_input($warehouse_input);
                                    } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('customer', 'slcustomer'); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="slcustomer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if($Settings->sale_man) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?php echo ($Settings->module_clinic == 1 ? lang("saleman", "saleman") : lang("salesman ", "salesman")); ?>
                                                <?php
                                                if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                                    $key = array_search($inv->saleman_by, array_column($salemans, 'id'));
                                                    $sm[$salemans[$key]->id] = $salemans[$key]->first_name.' '.$salemans[$key]->last_name;
                                                    
                                                    echo form_dropdown('saleman_by', $sm, (($inv->saleman_by != "")? $inv->saleman_by : ''), 'id="saleman_by" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("saleman") . '" style="width:100%;" ');
                                                } else {
                                                    $sm[''] = '';
                                                    if (!empty($salemans)) {
                                                        foreach($salemans as $saleman){
                                                            $sm[$saleman->id] = $saleman->first_name.' '.$saleman->last_name;
                                                        }
                                                    }
                                                    echo form_dropdown('saleman_by', $sm, (($inv->saleman_by != "")? $inv->saleman_by : ''), 'id="saleman_by" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("saleman") . '" style="width:100%;" ');
                                                } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if($Settings->zone){ ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('zone', 'zone_id') ?>
                                                <?php
                                                if($Settings->zone_by_saleman){
                                                    $z[''] = '';
                                                    foreach ($zones as $zn) {
                                                        $z[$zn->id] = $zn->zone_name;
                                                    }
                                                    echo form_dropdown('zone_id', $z, (isset($_POST['zone_id']) ? $_POST['zone_id'] : $inv->zone_id), 'class="form-control select" id="zone_id" data-placeholder="' . lang("select") . ' ' . lang("zone") . '"style="width:100%"');
                                                }else{
                                                    $zon[""] = "";
                                                    foreach ($zones as $zone) {
                                                        $zon[$zone->id] = $zone->zone_code.' '.$zone->zone_name;
                                                    }
                                                    echo form_dropdown('zone_id',$zon, (isset($_POST['zone_id']) ? $_POST['zone_id'] : $inv->zone_id), 'class="form-control select" data-placeholder="'.lang("select").' ' .lang("zone").'"style="width:100%"');
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php }
                                    if ($this->Settings->module_clinic) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('diagnosis', 'diagnosis'); ?>
                                            <?php 
                                            $diagnosis_id = explode(',', $inv->diagnosis_id);
                                            $get_fields = $this->site->getcustomfield('ill');
                                            $field ['']=lang('select');
                                            if (!empty($get_fields)) {
                                                foreach ($get_fields as $field_id) {
                                                    $field[$field_id->id] = $field_id->name;
                                                }
                                            }
                                            echo form_dropdown('diagnosis[]',$field, $diagnosis_id, 'class="form-control select" id="diagnosis" multiple="multiple"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('type', 'type'); ?>
                                            <?php 
                                            $patience_type =['IPD'=>lang('IPD'),'OPD'=>lang('OPD')];
                                            echo form_dropdown('patience_type',$patience_type,$inv->patience_type, 'class="form-control select" id="patience_type"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("bed","bed"); ?>
                                            <?php
                                            $tab [''] = lang('please_selected');
                                            foreach ($tables as $table) {
                                                $tab[$table->id] = $table->name;
                                            }
                                            echo form_dropdown('bed', $tab,$inv->bed_id, 'class="form-control tip" id="bed" required="required" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('po', 'po'); ?>
                                            <?php echo form_input('po', (isset($_POST['po']) ? $_POST['po'] : $inv->po_number), 'class="form-control input-tip" id="po"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                            <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="addManually">
                                                        <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <label class="table-label"><?= lang('order_items'); ?> *</label>
                                    <div class="controls table-controls">
                                        <table id="slTable"
                                        class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                <th id="addition_type" class="col-md-2" style="display: none;"><?= lang('type'); ?></th>
                                                <!-- <th  class="col-md-1" ><?= lang('item_barcode'); ?></th> -->
                                                <?php if ($Settings->product_serial) {
                                                    echo '<th class="col-md-1">' . lang('serial_no') . '</th>';
                                                }
                                                // if ($Settings->product_serial) {
                                                //     echo '<th class="col-md-1">' . lang('max_serial') . '</th>';
                                                // } ?>
                                                <?php if ($Settings->warranty) {
                                                    echo '<th class="col-md-1">' . lang('warranty') . '</th>';
                                                } ?>
                                                <th class="col-md-1"><?= lang('net_unit_price'); ?></th>
                                                <th><?= lang('unit'); ?></th>
                                                <?php if ($Settings->show_qoh == 1) { ?>  
                                                <th class="col-md-1"><?= lang("qoh"); ?></th>
                                                <?php }?>
                                                <th class="col-md-1"><?= lang('quantity'); ?></th>
                                                <?php if ($Settings->product_discount && $allow_discount) {
                                                    echo '<th class="col-md-1">' . lang('discount') . '</th>';
                                                } ?>
                                                <?php if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . lang('product_tax') . '</th>';
                                                } ?>
                                                <th><?= lang('subtotal'); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php if ($Settings->tax2) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('order_tax', 'sltax2'); ?>
                                        <?php
                                        $tr[''] = '';
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($allow_discount) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('order_discount', 'sldiscount'); ?>
                                        <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount" ' . ($allow_discount ? '' : 'readonly="true"')); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('shipping', 'slshipping'); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('document', 'document') ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                    data-show-preview="false" class="form-control file">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('sale_status', 'slsale_status'); ?>
                                    <?php 
                                        if ($Settings->sale_consignment) {
                                            $sst = ['completed' => lang('completed'), 'consignment' => lang('consignment'), 'pending' => lang('pending')];
                                        } else {
                                            $sst = ['completed' => lang('completed'), 'pending' => lang('pending')];
                                        }
                                        echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('payment_term', 'slpayment_term'); ?>
                                    <?php
                                    if ($this->Settings->payment_term) {
                                        echo form_input('payment_term', $inv->payment_term?$inv->payment_term:"", 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); 
                                    }else{
                                        $ptr[""] = "";
                                        if (!empty($payment_term)) {
                                            foreach ($payment_term as $term) {
                                                $ptr[$term->id] = $term->description;
                                            }
                                        }
                                        echo form_dropdown('payment_term', $ptr,$inv->payment_term ? $inv->payment_term : "", 'id="slpayment_term" data-placeholder="' . lang("payment_term_tip") .  '" class="form-control input-tip select" style="width:100%;"'); 
                                    }
                                    ?>
                                </div>
                            </div>
                            <?= form_hidden('payment_status', $inv->payment_status); ?>
                            <?php if ($this->Settings->delivery && (empty($inv->delivery_status) || $inv->delivery_status == 'pending')) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('shipping_request', 'slshipping_request'); ?>
                                    <?php 
                                    $sh_opt = [0 => lang('no'), 1 => lang('yes')];
                                    echo form_dropdown('shipping_request', $sh_opt, $inv->shipping_request, 'class="form-control input-tip" required="required" id="slshipping_request"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div id="shipping_request_form" style="display: none;">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= lang('shipping_request_info', 'shipping_request_info'); ?>
                                            <div class="col-md-12 well well-sm well_1">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?= lang('phone', 'shipping_request_phone'); ?>
                                                        <?= form_input('shipping_request_phone', $inv->shipping_request_phone, 'class="form-control tip" id="slshipping_request_phone"'); ?>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?= lang('address', 'shipping_request_address'); ?>
                                                        <textarea name="shipping_request_address" id="slshipping_request_address" class="pa form-control kb-text shipping_request_address"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?= lang('note', 'shipping_request_note'); ?>
                                                        <textarea name="shipping_request_note" id="slshipping_request_note" class="pa form-control kb-text shipping_request_note"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="clearfix"></div>
                            <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                            <div class="row" id="bt">
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('sale_note', 'slnote'); ?>
                                            <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('staff_note', 'slinnote'); ?>
                                            <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ''), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="fprom-group"><?php echo form_submit('edit_sale', lang('submit'), 'id="edit_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($allow_discount) { ?>
                                <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
    <div class="modal-dialog" style="width:50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="comboModalLabel"></h4>
            </div>
            <div class="modal-body" style="margin-top:-15px !important;">
                <label class="table-label"><?= lang("combo_products"); ?></label>
                <table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                    <thead>
                        <tr>
                            <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                            <?php if ($Settings->qty_operation) { ?>
                                <th><?= lang('width') ?></th>
                                <th><?= lang('height') ?></th>
                            <?php } ?>
                            <th><?= lang('quantity') ?></th>
                            <th><?= lang('price') ?></th>
                            <th width="3%">
                                <a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, '', 'id="ptax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_variant') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if($this->Settings->product_option) { ?>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div_1"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount" <?= $allow_discount ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pdescription" class="col-sm-4 control-label"><?= lang('description') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pdescription">
                        </div>
                    </div>
                    <?php if($Settings->sale_man && $Settings->commission){ ?>
                        <label for="pdescription" class="col-sm-4 control-label"><?= lang('saleman') ?></label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                 <select id="saleman_item" name="saleman_item" class="form-control input-tip select">
                                    <?php
                                    echo '<option value="">----------</option>';
                                    if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                        echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
                                    } else {
                                        foreach($salemans as $agency){
                                            echo '<option value="'.$agency->id.'">'.$agency->first_name.' '.$agency->last_name.'</option>';
                                        }
                                    }
                                    ?>
                                </select> 
                            </div>
                        </div>
                    <?php } ?>
                    <table class="table table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>
                            <div class="col-sm-8">
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control input-tip select" style="width:100%;"'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="munit" class="col-sm-4 control-label"><?= lang('unit') ?> *</label>
                        <div class="col-sm-8">
                            <?php
                            $uts[''] = '';
                            foreach ($units as $unit) {
                                $uts[$unit->id] = $unit->name;
                            }
                            echo form_dropdown('munit', $uts, '', 'id="munit" class="form-control input-tip select" style="width:100%;"'); ?>
                        </div>
                    </div>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="mserial" class="col-sm-4 control-label"><?= lang('product_serial') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mserial">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount" <?= $allow_discount ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#sltax2').change(function(){
            $.ajax({
                type: "get",
                url: site.base_url + "sales/getSaleRef/" + $(this).val(),
                success: function(dataResult){
                    if(dataResult){
                        if('<?= $inv->reference_no ?>'.match(/SALE.*/)){
                            if (dataResult.match(/SALE.*/)) {
                                localStorage.setItem('slref', '<?= $inv->reference_no ?>');
                                $('#slref').val('<?= $inv->reference_no ?>');
                            } else {
                                localStorage.setItem('slref', dataResult);
                                $('#slref').val(dataResult);
                            }
                        } else {
                            if (dataResult.match(/TAX.*/)) {
                                localStorage.setItem('slref', '<?= $inv->reference_no ?>');
                                $('#slref').val('<?= $inv->reference_no ?>');
                            } else {
                                localStorage.setItem('slref', dataResult);
                                $('#slref').val(dataResult);
                            }
                        }
                    } else {
                        console.log("Not Found!");
                    }
                }
            });
        });
        var destination;
            $("#destination").on("select2-focus",function(e) {
                destination = $(this).val();
            }).on("select2-close",function(e){
                if($(this).val() != '' && $(this).val() == $("#from").val()){
                    $(this).select2('val',destination);
                    bootbox.alert("<?= lang("please_select_different_destination")?>");
                }
            });
        var from;
        $("#from").on("select2-focus",function(e){
            from=$(this).val();
        }).on("select2-close",function(e){
            if($(this).val() != '' && $(this).val() == $('#destination').val()){
                $(this).select2('val',from);
                bootbox.alert("<?= lang("please_select_different_destination")?>");
            }
        });
        //-------------
        $(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
            $(this).autocomplete({
                source: '<?= admin_url('products/suggestions'); ?>',
                minLength: 1,
                autoFocus: false,
                delay: 250,
                response: function (event, ui) {
                    if (ui.content.length == 1 && ui.content[0].id != 0) {
                        ui.item = ui.content[0];
                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                        $(this).removeClass('ui-autocomplete-loading');
                    }
                },
                select: function (event, ui) {
                    event.preventDefault();
                    if (ui.item.id !== 0) {
                        var parent = $(this).parent().parent();
                        parent.find(".combo_product_id").val(ui.item.id);
                        parent.find(".combo_name").val(ui.item.name);
                        parent.find(".combo_code").val(ui.item.code);
                        parent.find(".combo_price").val(formatDecimal(ui.item.price));
                        parent.find(".combo_qty").val(formatDecimal(1));
                        if (site.settings.qty_operation == 1) {
                            parent.find(".combo_width").val(formatDecimal(1));
                            parent.find(".combo_height").val(formatDecimal(1));
                        }                   
                        $(this).val(ui.item.label);
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
         
    });
</script>