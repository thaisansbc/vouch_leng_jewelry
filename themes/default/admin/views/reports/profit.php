<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('today_sales') . ' (' . $this->bpas->hrsd($date) . ')'; ?></h4>
        </div>
        <div class="modal-body">
            <!-- <p><?= lang('unit_and_net_tip'); ?></p> -->
            <div class="form-group no-print">
                <?php
                $total_tax = $vat ? $vat->total_tax : 0; 
                $discount = $discount ? $discount->order_discount : 0; 
                $total_discount = $total_discount ? $total_discount->total_discount : 0; 

                $opts[] = lang('all_warehouses');
                foreach ($warehouses as $warehouse) {
                    $opts[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                }
                ?>
                <?= form_dropdown('warehouse', $opts, set_value('warehouse', $swh), 'class="form-control select" id="warehouse"'); ?>
            </div>
            <div class="table-responsive">
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('gross_sales'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($net_sale->total + $total_discount); ?></span></h4>
                            <!-- <span><?= $this->bpas->formatMoney($costing->sales) . ' (' . $this->bpas->formatMoney($costing->net_sales) . ')'; ?></span></h4> -->
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('order_discount'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php //echo $this->bpas->formatMoney($discount); 
                            echo $this->bpas->formatMoney($total_discount);
                            ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($net_sale->total); ?></span></h4>
                            <!-- <span><?= $this->bpas->formatMoney($costing->sales) . ' (' . $this->bpas->formatMoney($costing->net_sales) . ')'; ?></span></h4> -->
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;padding-left: 20px;"><h4><?= lang('net_sales'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($net_sale->total - $total_tax); ?></span></h4>
                            <!-- <span><?= $this->bpas->formatMoney($costing->sales) . ' (' . $this->bpas->formatMoney($costing->net_sales) . ')'; ?></span></h4> -->
                    </td>
                </tr>
                 <tr>
                    <td style="border-bottom: 1px solid #DDD;padding-left: 20px;"><h4><?= lang('vat'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php 
                            echo $this->bpas->formatMoney($total_tax); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><strong><h4><?= lang('payments'); ?>:</h4></strong></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"> </td>
                </tr>
                
                <?php 
                foreach ($paid_by as $value) {
                    $paid = $this->site->getpaid_by($value->code,$date,$swh);
                    if (!empty($paid->paid)) {
                        ?>
                            <tr>
                                <td style="border-bottom: 1px solid #EEE;padding-left: 20px;"><h4><?= lang($value->code); ?>:</h4></td>
                                <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                                        <span><?= $this->bpas->formatMoney($paid->paid); ?></span></h4>
                                </td>
                            </tr>
                        <?php
                    }
                }
               ?>

                <tr class="hide">
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('products_cost'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->bpas->formatMoney($costing->cost); ?></span>
                            <!-- <span><?= $this->bpas->formatMoney($costing->cost) . ' (' . $this->bpas->formatMoney($costing->net_cost) . ')'; ?></span> -->
                        </h4></td>
                </tr>
                <tr class="hide">
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->bpas->formatMoney($expense); ?></span>
                        </h4></td>
                </tr>
                <tr class="hide">
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('profit'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <span><strong><?= $this->bpas->formatMoney($costing->sales - $costing->cost - $discount - $expense); ?></strong></span>
                            <!-- <span><strong><?= $this->bpas->formatMoney($costing->sales - $costing->cost - $discount - $expense) . ' (' . $this->bpas->formatMoney($costing->net_sales - $costing->net_cost - $discount - $expense) . ')'; ?></strong></span> -->
                        </h4></td>
                </tr>
                <?php if (isset($returns->total)) {
                    ?>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('return_sales'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <span><strong><?= $this->bpas->formatMoney($returns->total); ?></strong></span>
                        </h4></td>
                </tr>
                <?php
                } ?>
            </table>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        $('#warehouse').select2({minimumResultsForSearch: 7});
        $('#warehouse').change(function(e) {
            var wh = $(this).val();
            $.get('<?= admin_url('reports/profit/' . $date); ?>/'+wh+'/1', function(data) {
                $('#myModal').empty().html(data);
                $('#warehouse').select2({minimumResultsForSearch: 7});
            });
        });
    });
</script>
