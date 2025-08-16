<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('transfer_account'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('account/tansfer/' . $inv->id, $attrib); ?>
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
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>

                <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang('amount', 'amount_1'); ?>
                                        <input name="amount-paid" type="text" id="amount_1"
                                               value="<?= $this->bpas->formatDecimal($inv->amount); ?>"  readonly="readonly"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                    <input name="paid_by" type="text" readonly="readonly" value="<?= lang($inv->paid_by); ?>"
                                               class="form-control"/>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <h4 class="modal-title text-center"><?php echo lang('transfer_to'); ?></h4>
                            </div>
                            <?php 
                            if($this->Settings->accounting) {
                                $bankAccounts =  $this->site->getAllBankAccounts();
                            ?>
                            <div class="col-sm-6" id="bank_acc1">
                                <div class="form-group">
                                    <?= lang("bank_charge", "bank_charge"); ?>
                                    <?php
                                        $bank = array('' => '-- Select Bank Account --');
                                        foreach($ExpenseAccounts as $bankAcc1) {
                                            $bank[$bankAcc1->accountcode] = $bankAcc1->accountcode . ' | '. $bankAcc1->accountname;
                                        }
                                        echo form_dropdown('bank_charge', $bank, '', 'id="bank_charge" class="ba form-control kb-pad" required="required" data-bv-notempty="true"');
                                       
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang('charge_amount', 'amount_1'); ?>
                                        <input name="bank_charge_amount" type="text" id="bank_charge_amount" value="0" 
                                               class="form-control" required="required"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6" id="bank_acc">
                                <div class="form-group">
                                    <?= lang("bank_account", "bank_account_1"); ?>
                                    <?php
                                        $bank = array('' => '-- Select Bank Account --');
                                        foreach($bankAccounts as $bankAcc) {
                                            $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                                        }
                                        echo form_dropdown('bank_account', $bank, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" required="required" data-bv-notempty="true"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang('transfer_amount', 'amount_1'); ?>
                                        <input name="bank_account_amount" type="text" value="<?= $this->bpas->formatDecimal($inv->amount); ?>" class="pa form-control kb-pad amount" id="bank_account_amount" readyonly="readyonly" required="required"/>

                                        <input type="hidden" value="<?= $this->bpas->formatDecimal($inv->amount); ?>" id="bank_total_amount"/>
                                    </div>
                                </div>
                            </div>

                            

                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group gc" style="display: none;">
                            <?= lang('gift_card_no', 'gift_card_no'); ?>
                            <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad"/>

                            <div id="gc_details"></div>
                        </div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control"
                                               placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">

                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control"
                                               placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                                placeholder="<?= lang('card_type') ?>">
                                            <option value="Visa"><?= lang('Visa'); ?></option>
                                            <option value="MasterCard"><?= lang('MasterCard'); ?></option>
                                            <option value="Amex"><?= lang('Amex'); ?></option>
                                            <option value="Discover"><?= lang('Discover'); ?></option>
                                        </select>
                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control"
                                               placeholder="<?= lang('month') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control"
                                               placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
                                <!--<div class="col-md-3">
                                    <div class="form-group">
                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                    </div>
                                </div>-->
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang('cheque_no', 'cheque_no_1'); ?>
                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                            </div>
                        </div>
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
