<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1;
    var type_opt = {
        'addition': '<?= lang('return_from_supplier'); ?>',
        'subtraction': '<?= lang('back_to_customer'); ?>'
    };
    $(document).ready(function() {
        if (localStorage.getItem('remove_qals')) {
            if (localStorage.getItem('qaitems')) {
                localStorage.removeItem('qaitems');
            }
            if (localStorage.getItem('qaref')) {
                localStorage.removeItem('qaref');
            }
            if (localStorage.getItem('qawarehouse')) {
                localStorage.removeItem('qawarehouse');
            }
            if (localStorage.getItem('qanote')) {
                localStorage.removeItem('qanote');
            }
            if (localStorage.getItem('qadate')) {
                localStorage.removeItem('qadate');
            }
            localStorage.removeItem('remove_qals');
        }
  
        <?php if ($warehouse_id) { ?>
            localStorage.setItem('qawarehouse', '<?= $warehouse_id; ?>');
            $('#qawarehouse').select2('readonly', true);
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('qadate')) {
                $("#qadate").datetimepicker({
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
            }
            $(document).on('change', '#qadate', function(e) {
                localStorage.setItem('qadate', $(this).val());
            });
            if (qadate = localStorage.getItem('qadate')) {
                $('#qadate').val(qadate);
            }
        <?php } ?>

        $(".item:not(.ui-autocomplete-input)").live("focus", function (event) {
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
                        var ring_change     = parseFloat($("#ring_change").val());
                        var money_change    = formatDecimal($("#money_change").val());
                        var result_change   = parseFloat($("#result_change").val());

                        var set             =  parseFloat($("#set").val());

                        var parent = $(this).parent().parent();
                        parent.find(".item_id").val(ui.item.id);
                        $(this).val(ui.item.label);
                        $("#item_qty").val(set * ring_change);
                        $("#money").val(set * money_change);
                        $("#result_qty").val(set * result_change);

                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
        $(".item_result:not(.ui-autocomplete-input)").live("focus", function (event) {
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
                        parent.find(".item_result_id").val(ui.item.id);
                        $(this).val(ui.item.label);
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
        
        $("#set").change(function(){
            var set = $(this).val();

            var ring_change     = parseFloat($("#ring_change").val());
            var money_change    = formatDecimal($("#money_change").val());
            var result_change   = parseFloat($("#result_change").val());

            $("#item_qty").val(set * ring_change);
            $("#money").val(set * money_change);
            $("#result_qty").val(set * result_change);


        })
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('reward_ring_can'); ?></h2>
</div>
<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/reward_ring' . ($count_id ? '/' . $count_id : ''), $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'qadate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control input-tip datetime" id="qadate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'qaref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="qaref"'); ?>
                            </div>
                        </div>
                        <?php if (($Admin || $Owner) || empty($user_billers)) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'class="form-control" id="qabiller" required="required"');
                                    ?>
                                </div>
                            </div>
                        <?php } elseif (count($user_billers) > 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    foreach ($billers as $biller) {
                                        foreach ($user_billers as $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'class="form-control" id="qabiller" required="required"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = [
                                'type'  => 'hidden',
                                'name'  => 'biller',
                                'id'    => 'qabiller',
                                'value' => $user_billers[0],
                            ];
                            echo form_input($biller_input);
                        } ?>
                        <?= form_hidden('count_id', $count_id); ?>
                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warehouse', 'qawarehouse'); ?>
                                    <div class="input-group" style="width:100%">
                                        <?php
                                        $wh[''] = '';
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ($warehouse_id ? $warehouse_id : $Settings->default_warehouse)), 'id="qawarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" ' . ($warehouse_id ? 'readonly' : '') . ' style="width:100%;"'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } else {
                            $warehouse_input = [
                                'type'  => 'hidden',
                                'name'  => 'warehouse',
                                'id'    => 'qawarehouse',
                                'value' => $this->session->userdata('warehouse_id'),
                            ];
                            echo form_input($warehouse_input);
                        } ?>
                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <?php if($Settings->module_account){ ?>
                        <div class="col-md-4 hide">
                            <div class="form-group company">
                                <label><?= lang("chart_account", "chart_account"); ?> *</label>
                                <?php
                                $acc_section = array(""=>"");
                                foreach($sectionacc as $section){
                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                }
                                echo form_dropdown('default_cost', $acc_section, $this->accounting_setting->default_cost, 'id="default_cost" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <?php }?>
                
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('ring_change', 'ring_change'); ?>
                                    <?php echo form_input('ring_change', (isset($_POST['ring_change']) ? $_POST['ring_change'] : 23), 'class="ring_change form-control input-tip" id="ring_change" readonly'); ?>
                                    
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('money_change', 'money_change'); ?>
                                    <?php echo form_input('money_change', (isset($_POST['money_change']) ? $_POST['money_change'] : 1.25), 'class="money_change form-control input-tip" id="money_change" readonly'); ?>
                                    
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('result_change', 'result_change'); ?>
                                    <?php echo form_input('result_change', (isset($_POST['result_change']) ? $_POST['result_change'] : 24), 'class="result_change form-control input-tip" id="result_change" readonly'); ?>
                                    
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="well well-sm">
                                

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang('item', 'item'); ?>
                                        <?php echo form_input('item', (isset($_POST['item']) ? $_POST['item'] : ''), 'class="item form-control input-tip" id="item" required'); ?>
                                        <?php echo form_hidden('item_id','', 'class="item_id form-control input-tip" id="item_id" required'); ?>
                                        
                                    </div>
                                    <div class="form-group">
                                        <?= lang('item_qty', 'item_qty'); ?>
                                        <?php echo form_input('item_qty', 0, 'class="item_qty form-control input-tip" id="item_qty" required'); ?>
                                        
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="plus">&nbsp;</label>
                                        <i class="fa-regular fa fa-plus-circle"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                               
                                    <div class="form-group">
                                        <?= lang('set', 'set'); ?>
                                        <?php echo form_input('set',1, 'class="set form-control input-tip" id="set"'); ?>
                                        
                                    </div>
                    
                                    <div class="form-group">
                                        <?= lang('money', 'money'); ?>
                                        <?php echo form_input('money', (isset($_POST['money']) ? $_POST['money'] : 0), 'class="form-control input-tip" id="money" required'); ?>
                                    </div>
                                </div>
                                <div class="col-md-1">=</div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang('item_result', 'item_result'); ?>
                                        <?php echo form_input('item_result', (isset($_POST['item_result']) ? $_POST['item_result'] : ''), 'class="form-control input-tip item_result" id="item_result" required'); ?>
                                        <?php echo form_hidden('item_result_id','', 'class="item_result_id form-control input-tip" id="item_result_id" required'); ?>
                                    </div>
                                    <div class="form-group">
                                        <?= lang('result_qty', 'result_qty'); ?>
                                        <?php echo form_input('result_qty',0, 'class="result_qty form-control input-tip" id="result_qty" required'); ?>
                                        
                                    </div>
                                </div>
                                 <div class="clearfix"></div>
                            </div>
                        </div>
                      
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang('note', 'qanote'); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="qanote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="fprom-group"><?php echo form_submit('add_adjustment', lang('submit'), 'id="add_adjustment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>