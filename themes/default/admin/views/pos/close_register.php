<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    td{
        padding: 0 5px;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title"
                id="myModalLabel"><?= lang('close_register') . ' (' . $this->bpas->hrld($register_open_time ? $register_open_time : $this->session->userdata('register_open_time')) . ' - ' . $this->bpas->hrld(date('Y-m-d H:i:s')) . ')'; ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('pos/close_register/' . $user_id, $attrib);
        ?>
        <div class="modal-body">
            <div id="alerts"></div>
            <p><?= lang('register_total_tip'); ?></p>
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sale'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($cashsales->paid ? $cashsales->paid : '0.00')?></span>
                        </h4></td>
                </tr>
                <?php
                /*
                foreach($payments as $payment){
                 
                ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang($payment->name); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($payment->paid); ?></span>
                        </h4></td>
                </tr>
                <?php
                }*/
                ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('ABA_payment'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($abasales->paid ? $abasales->paid : '0.00'); ?></span>
                        </h4></td>
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Acleda_payment'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($acledasales->paid ? $acledasales->paid : '0.00') ?></span> 
                        </h4></td>
                </tr>
            <?php if ($alipay->paid > 0) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Alipay'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($alipay->paid ? $alipay->paid : '0.00') ?></span>
                        </h4></td>
                </tr>
            <?php } ?>

            <?php if ($pipay->paid > 0) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Pipay'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($pipay->paid ? $pipay->paid : '0.00')?></span>
                        </h4></td>
                </tr>
            <?php } ?> 

            <?php if ($wing->paid > 0) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Wing'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($wing->paid ? $wing->paid : '0.00') ?></span>
                        </h4></td>
                </tr>
            <?php } ?> 

            <?php if ($other->paid > 0) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Other'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($other->paid ? $other->paid : '0.00') ?></span>
                        </h4></td>
                </tr>
            <?php } ?> 
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('ch_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($chsales->paid ? $chsales->paid : '0.00') ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($ccsales->paid ? $ccsales->paid : '0.00')?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('gc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?= $this->bpas->formatMoney($gcsales->paid ? $gcsales->paid : '0.00') ?></span>
                        </h4></td>
                </tr>
                <?php if ($this->pos_settings->paypal_pro) {
                    ?>
                    <tr>
                        <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('paypal_pro'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                                <span><?= $this->bpas->formatMoney($pppsales->paid ? $pppsales->paid : '0.00') ?></span>
                            </h4></td>
                    </tr>
                <?php
        } ?>
                <?php if ($this->pos_settings->stripe) {
            ?>
                    <tr>
                        <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('stripe'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                                <span><?= $this->bpas->formatMoney($stripesales->paid ? $stripesales->paid : '0.00') ?></span>
                            </h4></td>
                    </tr>
                <?php
        } ?>
                <?php if ($this->pos_settings->authorize) {
            ?>
                    <tr>
                        <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('authorize'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                                <span><?= $this->bpas->formatMoney($authorizesales->paid ? $authorizesales->paid : '0.00')?></span>
                            </h4></td>
                    </tr>
                <?php
        } ?>  
        <tr>
                    <td width="300px;" style="border-bottom: 1px solid #DDD;font-weight:bold;"><h4><?= lang('total_order_tax'); ?>:</h4></td>
                    <td width="200px;" style="border-bottom: 1px solid #DDD;font-weight:bold;text-align:right;"><h4>
                            <span><?= $this->bpas->formatMoney($totalsales->tax ? $totalsales->tax : '0.00'); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td width="300px;" style="border-bottom: 1px solid #DDD;font-weight:bold;"><h4><?= lang('total_order_discount'); ?>:</h4></td>
                    <td width="200px;" style="border-bottom: 1px solid #DDD;font-weight:bold;text-align:right;"><h4>
                            <span><?= $this->bpas->formatMoney($totalsales->discount ? $totalsales->discount : '0.00'); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Total Transaction'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatDecimal($totalreceipt->total_trans ? $totalreceipt->total_trans : '0', 0); ?></span></h4>
                    </td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
                            <span><?= $this->bpas->formatMoney($totalsales->paid ? $totalsales->paid : '0.00')?></span>
                        </h4></td>
                </tr>
              <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('refunds'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->bpas->formatMoney($refunds->returned ? $refunds->returned : '0.00') ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('returns'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->bpas->formatMoney($returns->total ? '-' . $returns->total : '0.00'); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->bpas->formatMoney($expense)?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;">
                        <h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;">
                        <h4>
                            <?php if ($this->pos_settings->allow_total_cash_in_hand == 1) { ?>
                                <?php $total_cash_amount = $cashsales->paid ? (($cashsales->paid + ($this->session->userdata('cash_in_hand'))) + ($cashrefunds->returned ? $cashrefunds->returned : 0) - ($returns->total ? $returns->total : 0) - $expense) : ($this->session->userdata('cash_in_hand') - $expense - ($returns->total ? $returns->total : 0)); ?>
                            <?php } else { ?>
                                <?php $total_cash_amount = $cashsales->paid ? (($cashsales->paid) + ($cashrefunds->returned ? $cashrefunds->returned : 0) - ($returns->total ? $returns->total : 0) - $expense) : ($this->session->userdata('cash_in_hand') - $expense - ($returns->total ? $returns->total : 0)); ?>
                            <?php } ?>
                            <span><strong><?= $this->bpas->formatMoney($total_cash_amount); ?></strong></span>
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;">
                        <?php if ($this->pos_settings->allow_total_cash_in_hand == 0) { ?>
                            <h4><?= lang('note');?>* : <?= lang('without_cash_in_hand'); ?></h4>
                            

                        <?php } ?>

                    </td>

                </tr>
            </table>
        <?php if($settings->default_currency != "KHM"){ $currency = "$";
                }else{ $currency = "$"; }?>
                <div class="table-responsive print_forms" style="display:none;">
                    <table style="width:100%">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                <th class="print_form"><?= lang('tax'); ?></th>
                                <th class="print_form"><?= lang('dis'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                                    $totalsqty =[];
                                    $totalsSale = 0;
                                    $totalsTax = 0;
                                    $totalsDiscount = 0;
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr class="border-0">
                                    <th colspan="5" class="border-bottom"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                $totalqty = 0;
                                $totalSale = 0;
                                $totalTax = 0;
                                $totalDiscount = 0;
                                $getProductsInOut = $this->pos_model->getProductsInOut($category->category_id);
                                $j = 1;
                                foreach ($getProductsInOut as $key => $row) {
                                ?>
                                    <tr>
                                        <!-- <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td> -->
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. '. $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity($row->soldQty); ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalTax); ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalDiscount); ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalSale); ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                     $totalTax += $row->totalTax;
                                    $totalDiscount += $row->totalDiscount;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right ">
                                       <?= lang('total'); ?>:&nbsp;&nbsp; </th>
                                    <th>
                                       <?= $this->bpas->formatQuantity($totalqty); ?></th>
                                       <th class=""> <?= lang('tax'); ?></th>
                                       <th class=""> <?= lang('dis'); ?></th>
                                    <th class=""><?= $currency; ?><?= $this->bpas->formatMoney($totalSale); ?></th>
                                </tr>
                                <?php $totalsqtys[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsSales[] = $this->bpas->formatMoney($totalSale); ?>
                                <?php $totalsTax[] = $this->bpas->formatMoney($totalTax); ?>
                                <?php $totalsDiscount[] = $this->bpas->formatMoney($totalDiscount); ?>
                            <?php
                                $i++;
                            }
                            ?>
                            <tr class="active">
                                <th class="text-right "><?= lang('TOTAL'); ?>:&nbsp;&nbsp;</th>
                                <th><?php echo array_sum($totalsqtys); ?></th>
                                <th class=""> <?= lang('tax'); ?></th>
                                       <th class=""> <?= lang('dis'); ?></th>
                                <th><?= $currency; ?><?php echo array_sum($totalsSales); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php if($this->pos_settings->show_close_register_products){?>
                <div class="table-responsive form_org">
                    <table class="table table-striped table-bordered table-condensed reports-table" style="margin-bottom:5px;">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                 <th class="print_form"><?= lang('tax'); ?></th>
                                  <th class="print_form"><?= lang('dis'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $i = 1;
                            $totalsqty = [];
                            $totalsSale = 0;
                            $totalsTax = 0;
                            $totalsDiscount = 0;
                        if($getcategoryInOut){
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr>
                                    <th colspan="5"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                
                                $totalqty = 0;
                                $totalSale = 0;
                                $totalTax = 0;
                                $totalDiscount = 0;
                                $getProductsInOut = $this->pos_model->getProductsInOut($category->category_id);
                                $j = 1;
                                foreach ($getProductsInOut as $key => $row) {

                                ?>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity(($row->soldQty)) ?></td>
                                        <td><?=$currency. $this->bpas->formatMoney($row->totalTax) ?></td>
                                        <td><?=$currency. $this->bpas->formatMoney($row->totalDiscount) ?></td>
                                        <td><?=$currency. $this->bpas->formatMoney($row->totalSale) ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                     $totalTax += $row->totalTax;
                                    $totalDiscount += $row->totalDiscount;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right"><?= lang('subtotal'); ?></th>
                                    <th><?= $this->bpas->formatQuantity($totalqty); ?></th>
                                    <th><?=$currency. $this->bpas->formatMoney($totalTax); ?></th>
                                    <th><?=$currency. $this->bpas->formatMoney($totalDiscount); ?></th>
                                    <th><?=$currency. $this->bpas->formatMoney($totalSale); ?></th>
                                </tr>
                                <?php $totalsqty[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsTax += $totalTax; ?>
                                <?php $totalsDiscount += $totalDiscount; ?>
                                <?php $totalsSale += $totalSale; ?>
                            <?php
                                $i++;
                            }
                        }
                            ?>
                            <tr class="active">
                                <th class="text-right"><?= lang('TOTAL'); ?></th>
                                <th><?php echo array_sum($totalsqty); ?></th>
                                <th><?php echo $currency.($totalsTax); ?></th>
                                <th><?php echo $currency.($totalsDiscount); ?></th>
                                <th><?php echo $currency.$totalsSale; ?></th>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <?php }?>
            <?php

            if ($suspended_bills) {
                echo '<hr><h3>' . lang('opened_bills') . '</h3><table class="table table-hovered table-bordered"><thead><tr><th>' . lang('customer') . '</th><th>' . lang('date') . '</th><th>' . lang('total_items') . '</th><th>' . lang('amount') . '</th><th><i class="fa fa-trash-o"></i></th></tr></thead><tbody>';
                foreach ($suspended_bills as $bill) {
                    echo '<tr><td>' . $bill->customer . '</td>
                                <td>' . $this->bpas->hrld($bill->date) . '</td>
                                <td class="text-center">' . $bill->count . '</td>
                                <td class="text-right">' . $bill->total . '</td>
                                <td class="text-center">
                                ';

                    if ($Owner || $Admin) {
                        echo '<a href="#" class="tip po" title="<b>' . $this->lang->line('delete_bill') . '</b>" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger po-delete\' href=\'' . admin_url('pos/delete/' . $bill->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i>
                                    </a>';
                    }
                    echo '</td>  
                            </tr>';
                }
                echo '</tbody></table>';
            }

            ?>
            <hr>
            <div class="row no-print">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('total_cash', 'total_cash_submitted'); ?>
                        <?= form_hidden('total_cash', $total_cash_amount); ?>
                        <?= form_input('total_cash_submitted', (isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount), 'class="form-control input-tip" id="total_cash_submitted" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('total_cheques', 'total_cheques_submitted'); ?>
                        <?= form_hidden('total_cheques', $chsales->total_cheques); ?>
                        <?= form_input('total_cheques_submitted', (isset($_POST['total_cheques_submitted']) ? $_POST['total_cheques_submitted'] : $chsales->total_cheques), 'class="form-control input-tip" id="total_cheques_submitted" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <?php if ($suspended_bills) {
                    ?>
                        <div class="form-group">
                            <?= lang('transfer_opened_bills', 'transfer_opened_bills'); ?>
                            <?php $u = $user_id ? $user_id : $this->session->userdata('user_id');
                            if ($Owner || $Admin) {
                                $usrs[-1] = lang('delete_all');
                            }
                            $usrs[0] = lang('leave_opened');
                            foreach ($users as $user) {
                                if ($user->id != $u) {
                                    $usrs[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                            } ?>
                            <?= form_dropdown('transfer_opened_bills', $usrs, (isset($_POST['transfer_opened_bills']) ? $_POST['transfer_opened_bills'] : 0), 'class="form-control input-tip" id="transfer_opened_bills" required="required"'); ?>
                        </div>
                    <?php
                    } ?>
                    <div class="form-group">
                        <?= lang('total_cc_slips', 'total_cc_slips_submitted'); ?>
                        <?= form_hidden('total_cc_slips', $ccsales->total_cc_slips); ?>
                        <?= form_input('total_cc_slips_submitted', (isset($_POST['total_cc_slips_submitted']) ? $_POST['total_cc_slips_submitted'] : $ccsales->total_cc_slips), 'class="form-control input-tip" id="total_cc_slips_submitted" required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group no-print">
                <label for="note"><?= lang('note'); ?></label>

                <div class="controls"> <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?> </div>
            </div>

        </div>
        <div class="modal-footer no-print">
            <?= form_submit('close_register', lang('close_register'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.po', function (e) {
            e.preventDefault();
            $('.po').popover({
                html: true,
                placement: 'left',
                trigger: 'manual'
            }).popover('show').not(this).popover('hide');
            return false;
        });
        $(document).on('click', '.po-close', function () {
            $('.po').popover('hide');
            return false;
        });
        $(document).on('click', '.po-delete', function (e) {
            var row = $(this).closest('tr');
            e.preventDefault();
            $('.po').popover('hide');
            var link = $(this).attr('href');
            $.ajax({
                type: "get", url: link,
                success: function (data) {
                    row.remove();
                    addAlert(data, 'success');
                },
                error: function (data) {
                    addAlert('Failed', 'danger');
                }
            });
            return false;
        });
    });
    function addAlert(message, type) {
        $('#alerts').empty().append(
            '<div class="alert alert-' + type + '">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '&times;</button>' + message + '</div>');
    }
</script>


