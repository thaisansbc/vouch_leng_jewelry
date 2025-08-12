<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_deposit') . ' (' . $company->name . ')'; ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('customers/edit_deposit/' . $deposit->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php 
                    $ex_rate_usd = !empty($currency_dollar) ? $currency_dollar->rate : 0;
                    $ex_rate_khr = !empty($currency_riel) ? $currency_riel->rate : 0;
                    $ex_rate_thb = !empty($currency_baht) ? $currency_baht->rate : 0;

                    $code_usd = !empty($currency_dollar) ? $currency_dollar->code : '';
                    $code_khr = !empty($currency_riel) ? $currency_riel->code : '';
                    $code_thb = !empty($currency_baht) ? $currency_baht->code : '';
                ?>
                    <div class="col-sm-12">
                        <?php if ($Owner || $Admin) { ?>
                        <div class="form-group">
                            <?php echo lang('date', 'date'); ?>
                            <div class="controls">
                                <?php echo form_input('date', set_value('date', $this->bpas->hrld($deposit->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang(lang('deposit') . ' ' . lang('dollar') . ' (' . $code_usd . ') <span style="color: red; opacity: 0.8;">*' . lang('rate') . ' : ' . $ex_rate_usd . '</span>', 'amount_usd'); ?>
                            <div class="controls">
                                <?php echo form_input('amount_usd', set_value('amount_usd', $deposit->amount_usd), 'class="form-control amount" id="amount_usd" '); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang(lang('deposit') . ' ' . lang('riel') . ' (' . $code_khr . ') <span style="color: red; opacity: 0.8;">*' . lang('rate') . ' : ' . $ex_rate_khr . '</span>', 'amount_khr'); ?>
                            <div class="controls">
                                <?php echo form_input('amount_khr', set_value('amount_khr', $deposit->amount_khr), 'class="form-control amount" id="amount_khr" '); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang(lang('deposit') . ' ' . lang('baht') . ' (' . $code_thb . ') <span style="color: red; opacity: 0.8;">*' . lang('rate') . ' : ' . $ex_rate_thb . '</span>', 'amount_thb'); ?>
                            <div class="controls">
                                <?php echo form_input('amount_thb', set_value('amount_thb', $deposit->amount_thb), 'class="form-control amount" id="amount_thb" '); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('total_deposit_amount', 'amount') . ' (USD)'; ?>
                            <div class="controls">
                                <?php echo form_input('amount', set_value('amount', $this->bpas->formatMoney($deposit->amount)), 'class="form-control amount" id="amount" required="required" readonly'); ?>
                                <input type="hidden" name="khr_rate" value="<?= $ex_rate_khr ?>">
                                <input type="hidden" name="thb_rate" value="<?= $ex_rate_thb ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('paid_by', 'paid_by'); ?>
                            <div class="controls">
                                <select name="paid_by" id="paid_by" class="form-control paid_by" required="required">
                                    <?= $this->bpas->paid_opts($deposit->paid_by); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('note', 'note'); ?>
                            <div class="controls">
                                <?php echo form_textarea('note', $deposit->note, 'class="form-control" id="note"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_deposit', lang('edit_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('keypress', '.amount', function(){
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        })
        $(document).on('focus', '.amount', function () {
            $(this).select();
        });
        $(document).on('focusout', '.amount', function () {
            if($(this).val() == '' || $(this).val() < 0){
                $(this).val('0.00');
            }
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });
        $(document).on('change', '.amount', function () {
            var khr_rate   = parseFloat($('input[name="khr_rate"]').val());
            var thb_rate   = parseFloat($('input[name="thb_rate"]').val());
            var amount_usd = parseFloat($('input[name="amount_usd"]').val());
            var amount_khr = parseFloat($('input[name="amount_khr"]').val());
            var amount_thb = parseFloat($('input[name="amount_thb"]').val());
            var amount     = amount_usd + (amount_khr / khr_rate) + (amount_thb / thb_rate);
            $('input[name="amount"]').val(parseFloat(amount).toFixed(2));
        });
    });
</script>