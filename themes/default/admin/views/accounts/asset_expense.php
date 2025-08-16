<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('assets_expense'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('account/asset_expense/' . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php 
                //if ($Owner || $Admin) {
                ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d H:i:s')), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php
                //} 
                ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('reference_no', 'reference_no'); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $depreciation_ref), 'class="form-control tip" readonly="readonly" id="reference_no"'); ?>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    
                                    echo form_dropdown('biller_id', $bl, (isset($inv->biller_id) ? $inv->biller_id : $this->site->get_setting()->default_biller), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang('amount', 'amount_1'); ?>
                                        <input name="amount" type="text" id="amount_1"
                                               value="<?= $this->bpas->formatDecimal($inv->current_cost); ?>"  readonly="readonly"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
                       
                            <div class="col-sm-12">
                                <h4 class="modal-title text-center"></h4>
                            </div>
                            <?php 
                            if($this->Settings->accounting) {  
                            ?>
                            <div class="col-sm-6" id="bank_acc">
                                <div class="form-group">
                                    <?= lang("asset_account", "bank_account_1"); ?>
                                    <?php
                                   // $bankAccounts =  $this->site->getAllBankAccounts();
                                        $bank1 = array('' => '-- Select Bank Account --');
                                        foreach($sectionacc as $bankAcc) {
                                            $bank1[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                                        }
                                        echo form_dropdown('asset_account', $bank1, $inv->asset_account, 'id="bank_account_1" class="ba form-control kb-pad bank_account" required="required" data-bv-notempty="true"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-6" id="bank_acc1">
                                <div class="form-group">
                                    <?= lang("ExpenseAccounts", "bank_charge"); ?>
                                    <?php
                                        $bank = array('' => '-- Select Bank Account --');
                                        foreach($sectionacc as $bankAcc1) {
                                            $bank[$bankAcc1->accountcode] = $bankAcc1->accountcode . ' | '. $bankAcc1->accountname;
                                        }
                                        echo form_dropdown('expense_account', $bank, $inv->expense_account, 'id="bank_charge" class="ba form-control kb-pad" required="required" data-bv-notempty="true"');
                                       
                                    ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                      
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="form-group">
                <?= lang('attachment', 'attachment') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        

        $(document).on('change', '#bank_charge_amount', function () {

            var bank_charge_amount = $(this).val();
            var bank_account_amount = $("#bank_total_amount").val();

            $("#bank_account_amount").val(bank_account_amount - bank_charge_amount);
        }); 
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date()); 
    });
</script>
