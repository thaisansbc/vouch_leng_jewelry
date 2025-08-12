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
    @page { margin-top: 0; }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content" style="padding-top: 5px; padding-left: 5px;">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="padding-top: 15px; font-size: 18px;font-weight: bold;">
                    <?= isset($biller->name) ? $biller->name : ''; ?></div></center>
                <center><div style="font-size: 16px;font-weight: bold;">
                    <?= lang('របាយការណ៍ លក់តាមបញ្ជីសារពើរពន្ធ ដាក់ជាក្រុម កាលបរិច្ឆេទ (លំអិត)'); ?></div></center>
                <center><div style="font-size: 16px;font-weight: bold;">
                    <?= lang('Sales Report By Inventory Group By Date (Detail)'); ?></div></center>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr class="hide">
                        <th>លេខរៀង <br><?= lang('no'); ?></th>
                        <th>កាលបរិច្ឆេទ <br><?= lang('date'); ?></th>
                        <th>វិក្ក័យបត្រ <br><?= lang('ref'); ?></th>
                        <th>អតិថិជន <br><?= lang('customer'); ?></th>
                        <th>កូដទំនិញ <br><?= lang('items'); ?></th>
                        <th>ឃ្លំាង <br><?= lang('warehouse'); ?></th>
                        <th>បរិមាណ <br><?= lang('qty'); ?></th>
                        <th>ខ្នាត <br><?= lang('unit'); ?></th>
                        <th>លក់ <br><?= lang('price'); ?></th>
                        <th>សរុប <br><?= lang('subtotal'); ?></th>
                        <th>បញ្ចុះតំលៃ <br><?= lang('discount'); ?></th>
                        <th>ពន្ធអាករ <br><?= lang('vat'); ?></th>
                        <th>សរុបចុងក្រោយ<br><?= lang('total'); ?></th>
                    </tr>
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('date'); ?></th>
                        <th><?= lang('ref'); ?></th>
                        <th><?= lang('customer'); ?></th>
                        <th><?= lang('items'); ?></th>
                        <th><?= lang('warehouse'); ?></th>
                        <th><?= lang('qty'); ?></th>
                        <th><?= lang('unit'); ?></th>
                        <th><?= lang('price'); ?></th>
                        <th><?= lang('subtotal'); ?></th>
                        <th><?= lang('discount'); ?></th>
                        <th><?= lang('vat'); ?></th>
                        <th><?= lang('total'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $total = 0;$paid=0;$balance=0;
                        $tamountpaid = 0;
                        $i=1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $this->bpas->hrld($row->date); ?></td>
                                <td><?= $row->reference_no; ?></td>
                                <td><?= $row->customer; ?></td>
                                <td><?= $row->product_code.'-'.$row->product_name; ?></td>
                                <td><?= $row->warehouse_name; ?></td> 
                                <td><?= $row->quantity; ?></td> 
                                <td><?= $row->product_unit_code; ?></td>
                                <td><?= $this->bpas->formatDecimal($row->unit_price); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->unit_quantity * $row->unit_price); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->item_discount); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->item_tax); ?></td>
                                <td><?= $row->subtotal; ?></td>
                            </tr>
                        <?php 
                        $total += 0; //$row->grand_total;
                        $paid += 0;//$row->paid;
                        $balance += $row->subtotal;
                        $i++;
                        }
                    }?>

                    
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-right"><strong><?= lang('total') ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($total); ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($paid); ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($balance); ?></strong></td>
                            <td></td>
                        </tr>
                    
                </table>
                <!-- <div id="pageFooter">Page </div> -->
            </div>
            </div>
        </div>
    </div>
</div>
<footer style="display:none;"><div id="pageFooter">Page </div></footer>