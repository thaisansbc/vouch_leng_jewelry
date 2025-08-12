<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css" media="all">
    table {
        font-size: 13px !important;
    }
    @media print {
        table {
            font-size: 13px !important;
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("products_expiry_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('products_expiry_report'); ?></div></center>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('product_code'); ?></th>
                        <th><?= lang('product_name'); ?></th>
                        <th style="text-align: right;"><?= lang('beginning'); ?></th>
                        <th style="text-align: right;"><?= lang('purchased'); ?></th>
                        <th style="text-align: right;"><?= lang('transfer'); ?></th>
                        <th style="text-align: right;"><?= lang('adjustment'); ?></th>
                        <th style="text-align: right;"><?= lang('sold'); ?></th>
                        <th style="text-align: right;"><?= lang('return'); ?></th>
                        <th style="text-align: right;"><?= lang('stock_in_hand'); ?></th>
                        <th style="text-align: right;"><?= lang('expiry_date'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $total_beginning = 0;
                        $total_purchased = 0;
                        $total_transfer = 0;
                        $total_adjustment = 0;
                        $total_sold = 0;
                        $total_return = 0;
                        $total_discount = 0;
                        $total_balance = 0;
                        $total_weight = 0;
                        $total_new = 0;
                        $total_old = 0;
                        $total_broken = 0;
                        $i=1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $row->code; ?></td> 
                                <td><?= $row->name; ?></td> 
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->BeginningQty); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->PurchasedQty); ?></td>
                                <td style="text-align: right;">
                                    <div><?= $this->bpas->formatDecimal($row->TransferQtyIN); ?><span style="width: 45px; display: inline-block;">(IN)</span></div>
                                    <div style="color: red;"><?= $this->bpas->formatDecimal($row->TransferQtyOUT); ?><span style="width: 45px; display: inline-block;">(OUT)</span></div>
                                </td>
                                <td style="text-align: right;">
                                    <div><?= $this->bpas->formatDecimal($row->AdjustmentQtyADD); ?><span style="width: 45px; display: inline-block;">(ADD)</span></div>
                                    <div style="color: red;"><?= $this->bpas->formatDecimal($row->AdjustmentQtySUB); ?><span style="width: 45px; display: inline-block;">(SUB)</span></div>
                                </td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->SoldQty); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->returnQty); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->BalacneQty); ?></td>
                                <td style="text-align: right;"><?= !empty($row->expiry) ? date('d/m/Y', strtotime($row->expiry)) : 'N/A' ?></td>
                            </tr>
                        <?php 
                        $total_beginning  += $row->BeginningQty;
                        $total_purchased  += $row->PurchasedQty;
                        $total_transfer   += $row->TransferQtyIN;
                        $total_transfer   -= $row->TransferQtyOUT;
                        $total_adjustment += $row->AdjustmentQtyADD;
                        $total_adjustment -= $row->AdjustmentQtySUB;
                        $total_sold       += $row->SoldQty;
                        $total_return     += $row->returnQty;
                        $total_discount   += $row->discount;
                        $total_balance    += $row->BalacneQty;
                        $total_weight     += $row->weight;
                        $total_new        += $row->qtyNewVar;
                        $total_old        += $row->qtyOldVar;
                        $total_broken     += $row->qtyBrokenVar;
                        $i++;
                        }
                    } ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;"><strong><?= lang('total') ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_beginning); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_purchased); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_transfer); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_adjustment); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_sold); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_return); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_balance); ?></strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>