<?php defined('BASEPATH') or exit('No direct script access allowed'); 

$detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
    <title></title>
    <style type="text/css">
        .table_pro {
            width: 100%;
        }
        .thead tr th {
            text-align: center !important;
            font-size: 11px;
            padding: 5px;
            color: white;
            height: 40px;
        }
        .thead tr th {
            border: 1px solid #000 !important;
        }
        table tr td {
            border: 1px solid #000 !important;
            height: 25px;
            font-size: 11px;
        }
        .divTable {
            display: table;
            width: auto;
        }
        .divRow {
            display: table-row;
            font-size: 12px;
            width: auto;
            margin-bottom: 5px;
        }
        .divCell {
            float: left;
            display :table-column;
            width: 35%;
        }
        .divCell_ {
            float: left;
            display :table-column;
            width: 8%;
        }
    </style>
</head>
<body>
    <div style="padding-top: -85px;">
        <div class="row">
            <div class="col-xs-2">
                <?php
                if ($logo) { ?>
                    <div><img style="height: 80px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$biller->logo; ?>" ></div>
                <?php } ?>                                
            </div>
            <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                <h2 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h2>
                <h2 style="font-weight: bold; font-family: 'FontAwesome';"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                <div style="font-size:14px; font-weight: bold; line-height: 110%; text-align: center;">
                    <?php
                 
                        if($biller->address){
                            echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                        }
                        if($biller->phone){
                            echo '<p>Tel: ' . $biller->phone . '</p>';
                        }
                    ?>
                </div>
            </div>
            <div class="col-xs-2 text-right order_barcodes" style="margin-top: 15px;">
                <!-- <?= $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?> -->
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="col-xs-4" style="border-bottom: 2px solid #2E86C1; text-align: left; margin-bottom: 10px;"></div>
                <div class="col-xs-3 text-center">
                    <h3 style="font-family: 'Khmer OS Muol Light'; font-weight: bold; margin-top: -5px;">វិក្កយបត្រ / INVOICE</h3>
                </div>
                <div class="col-xs-3" style="border-bottom: 2px solid #2E86C1; text-align: right; margin-bottom: 10px;"></div>
            </div>
        </div>
        <div class="row">
            <?php if ($Settings->invoice_view == 1) { ?>
            <div class="col-xs-12 text-center">
                <h1><?= lang('tax_invoice'); ?></h1>
            </div>
            <?php } ?>
  
            <div style="width: 45%; float: left; margin-left: 17px;border-radius: 10px;background-color: white !important;border: 1px solid #2E86C1;padding: 5px;">

                <div style="position: absolute; top: -50px; background-color: white !important; margin-left: 10px; width: 85%;font-style: italic !important;border: 1px solid #000;display: none;">ព័ត៍មានអតិថិជន</div>

                <div class="divTable">
                    <div class="divRow">
                        <div class="divCell">អតិថិជន / <?= lang('customer'); ?></div>
                        <div class="divCell_">:</div>
                        <div class="divCell">
                            <b><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></b></div>
                    </div>
                    
                    <div class="divRow">
                        <div class="divCell">ទូរស័ព្ទលេខ (<?= lang('tel'); ?>)</div>
                        <div class="divCell_">:</div>
                        <div class="divCell"><?= $customer->phone ?></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">អាសយដ្ឋាន/<?= lang('address'); ?></div>
                        <div class="divCell_">:</div>
                        <div class="divCell"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country;?></div>
                    </div>
                </div>
            </div>
            <div style="width: 45%; padding-right: -50px; float: right;border-radius: 10px;background-color: white !important;border: 1px solid #2E86C1;padding: 5px;">
                <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;display: none;">ឯកសារយោង</caption>
                <div class="divTable">
                    <div class="divRow">
                        <div class="divCell">កាលបរិច្ឆេទ/<?= lang('date'); ?></div>
                        <div class="divCell_">:</div>
                        <div class="divCell"><?= $this->bpas->hrld($inv->date); ?></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">វិក្កយបត្រ/Invoice No</div>
                        <div class="divCell_">:</div>
                        <div class="divCell"><?= $inv->reference_no; ?></div>
                    </div>
                    <div class="divRow">
                        <?php if (!empty($inv->return_sale_ref)) { ?>
                        <div class="divCell">លេខរៀងវិក្កយបត្រការលក់ត្រឡប់មកវិញ/<?= lang("return_ref"); ?></div>
                        <div class="divCell_">:</div>
                        <div class="divCell"><?= $inv->return_sale_ref; ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $col = $Settings->indian_gst ? 5 : 4;
        if ($Settings->product_discount && $inv->product_discount != 0) {
            $col++;
        }
        if ($Settings->tax1 && $inv->product_tax > 0) {
            $col++;
        }
        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
            $tcol = $col - 2;
        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
            $tcol = $col - 1;
        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
            $tcol = $col - 1;
        } else {
            $tcol = $col;
        } ?>
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <table class="table_pro" style="margin-top: 10px;">
                    <thead class="thead" style="font-size: 11px;">
                        <tr style="color: #FFFfff !important; background-color: #5DADE2 !important;">
                            <th>ល.រ<br><?= strtoupper(lang('no.')) ?></th>
                            <th>បរិយាយមុខទំនិញ<br><?= strtoupper(lang('description')) ?></th>
                            <th>ចំនួន<br><?= strtoupper(lang('quantity')) ?></th>
                            <th>តម្លៃ<br><?= strtoupper(lang('unit_price')) ?></th>
                            <?php if ($Settings->product_discount) {
                                echo '<th>' . 'បញ្ចុះតម្លៃ <br>' . strtoupper(lang('discount')) . '</th>';
                            } ?>
                            <th>តម្លៃសរុបតាមមុខទំនិញ<br><?= strtoupper(lang('subtotal')) ?></th>
                        </tr>
                    </thead>
                    <div class="tbody">
                        <?php $no = 1; $erow = 1; $totalRow = 0;
                            foreach ($rows as $row) {
                                $free = lang('free');
                                $product_unit = '';
                                $total = 0;
                                $product_name_setting;
                                $product_name_setting = $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');?>
                            <tr>
                                <td style="vertical-align: middle; text-align: center"><?php echo $no ?></td>
                                <td style="vertical-align: middle; padding-left: 10px;">
                                    <?=$row->product_name;?>
                                </td>
                                <td style="vertical-align: middle; text-align: center;">
                                    <?= $this->bpas->formatQuantity($row->unit_quantity) ?> <?= $inv->sale_status == 'returned' ? $row->base_unit_code : $row->product_unit_code; ?>
                                </td>
                                <td style="vertical-align: middle; text-align: center">
                                    <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                    <?= $default_currency->symbol . $this->bpas->formatMoney($row->unit_price); ?>
                                </td>
                                <?php if ($Settings->product_discount) {
                                    echo '<td style="vertical-align: middle; text-align: center">' . $default_currency->symbol . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                }
                                if ($Settings->tax1) {
                                    echo '<td style="vertical-align: middle; text-align: center">' . $default_currency->symbol . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                } ?>
                                <td style="vertical-align: middle; text-align: right; padding-right: 10px;">
                                    <?= $default_currency->symbol . $this->bpas->formatMoney($row->subtotal); ?>
                                </td>
                            </tr>
                        <?php $no++; $erow++; $totalRow++;
                            if ($totalRow % 25 == 0) {
                                echo '<tr class="pageBreak"></tr>';
                            }
                        } ?>
                        <?php
                            if($erow < 11){
                                $k=11 - $erow;
                                for($j=1; $j<=$k; $j++) {
                                    echo '<tr>
                                            <td height="34px" style="text-align: center; vertical-align: middle">'.$no.'</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>';
                                            if($Settings->product_discount){
                                                echo '<td></td>';
                                            }
                                            if ($Settings->tax1) {
                                                echo '<td></td>';   
                                            }
                                    echo '</tr>';
                                    $no++;
                                }
                            }
                        ?>
                        <?php
                            $row = 4;
                            $col =2;
                            if ($inv->grand_total != $inv->total) {
                                $row++;
                            }
                            if ($inv->order_discount != 0) {
                                $row++;
                                $col = 1;
                            }
                            if ($inv->shipping != 0) {
                                $row++;
                                $col = 1;
                            }
                            if ($inv->order_tax != 0) {
                                $row++;
                                $col = 1;
                            }
                        ?>
                    </div>
                    <div class="tfoot">
                        <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td rowspan = "<?= $row; ?>" colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;">
                                <?php if (!empty($inv->invoice_footer)) { ?>
                                    <p style="font-size:14px !important;"><strong><u>Note: </u></strong></p>
                                    <p style="margin-top:-5px !important; line-height: 2"><?= $inv->invoice_footer ?></p>
                                <?php } ?>
                            </td>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">សរុប​ / <?= strtoupper(lang('total')) ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol . $this->bpas->formatMoney($inv->total); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0) { ?>
                        <tr>
                            <td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">បញ្ចុះតម្លៃលើការបញ្ជាទិញ / <?= strtoupper(lang('order_discount')) ?></td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?php echo $this->bpas->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($Settings->tax1 && $inv->product_tax > 0) { ?>
                        <tr>
                            <td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">ពន្ធអាករ / <?= strtoupper(lang('tax')) ?></td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($return_sale) { ?>
                        <tr>
                            <td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">សរុបការលក់ត្រឡប់​មកវិញ / <?= strtoupper(lang('return_total')) . '(' . $default_currency->code . ')' ?></td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney($return_sale->grand_total) ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($inv->surcharge != 0) { ?>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight: bold; padding-right: 10px;">ការបង់ប្រាក់ត្រឡប់មកវិញ / <?= strtoupper(lang('return_surcharge')) . '(' . $default_currency->code . ')' ?></td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney($inv->surcharge) ?></td>
                        </tr>
                        <?php } ?>
                   
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr>' . 
                                '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' . 
                                '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    lang('order_discount') . ' (' . $default_currency->code . ')' . 
                                '</td>' . 
                                '<td style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    $default_currency->symbol . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . 
                                '</td>' . 
                            '</tr>';
                        } ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr>' . 
                                '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    lang('order_tax') . ' (' . $default_currency->code . ')' . 
                                '</td>' . 
                                '<td style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    $default_currency->symbol . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . 
                                '</td>' . 
                            '</tr>';
                        } ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr>' . 
                                '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    lang('shipping') . ' (' . $default_currency->code . ')' . 
                                '</td>' . 
                                '<td style="text-align: right; padding-right: 10px; font-weight: bold;">' . 
                                    $default_currency->symbol . $this->bpas->formatMoney($inv->shipping - ($return_sale && $return_sale->shipping ? $return_sale->shipping : 0)) . 
                                '</td>' . 
                            '</tr>';
                        } ?>
                        <tr>
                            <td rowspan="<?= $row; ?>" colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;">
                                <?php if (!empty($inv->invoice_footer)) { ?>
                                    <p><strong><u>Note: </u></strong></p>
                                    <p><?= $inv->invoice_footer ?></p>
                                <?php } ?>
                            </td>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">តម្លៃសរុបរួម​​ / <?= strtoupper(lang('total_amount')) ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr style="font-size: 11px;">
                            <?php $usa ="ចំនួនទឹកប្រាក់ជាដុល្លា / USA"; $kh ="ចំនួនទឹកប្រាក់ជារៀល / Riel";  ?>
                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="<?= $col; ?>"> 
                                <?=  $detault_currency == "៛" ? $usa : $kh ; ?> </td>
                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">
                            <?php 
                                $kh_money = $inv->grand_total * $inv->currency_rate_kh;
                                $kh_money = ceil($kh_money / 100) * 100;
                            ?>
                            <?=  $detault_currency == "៛" ? ("$" . $this->bpas->formatMoney($inv->grand_total / $inv->currency_rate_kh)) : ("៛" . $this->bpas->formatMoney($kh_money, false, -1)) ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">បានបង់ប្រាក់ចំនួន <?= strtoupper(lang('paid')); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">ចំនួនទឹកប្រាក់ <?= strtoupper(lang('balance')); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td align="right" style="padding-right: 10px; font-weight: bold;"><?= $default_currency->symbol ?><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                        </tr>
                    </div>
                </table>
            </div>
        </div>
        <div class="row" style="font-size: 12px; margin-top: 30px;">
            <div class="col-xs-3 pull-left text-center">
                <hr class="signature" style="margin: 25px; height: 2px; color: black;">
                <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នកលក់<br>Seller's signature and Name</p>
            </div>
            <div class="col-xs-3 pull-left text-center">
                <hr class="signature" style="margin: 25px; height: 2px; color: black;">
                <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នកដឹក<br>Delivery's Signature & Name</p>
            </div>
            <div class="col-xs-4 pull-left text-center" style="margin-left: 2%;">
                <hr class="signature" style="width: 85%; margin: 25px auto !important; height: 2px; color: black;">
                <p style="margin-top: 5px;">ហត្ថលេខា​ និង ឈ្មោះអតិជន<br>Customer's signature and Name</p>
            </div>
            
        </div>
    </div>
</body>
</html>