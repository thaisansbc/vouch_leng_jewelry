<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/jquery-3.3.1.min.js"></script>
<style type="text/css" media="all">
    table {
        font-size: 13px !important;
    }
    @media print {
        table {
            font-size: 13px !important;
        }
        @page { margin-top: 30px; }
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_detail_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content" style="padding-top: 5px; padding-left: 5px;">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($company_info)) { ?>
                    <div class="row" id="company_info">
                        <div class="col-xs-2">
                            <div><img style="width: 120px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$company_info->logo; ?>" ></div>                                
                        </div>
                        <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                            <h2 style="font-size: 16px; font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $company_info->cf1; ?></h2>
                            <h2 style="font-size: 16px; font-weight: bold; font-family: 'Time New Romance';"><?= $company_info->company && $company_info->company != '-' ? $company_info->company : $company_info->name; ?></h2>
                            <div style="font-size: 16px; font-weight: bold; line-height: normal; text-align: center;">
                                <?php
                                echo '<p style="letter-spacing: 3px;">' . $company_info->cf3 . '</p>';
                                echo '<p>' . $company_info->cf2 . '</p>';
                                if($company_info->address){
                                    echo '<p>' . $company_info->address . '' . $company_info->postal_code . '' . $company_info->city . ' ' . $company_info->country . '</p>';
                                }
                                if($company_info->phone){
                                    echo '<p>Tel: ' . $company_info->phone . '</p>';
                                }
                                ?>
                            </div>
                            <center><div style="font-size: 16px;font-weight: bold;"><?= lang('របាយការណ៍ លក់តាមបញ្ជីសារពើរពន្ធ ដាក់ជាក្រុម កាលបរិច្ឆេទ (លំអិត)'); ?></div></center>
                            <center><div style="font-size: 16px;font-weight: bold;"><?= lang('Sales Report By Inventory Group By Date (Detail)'); ?></div></center>
                            <center><div style="font-size: 16px;"><?= $this->bpas->hrsd($start_date) . ' 00:00' . ' To ' . $this->bpas->hrsd($end_date) . ' 23:59';?></div></center><br>
                        </div>
                        <div class="col-xs-2" style="margin-top: 15px;">&nbsp;</div>
                    </div>
                <?php } ?>
                <div class=""><!-- table-responsive -->
                    <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <thead>
                            <tr style="border: 1px solid black !important;">
                                <th style="border: 1px solid black !important; text-align: center !important;">ល.រ <br><?= lang('no'); ?></th>
                                <th style="border: 1px solid black !important; text-align: center !important;">កាលបរិច្ឆេទ <br><?= lang('date'); ?></th>
                                <th style="border: 1px solid black !important; text-align: center !important;">វិក្ក័យបត្រ <br><?= lang('ref'); ?></th>
                                <th style="border: 1px solid black !important; text-align: center !important;">មុខទំនិញ <br><?= lang('items'); ?></th>
                                <th style="border: 1px solid black !important; text-align: center !important;">បរិមាណ <br><?= lang('qty'); ?></th>
                                <th style="border: 1px solid black !important; text-align: right !important;">សរុប <br><?= lang('grand_total'); ?></th>
                                <th style="border: 1px solid black !important; text-align: right !important;">បានបង់ <br><?= lang('paid'); ?></th>
                                <th style="border: 1px solid black !important; text-align: right !important;">សមតុល្យសាច់ប្រាក់ <br><?= lang('balance'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($customers)) { 
                                foreach($customers as $cus){
                                    $sales = $this->reports_model->getCustomersPending('sale', null, $cus->id, $product, $reference, $biller, $warehouse, $saleman, $start_date, $end_date, $sale_type, $payment_status);
                                    if(is_array($sales)){ 
                                        $number      = 1;
                                        $grand_total = 0; $paid = 0; $balance = 0;
                                        foreach($sales as $inv){
                                            $return_sale = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
                                            $items = $this->reports_model->getCustomersPending('item', $inv->id);
                                            if(is_array($items)){
                                                $count_items = count($items);
                                                foreach($items as $item_index => $row) { ?>
                                                    <tr style="border: 1px solid black !important;">
                                                        <?php if ($item_index == 0) { ?>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: center !important; border: 1px solid black !important;"><?= $number ?></td>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: center !important; border: 1px solid black !important;"><?=$this->bpas->hrld($inv->date)?></td>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: center !important; border: 1px solid black !important;"><?= $inv->reference_no ?></td>
                                                        <?php } ?>
                                                        <td style="text-align: left !important; border: 1px solid black !important;"><?= $row->product_code . ' (' . $row->product_name . ')' ?></td>
                                                        <td style="text-align: center !important; border: 1px solid black !important;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . ($row->product_unit_name); ?></td>
                                                        <?php if ($item_index == 0) { ?>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($inv->grand_total); ?></td>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($inv->paid); ?></td>
                                                            <td rowspan="<?= $count_items ?>" style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($inv->grand_total - $inv->paid); ?></td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php 
                                            $grand_total += $inv->grand_total; 
                                            $paid        += $inv->paid; 
                                            $balance     += ($inv->grand_total - $inv->paid);
                                            $number++; 
                                        } ?>
                                        <tr style="border: 1px solid black !important;">
                                            <td colspan="5" style="text-align: right; border: 1px solid black !important; text-align: center !important;">ចំនួនលុយសរុបប្រចាំខែ / Monthly Statement</td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($grand_total); ?></td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($paid); ?></td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($balance); ?></td>
                                        </tr>
                                        <tr style="border: 1px solid black !important;">
                                            <td colspan="5" style="text-align: right; border: 1px solid black !important; text-align: center !important;">ចំនួនលុយជំពាក់សរុបរហូតដល់ខែ / Payment Outstanding</td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($grand_total); ?></td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($paid); ?></td>
                                            <td style="text-align: right; border: 1px solid black !important;"><?= $this->bpas->formatMoney($balance); ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xs-6" style="text-align: center !important; margin-top: 3%;"><p>រៀបរៀងដោយ</p></div>
            <div class="col-xs-6" style="text-align: center !important; margin-top: 3%;"><p>យល់ព្រមដោយ</p></div>
        </div>
    </div>
</div>
<footer style="display: none;"><div id="pageFooter">Page </div></footer>