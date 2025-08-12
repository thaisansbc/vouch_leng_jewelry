<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1,  DT = <?= $Settings->default_tax_rate ?>, invoice_tax = 0, total = 0, tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if($this->session->userdata('remove_expls')) { ?>
            removeLS();
        <?php $this->bpas->unset_data('remove_expls'); } ?>
        if(localStorage.getItem('remove_expls')){
            removeLS();
            localStorage.removeItem('remove_expls');
        }
        <?php if ($request) { ?>
            localStorage.setItem('expdate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($request->date))?>');
            localStorage.setItem('expbiller', '<?=$request->biller_id?>');
            localStorage.setItem('expsupplier', '<?=$request->supplier_id?>');
            localStorage.setItem('expwarehouse', '<?=$request->warehouse_id?>');
            localStorage.setItem('expnote', '<?= str_replace(array("\r", "\n", "'"), "", $this->bpas->decode_html($request->note)); ?>');
            localStorage.setItem('exptax2', '<?=$request->order_tax_id?>');
            localStorage.setItem('exdiscount', '<?=$request->order_discount_id?>');
            localStorage.setItem('expproject', '<?=$request->project_id?>');
            localStorage.setItem('expvehicle', '<?=$request->vehicle_id?>');
            localStorage.setItem('expitems', JSON.stringify(<?=$request_items?>));
        <?php } ?>
        
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('expdate')) {
                $("#expdate").datetimepicker({
                    <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
                    fontAwesome: true,
                    language: 'bpas',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
            $(document).on('change', '#expdate', function (e) {
                localStorage.setItem('expdate', $(this).val());
            });
            if (expdate = localStorage.getItem('expdate')) {
                $('#expdate').val(expdate);
        }
        <?php } ?>
        $(document).on('change', '#expbiller', function (e) {
            localStorage.setItem('expbiller', $(this).val());
        });
        if (expbiller = localStorage.getItem('expbiller')) {
            $('#expbiller').val(expbiller);
        }
        if (!localStorage.getItem('exptax2')) {
            localStorage.setItem('exptax2', <?=$Settings->default_tax_rate2;?>);
        }

        
    });
</script>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_expense'); ?></h4>
        </div>
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("expenses/add/".($request ? $request->id : ''), $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
            <div class="col-md-12">
                <div class="row">
                    <?php 
                    if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("date", "expdate"); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="expdate" required="required"'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang("reference_no", "expref"); ?>
                            <?php echo form_input('reference_no', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control input-tip" id="expref"'); ?>
                        </div>
                    </div>
                    <?php if($this->config->item('expense_request')) {  ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("expense_request", "expense_request"); ?>
                                <?php
                                $exr_opt[""] =  lang('select').' '.lang('expense_request');
                                if($expense_requests){
                                    foreach ($expense_requests as $expense_request) {
                                        $exr_opt[$expense_request->id] = $expense_request->reference;
                                    }
                                }
                                
                                echo form_dropdown('expense_request', $exr_opt, ($request ? $request->id : ''), 'id="expense_request" class="form-control input-tip select" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    <?php } if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("biller", "expbiller"); ?>
                                <?php
                                $bl[""] = lang("select").' '.lang("biller");
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="expbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } else {
                        $biller_input = array(
                            'type' => 'hidden',
                            'name' => 'biller',
                            'id' => 'expbiller',
                            'value' => $this->session->userdata('biller_id'),
                        );
                        echo form_input($biller_input);
                    } ?>
                    <?php if($Settings->project == 1){ ?>
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                     
                                    <?php
                                    $pj[''] = '';
                                    if($projects){
                                        foreach ($projects as $project) {
                                            $pj[$project->project_id] = $project->project_name;
                                        }
                                    }
                                    echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->default_project), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                    ?>
                                 
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = ''; 
                                        if(isset($user) && isset($projects) && $projects){
                                            $right_project = json_decode($user->project_ids);
                                            foreach ($projects as $project) {
                                                if(in_array($project->project_id, $right_project)){
                                                    $pj[$project->project_id] = $project->project_name;
                                                }
                                            }
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->default_project), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang("warehouse", "expwarehouse"); ?>
                            <?php
                            $wh[''] = $this->lang->line("select") . ' ' . $this->lang->line("warehouse");
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->Settings->default_warehouse), 'id="expwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" style="width:100%;" ');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel panel-warning">
                    <div class="panel-heading"><?= lang('please_select_these_before_adding_expense') ?></div>
                    <div class="panel-body" style="padding: 5px;">
                        <?php if($Settings->payment_expense==1){ ?>
                            <div class="col-md-4" style="margin-bottom: 13px;">
                                <div class="form-group">
                                    <?= lang("supplier", "expsupplier"); ?>
                                    <input type="hidden" name="supplier" value="" id="expsupplier" required class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id" required class="form-control">
                                </div>
                            </div>
                            <?php if($Settings->module_account==1){?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("payable_account", "payable_account"); ?>
                                    <?php
                                    $bank = array('0' => '-- Select Bank Account --');
                                    foreach ($bankAccounts as $bankAcc) {
                                        $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
                                    }
                                    echo form_dropdown('payable_account', $bank, '', 'class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
                                    ?>
                                </div>
                            </div>
                           <?php } 
                        }else{ ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                        <?= $this->bpas->paid_opts(false,true,false,true); ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12" id="sticker">
                <div class="well well-sm">
                    <div class="form-group" style="margin-bottom:0;">
                        <div class="input-group wide-tip">
                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_expense_to_order") . '"'); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="control-group table-group">
                    <label class="table-label"><?= lang("order_items"); ?> *</label>

                    <div class="controls table-controls">
                        <table id="expTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th class="col-md-4"><?= lang('expense') ?></th>
                                <th class="col-md-4"><?= lang("description"); ?></th>
                                <th class="col-md-1"><?= lang("unit_cost"); ?></th>
                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                <th><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)
                                <th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
            
            <?php if ($Settings->tax2) { ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang("order_tax", "exptax2"); ?>
                        <?php
                        $tr[""] = "";
                        foreach ($tax_rates as $tax) {
                            $tr[$tax->id] = $tax->name;
                        }
                        echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="exptax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-4">
                <div class="form-group">
                    <?= lang("discount_label", "exdiscount"); ?>
                    <?php echo form_input('discount', '', 'class="form-control input-tip" id="exdiscount"'); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <?= lang("document", "document") ?>
                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                           data-show-preview="false" class="form-control file">
                </div>
            </div>
            
            <div class="row" id="bt">
                <div class="col-sm-12">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?= lang("note", "expnote"); ?>
                            <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="expnote" style="margin-top: 10px; height: 100px;"'); ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer">
            <div class="fprom-group">
                <?php echo form_submit('add_expense', $this->lang->line("submit"), 'id="add_expense" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/expenses.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'bpas',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
        //-----------------
        $("#add_item").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('expenses/suggestion_expenses'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term
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
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>