<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content" style="width:750px;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_downpayment'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('sales/add_downpayment/'.$sale_id.'/'.$product_id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php //if ($Owner || $Admin) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php //} ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('balance_amount', 'payable'); ?>
                        <input name="total_payable_usd" type="text" readonly id="total_payable_usd" value="<?= $this->bpas->formatDecimal($balance); ?>" class="pa form-control kb-pad amount" required="required"/>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $sale_id; ?>" name="sale_id"/>
                <input type="hidden" value="<?php echo $product_id; ?>" name="product_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">
   
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('title', 'title'); ?>
                                    <?php echo form_input('title', (isset($_POST['title']) ? $_POST['title'] : ''), 'class="form-control" id="title"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang(lang('amount'), 'amount'); ?>
                                    <input name="amount" type="text" id="amount" value="" data="" class="pa form-control kb-pad amount"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="type"><?= lang('type'); ?></label>
                                    <?php
                                    $type = [
                                        'down_payment'  => lang('down_payment'),
                                        'installment'   => lang('installment'),
                                        'deposit'       => lang('deposit')
                                    ];
                                    echo form_dropdown('type', $type,'', 'class="form-control" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('payment_date', 'payment_date'); ?>
                                    <?= form_input('payment_date', (isset($_POST['payment_date']) ? $_POST['payment_date'] : ''), 'class="form-control date" id="payment_date" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" data="" required="required">
                                        <?= $this->bpas->paid_opts(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang('noted', 'noted'); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('Add_Downpayment', lang('Add_Downpayment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    $('#MyModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    })
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
</script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>