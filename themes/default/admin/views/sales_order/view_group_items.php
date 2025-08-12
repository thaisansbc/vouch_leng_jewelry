<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<head>
    <meta charset="utf-8">
    <style>
        .container {
            width: 29.7cm;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            
        }
        .table_pro {
            width: 100%;
        }
        .table_pro tr > th {
            text-align: center !important;
            font-size: 12px;
            padding: 5px;
        }
        .table_pro tr > th, .table_pro tr > td {
            border: 1px solid #000 !important;
            font-size: 12px;
        }
        .header_th td{
            font-size: 12px;
            font-family: 'Khmer Mool1';
        }
        .table_top tr > th, .table_top tr > td {
            border: 1px solid #000 !important;
            font-size: 12px;
            text-align: center;
        }
        .well { padding-bottom: 0px; }
        .qrimg { width: 50px !important; }
        
        @media print {
            .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
                border-top: 1px solid #000000 !important;
            }
            @page {
                margin: 0.15in 0 1.68in 0;
            }
            .header_th td{
                font-size: 12px;
            }
            .modal-body{
                margin-right: 20px;
            }
            thead { display: table-header-group; }
            .note_ { border: 1px solid black !important; }
            .modal-content { page-break-after: auto; }
        } 
        @font-face {
            font-family: 'KhmerOS_muollight';
            src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
        }
    </style>
</head>
<body>
<div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; margin-top: -15px !important;">
    <div class="modal-content">    
        <div class="modal-body">
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <table border="0" cellspacing="0" style="width: 100%;" id="tb_outter">
                <thead>
                    <tr>
                        <td>
                            <div class="col-xs-2">
                                <?php
                                    if ($logo==1) { ?>
                                        <div><img style="width: 190px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$biller->logo; ?>" ></div>
                                    <?php }
                                ?>                                
                            </div>
                            <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                                <h1 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h1>
                                <h1 style="font-weight: bold; font-family: 'FontAwesome';"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h1>
                                <div style="font-size:14px; font-weight: bold; line-height: 110%; text-align: center;">
                                    <?php
                                        echo '<p style="letter-spacing: 3px;">' . $biller->cf3 . '</p>';
                                        echo '<p>' . $biller->cf2 . '</p>';
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
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:15px 0;">
                            <div class="col-xs-4" style="">&nbsp;</div>
                            <div class="col-xs-4 text-center" style="font-size: 16px; line-height: 55%; font-family: KhmerOS_muollight !important; font-weight: bold; padding: 0;">
                                <p><span style="margin-bottom: 0px;"><?= strtoupper(lang('QUOTATION')) ?></span></p>
                            </div>
                            <div class="col-xs-4" style=""></div> <!-- #5DADE2 -->
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="header_th" style="border-radius: 10px; width: 49%; float: left;  font-weight: bold; margin-bottom: 5px !important;line-height:1.6 !important; ">
                                <!-- <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 85%; margin-bottom: -5px; font-style: italic !important;">ព័ត៍មានអតិថិជន</caption> -->
                                <tr>
                                    <td style="width: 30%; padding-left: 5px;">អតិថិជន / <?= lang('customer'); ?></td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><b><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></b></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                                    <td>:</td>
                                    <td><?= $customer->phone ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន / <?= lang('address'); ?></td>
                                    <td style="vertical-align: top;">:</td>
                                    <td style="padding-bottom: 3px;"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country; ?></td>
                                </tr>
                                <?php if($inv->payment_term) {?>
                                <tr>
                                    <td style="padding-left: 5px;">លក្ខណទូរទាត់ / Payment Term</td>
                                    <td>:</td>
                                    <td><?= $inv->payment_term ?> Day</td>
                                </tr>
                                <?php }?>
                            </table>
                            <table class="header_th" style="border-radius: 10px; width: 49%; float: left;  font-weight: bold; margin-bottom: 5px !important;line-height:1.6 !important; ">
                                <!-- <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;">ឯកសារយោង</caption> -->
                                <tr>
                                    <td style="width: 25%; padding-left: 5px;">វិក្កយបត្រ / Invoice NO</td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><?= $inv->reference_no; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">កាលបរិច្ឆាទ / Date</td>
                                    <td>:</td>
                                    <td><?= $this->bpas->hrsd($inv->date); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">អ្នកគិតលុយ / Cashier</td>
                                    <td>:</td>
                                    <td><?php echo $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">អត្រាប្តូរប្រាក់ / Rate</td>
                                    <td>:</td>
                                    <td><?php echo $this->bpas->formatMoney($inv->currency_rate_kh); ?></td>
                                </tr>
                                
                                <?php if($sold_by && 0){?>
                                <tr>
                                    <td style="padding-left: 5px;">អ្នកលក់ / Sale Man</td>
                                    <td>:</td>
                                    <td><?php echo $sold_by->first_name . ' ' . $sold_by->last_name; ?></td>
                                </tr> 
                                <?php }?>
                                <?php if($Settings->module_hotel_apartment){?>
                                <tr>
                                    <td style="padding-left: 5px;">ថ្ងៃចូល / Check In</td>
                                    <td>:</td>
                                    <td><?= $this->bpas->hrld($inv->date_in); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">ចេញដំណើរ / Departure</td>
                                    <td>:</td>
                                    <td><?php echo $this->bpas->hrsd($inv->date_out);?></td>
                                </tr>
                                <?php }?>
                            </table>
                        </td>
                    </tr>
                </thead>
                <tbody>
                     <?php  $detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";  ?>
                    <tr>
                        <td>
                            <div class="table-responsive">
                                <table class="table" style="width: 100%;">
                                    <thead style="border: 1px solid #000000 !important; font-size: 12px;">
                                        <tr style="border: 1px solid #000000 !important;">
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 10px;">ល.រ</br>Nº<br></th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 180px">បរិយាយ<br>Description<br></th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 50px;">ចំនួន<br>Qty</th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 10%;">តំលៃ<br>Price</th>
                                            <?php 
                                            if ($Settings->product_discount) {
                                                echo '<th style="background-color: #5DADE2 !important;  padding: 2px 0; text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important; width: 12%">បញ្ចុះតំលៃ<br>Discount</th>';
                                            } ?>
                                            <th style="background-color: #5DADE2 !important;  padding: 2px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 17%;">សរុប<br>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="content-print">
                                    <?php 
                                    $i = 1;
                                    $stotal = 0;
                                    $unit   = "";
                                    $qty    = 0; 
                                    foreach ($rows as $rowx) {
                                        if ($rowx->option_id == 0 || $rowx->option_id == "") {
                                            $unit = $rowx->base_unit_code;
                                            $qty = $rowx->unit_quantity;
                                        } else {
                                            $unit = $rowx->variant;
                                            $qty = $rowx->unit_quantity;
                                        }
                                        $stotal += $qty * $rowx->unit_price; 
                                    }
                                    foreach ($rows as $row) {
                                        if ($row->option_id == 0 || $row->option_id == "") {
                                            $unit = $row->base_unit_code;
                                            $qty = $row->unit_quantity;
                                        } else {
                                            $unit = $row->variant;
                                            $qty = $row->unit_quantity;
                                        }
                                    ?>
                                        <tr style="line-height: 5px !important; border:1px solid #000000 !important; font-size: 12px;">
                                            <td style="padding: 2px; border-right: 1px solid #000000 !important; text-align:center;"><?= $i ?></td>
                                            <td style="padding: 2px; border-right: 1px solid #000000 !important; font-size: 12px;" class="cap-height">
                                                <?php 
                                                    $descr = '';
                                                    $description = 6;//$Settings->product_description;
                                                    switch($description){
                                                        case 1:
                                                            $descr = $row->product_code;
                                                            break;
                                                        case 2:
                                                            $descr = $row->product_name;
                                                            break;
                                                        case 3:
                                                            $descr = $row->product_name . ($row->comment ? ' (' . $row->comment . ')' : '');
                                                            break;
                                                        case 4:
                                                            $descr = $row->product_code . '-' . $row->product_name;
                                                            break;
                                                        case 5:
                                                            $descr = $row->product_code . '-' . $row->product_name . ($row->comment ? ' (' . $row->comment . ')' : '');
                                                            break;
                                                        case 6:
                                                            $descr = $row->product_code . '-' . $row->product_name . (($row->expiry != "0000-00-00" && $row->expiry != null) ? ' ('.$row->expiry.')' : '') . ($row->comment ? ' (' . $row->comment . ')' : '');
                                                            break;
                                                    } 
                                                    // echo strlen($descr) > 85 ? substr($descr, 0, 80) . '...' : $descr;
                                                    echo $descr;
                                                    if($this->Settings->product_option){
                                                        echo ($row->option_name ? ' (' . $row->option_name . ')' : '');
                                                        echo $row->serial_no ? ' ['.$row->serial_no.'L -' : '';
                                                        echo $row->max_serial ? $row->max_serial.'L]' : '';
                                                    }
                                                ?>
                                            </td>
                                            <td style="padding: 2px; text-align:center; border-right: 1px solid #000000 !important;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->name_unit; ?></td>
                                            <td style="padding: 2px; text-align:center; border-right: 1px solid #000000 !important;">
                                                <!-- <?= ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' ?> -->
                                                <?php if($row->unit_price == 0){ echo "Free"; } else { echo $detault_currency.$this->bpas->formatMoney($row->unit_price); } ?>
                                            </td>
                                            <?php
                                                if ($Settings->product_discount){
                                                    echo '<td style="padding: 2px; text-align: center; border-right: 1px solid #000000 !important;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $detault_currency.$this->bpas->formatMoney($row->item_discount) . '</td>';
                                                }
                                            ?>
                                            <td style="padding: 2px; text-align: right; border-right: 1px solid #000000 !important;">
                                                <?php if($row->unit_price == 0){echo "Free";} else { echo $row->subtotal!=0 ? $detault_currency.$this->bpas->formatMoney($row->subtotal) : $t ; ?>&nbsp<?php } ?> 
                                            </td>      
                                        </tr>
                                    <?php $i++; }
                                    $G = ((count($rows) / 10) - floor(count($rows) / 10)) * 10;
                                    if(count($rows) != 10){
                                        if($G < 10 && $G != 0){
                                            $G++;
                                            $num = count($rows) + 1;
                                            while($G <= 10) {
                                                echo ' 
                                                    <tr style="line-height: 8px !important; border:1px solid #000000 !important;">
                                                        <td style="padding: 2px; border-right: 1px solid #000000 !important; text-align:center;">'.$num.'</td>
                                                        <td style="padding: 2px; border-right: 1px solid #000000 !important;"></td>
                                                        <td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>
                                                        <td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                                if ($Settings->product_discount){
                                                    echo '<td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                                }
                                                echo   '<td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;" ></td>     
                                                    </tr>'; 
                                                $G++;
                                                $num++;
                                            }
                                        }
                                    }  ?>
                                    </tbody>
                                    <tfoot style=" font-size: 12px;">
                                        <tr style="font-size: 11px; border-top: 2px solid #000000 !important;">
                                            <td style="vertical-align: top !important; padding: 3.5px 5px; border: 0 solid !important;" rowspan="7" colspan="2">បញ្ជាក់៖ ទំនិញទិញរួចមិនអាចដូរវិញបានទេ!</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">សរុបទឹកប្រាក់ / Total</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency.$this->bpas->formatMoney($stotal)?></td>
                                        </tr>
                                        <?php if ($inv->order_discount != 0) {
                                            echo '<tr style="font-size: 11px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">បញ្ចុះតម្លៃ / ' . lang("order_discount") . '</td>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                                        } ?>
                                        <?php if ($inv->order_tax !=0) {
                                            echo '<tr style="font-size: 11px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">អាករលើតម្លៃបន្ថែម / ' . lang("vat") . '</td>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td>
                                                </tr>';
                                        } ?>
                                        <?php if ($inv->shipping != 0) {
                                            echo '<tr>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ថ្លៃដឹកជញ្ជូន / ' . lang("shipping") . '</td>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">$' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                                        } ?>
                                        <?php if ($inv->order_tax > 0 || $inv->order_discount > 0 ||  $inv->shipping > 0 ) { ?>
                                            <tr style="font-size: 11px;">
                                                <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ចំនួនទឹកប្រាក់ / Grand Total</td>
                                                <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency.$this->bpas->formatMoney($inv->grand_total)?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr style="font-size: 11px;">
                                            <?php $usa ="ចំនួនទឹកប្រាក់ជាដុល្លា / USA"; $kh ="ចំនួនទឹកប្រាក់ជារៀល / Riel";  ?>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3"> <?=  $detault_currency == "៛" ? $usa : $kh ; ?> </td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">
                                            <?php 
                                                $kh_money = $inv->grand_total * $inv->currency_rate_kh;
                                                $kh_money = ceil($kh_money / 100) * 100;
                                            ?>
                                            <?=  $detault_currency == "៛" ? ("$" . $this->bpas->formatMoney($inv->grand_total / $inv->currency_rate_kh)) : ("៛" . $this->bpas->formatMoney($kh_money, false, -1)) ?></td>
                                        </tr>
                                        <!-- <tr style="font-size: 11px;">
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ប្រាក់កក់ / Deposit</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?=$detault_currency.$this->bpas->formatMoney($inv->deposit)?></td>
                                        </tr> -->
                                        <!-- <tr style="font-size: 11px;">
                                            <td style=" padding: 3.5px 5px; text-align: right; border:1px solid !important; font-weight: bold;" colspan="3">ប្រាក់នៅសល់ / Balance</td>
                                            <td style=" padding: 3.5px 5px; text-align: right; border:1px solid !important; font-weight: bold;"><?= $detault_currency.$this->bpas->formatMoney($inv->grand_total-$inv->deposit);?></td>
                                        </tr> -->
                                    </tfoot>
                                </table>
                            </div>
                            <?php 
                            if (!empty($payments)) { ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <table class="table">
                                            <?php 
                                            foreach ($payments as $payment) {
                                            $i = 1; ?>
                                                <tr>
                                                    <td>លើក <?= $i; ?></td>
                                                    <td>ចំនួន <?= $this->bpas->formatMoney($payment->amount); ?></td>
                                                    <td>នៅថ្ធៃ <?= $this->bpas->hrld($payment->date) ?></td>
                                                    <td>តាម <?= lang($payment->paid_by);
                                                        if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                            echo ' (' . $payment->cc_no . ')';
                                                        } elseif ($payment->paid_by == 'Cheque') {
                                                            echo ' (' . $payment->cheque_no . ')';
                                                        }
                                                        ?></td>
                                                </tr>
                                            <?php 
                                            $i++;
                                        } ?>
                                    </table>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <?php
                                        if ($inv->note || $inv->note != "") { ?>
                                            <div class="well well-sm note_" style="font-size: 8px;">
                                                <p class="bold"><?= lang("note"); ?>:</p>
                                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                                            </div>
                                        <?php }
                                        if ($inv->staff_note || $inv->staff_note != "") { ?>
                                            <div class="well well-sm staff_note" style="font-size: 8px;">
                                                <p class="bold"><?= lang("staff_note"); ?>:</p>
                                                <div><?= $this->bpas->decode_html($inv->staff_note); ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php if (!empty($payments)) { ?>
                                    <div class="row staff_note hide">
                                        <div class="col-xs-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-condensed print-table">
                                                    <thead>
                                                        <tr>
                                                            <th><?= lang('date') ?></th>
                                                            <th><?= lang('payment_reference') ?></th>
                                                            <th><?= lang('paid_by') ?></th>
                                                            <th><?= lang('amount') ?></th>
                                                            <th><?= lang('created_by') ?></th>
                                                            <th><?= lang('type') ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($payments as $payment) { ?>
                                                            <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                                                <td><?= $this->bpas->hrld($payment->date) ?></td>
                                                                <td><?= $payment->reference_no; ?></td>
                                                                <td><?= lang($payment->paid_by);
                                                                    if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                                        echo ' (' . $payment->cc_no . ')';
                                                                    } elseif ($payment->paid_by == 'Cheque') {
                                                                        echo ' (' . $payment->cheque_no . ')';
                                                                    }
                                                                    ?></td>
                                                                <td><?= $this->bpas->formatMoney($payment->amount); ?></td>
                                                                <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                                                <td><?= lang($payment->type); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <div class="row" style="font-size: 11px;">
                <div class="col-xs-4 pull-left text-center">
                    <p style="margin-top: 2px;">អ្នកលក់ / Seller Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center">
                    <p style="margin-top: 2px;">អ្នកទិញ / Buyer Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center">
                    <p style="margin-top: 2px;">អ្នកដឹក / Delivery Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
            </div>
            <div class="container no-print" style="width: 100%;">
        </div>
    </div>
</div>
</body>
<style>
    @media print{
        .no-print{
            display:none !important;
        }
        .tr_print{
            display:table-row !important;
        }
        .modal-dialog{
            <?= ($print==1) ? 'display:none !important;' : ''; ?>
        }
        .bg-text{
            display:block !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
    }
    .bg-text{
        opacity: 0.1;
        color:lightblack;
        font-size:60px;
        position:absolute;
        transform:rotate(300deg);
        -webkit-transform:rotate(300deg);
        display:none;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        window.onafterprint = function(){       
            $.ajax({
                url : site.base_url + "system_settings/add_print",
                dataType : "JSON",
                type : "GET",
                data : { 
                        transaction_id : <?= $inv->id ?>,
                        transaction : "Sale",
                        reference_no : "<?= $inv->reference_no ?>"
                    }
            });
        }
        $('.tip').tooltip();            
    });
</script>   