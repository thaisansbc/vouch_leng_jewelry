<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= lang("tax_invoice") ?></title>
    <link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
    <link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style type="text/css">
    .container {
        width: 29.7cm;
        margin: 20px auto;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    }
    .table_pro {
        width: 100%;
    }
    .table_pro tr > th {
        text-align: center !important;
        font-size: 10px;
        padding: 5px;
    }
    .table_pro tr > th, .table_pro tr > td {
        border: 1px solid #000 !important;
        font-size: 10px;
    }
    .table_top tr > th, .table_top tr > td {
        border: 1px solid #000 !important;
        font-size: 10px;
        text-align: center;
    }
    .well { padding-bottom: 0px; }
    @media print {
        .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
            border-top: 1px solid #000000 !important;
        }
        @page {
            margin: 0.30in 0 2.30in 0;
        }
        thead { display: table-header-group; }
    }
    @font-face {
        font-family: 'KhmerOS_muollight';
        src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
    }
</style>

<body>
<div class="container" style="width: 821px; margin: 15px auto;">
    <div class="col-xs-12" style="width: 794px;">
        <div class="row">
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
                echo '<div class="alert alert-info no-print"><p>' . lang("sale_is_returned") . ': ' . $inv->return_sale_ref;
                echo '<a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                echo '</p></div>';
            } ?>
            <table style="width: 100%; margin: 0 auto;" id="tb_outter">
                <thead style="display: table-header-group;">
                    <tr>
                        <td>
                            <div class="col-xs-4">
                                <?php
                                if ($logo) { ?>
                                    <div>
                                        <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>" width="250"></div>
                                <?php } ?>                                
                            </div>
                            <div class="col-xs-8" style="padding-left: 0;">
                                <div style="font-weight: bold; font-family: 'Khmer OS Muol Light';font-size: 18px;"><?= $biller->cf1; ?></div>
                                <div style="font-weight: bold; font-family: 'FontAwesome';font-size: 24px;"><?=  $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></div>
                                <div style="font-size:14px;font-weight: bold;line-height: 80%;padding: 10px 0;">
                                    <?php
                                        if ($biller->vat_no != '-' && $biller->vat_no != '') {
											echo '<p style="">' . lang('vat_no') . ': ' . $biller->vat_no . '</p>';
										}
										echo '<p style="letter-spacing: 3px;">' . $biller->cf3 . '</p>';
                                        echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                                        echo '<p>Tel: '.$biller->phone.($biller->email? ' | '.$biller->email:'').($biller->gst_no ? ' | '.$biller->gst_no:'').'</p>';
                                    ?>
                                </div>
                            </div>
                            <div class="col-xs-2 pull-right hide" style="text-align: center;">
								<?= $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="col-xs-5" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div>
                            <div class="col-xs-4 text-center" style="font-size: 14px; line-height: 55%; font-family: KhmerOS_muollight !important; font-weight: bold;">
                                <p >វិក្កយបត្រអាករ / <span style="margin-bottom: 0px;"><?= strtoupper('Tax Invoice') ?></span></p>
                            </div>
                            <div class="col-xs-3" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 20px;"></div>
                        </td>
                    </tr>
                    <tr style="font-size: 11px;">
                        <td>
                            <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 50%; float: left; margin-right: 7px; font-weight: bold;">
                                <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;">ព័ត៍មានអតិថិជន</caption>
                                <?php  if ($customer->name != '-' && $customer->name != '') {?>
                                <tr>
                                    <td style="width: 15%; padding-left: 5px;">ក្រុមហ៊ុន / Company</td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><b><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></b></td>
                                </tr>
                                <tr>
                                    <td style="width: 15%; padding-left: 5px;">អ្នកទទួល / Att. to</td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><b><?= $customer->name; ?></b></td>
                                </tr>
                                
                                <?php } ?>
								<?php  if ($customer->vat_no != '-' && $customer->vat_no != '') {?>
								<tr>
                                    <td style="padding-left: 5px;"><?= lang('vat_no');?></td>
                                    <td>:</td>
                                    <td><?= $customer->vat_no ?></td>
                                </tr>
								<?php }?>
                                <tr>
                                    <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                                    <td>:</td>
                                    <td><?= $customer->phone ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន / Address</td>
                                    <td style="vertical-align: top;">:</td>
                                    <td style="padding-bottom: 3px;"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ' ' . $customer->country; ?></td>
                                </tr>
                            </table>
                            <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 49%; font-weight: bold;">
                                <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;">ឯកសារយោង</caption>
                                <tr>
                                    <td style="width: 12%; padding-left: 5px;">វិក្កយបត្រ / Invoice NO</td>
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
                                    <td style="padding-bottom: 3px;"><?php echo $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                                </tr>
                                <tr>
                                    
                                    <td style="padding-left: 5px;">អត្រាប្តូរប្រាក់ 1USD</td>
                                    <td>:</td>
                                    <td style="">
                                        ៛<?= $this->bpas->formatMoney($inv->currency_rate_kh);?></td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <div class="table-responsive">
                        <table class="table" style="width: 100%; margin-top: 5px;">
                            <thead style="border: 1px solid #000000 !important; font-size: 11px;">
                                <tr style="border: 1px solid #000000 !important; background-color: #5DADE2 !important;">
                                    <th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important; width: 10px;">ល.រ</br>Nº<br></th>
                                    <th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important; width: 380px">បរិយាយ<br>Description<br></th>
                                    <th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important;">ចំនួន<br>Qty</th>
                                    <th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important;">តំលៃ<br>Price</th>
                                    <?php 
                                    if ($Settings->product_discount) {
                                        echo '<th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important;">បញ្ចុះតំលៃ<br>Discount</th>';
                                    } ?>
                                    <th style="text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important; width:15%;">សរុប<br>Amount</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:11px;" class="content-print">
                            <?php 
                                $i = 1;
                                $stotal = 0;
                                $unit = "";
                                $qty = 0; 
                                foreach($rows as $rowx){
                                    if($rowx->option_id == 0 || $rowx->option_id == ""){
                                        $unit = $rowx->base_unit_code;
                                        $qty = $rowx->unit_quantity;
                                    } else {
                                        $unit = $rowx->variant;
                                        $qty = $rowx->unit_quantity;
                                    }
                                    $stotal += $qty * $rowx->unit_price; 
                                }
                                foreach($rows as $row){
                                    if($row->option_id == 0 || $row->option_id == ""){
                                        $unit = $row->base_unit_code;
                                        $qty = $row->unit_quantity;
                                    } else {
                                        $unit = $row->variant;
                                        $qty = $row->unit_quantity;
                                    }
                                ?>
                                <tr style="border:1px solid #000000 !important;">
                                    <td style="border-right: 1px solid #000000 !important; text-align:center;"><?= $i;?></td>
                                    <!-- <td style="border-right: 1px solid #000000 !important; font-size: 10px;" class="cap-height"><?= $row->product_code . '-' . $row->product_name . ($row->comment ? ' (' . $row->comment . ')' : '') ?></td> -->
                                    <td style="border-right: 1px solid #000000 !important; font-size: 10px;" class="cap-height">
                                    <?php 
                                        echo $row->product_code;
                                        echo $row->product_name ? ('-'.$row->product_name):'';
                                        // echo strlen($descr) > 85 ? substr($descr, 0, 80) . '...' : $descr;
                                        echo '<span class="no-print">'.(($row->expiry != "0000-00-00" && $row->expiry != null) ? ' ('.$row->expiry.')' : '').'<span>';
                                        echo $row->comment ? ' (' . $row->comment . ')' : '';
                                        if($this->Settings->product_option){
                                            echo ($row->option_name ? ' (' . $row->option_name . ')' : '');
                                            echo $row->serial_no ? ' ['.$row->serial_no.'L -' : '';
                                            echo $row->max_serial ? $row->max_serial.'L]' : '';
                                        }
                                    ?>
                                    </td>
                                    <td style="text-align:center; border-right: 1px solid #000000 !important;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_name; ?></td>
                                    <td style="text-align:center; border-right: 1px solid #000000 !important;">
                                        <!-- <?= ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' ?> -->
                                        <?php if($row->net_unit_price == 0){ echo "Free"; } else { echo $this->bpas->formatMoney($row->net_unit_price);} ?>
                                    </td>
                                    <?php
                                        if ($Settings->product_discount){
                                            echo '<td style="text-align:center; border-right: 1px solid #000000 !important;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                        }
                                    ?>
                                    <td style="text-align:center;border-right: 1px solid #000000 !important;">
                                        <?php if($row->unit_price == 0){echo "Free";} else { echo $row->subtotal!=0?$this->bpas->formatMoney($row->subtotal):$t; ?>&nbsp;<?php } ?>
                                    </td>      
                                </tr>
                            <?php
                            $i++;
                            ?>
                            <?php }
                            $G = ((count($rows) / 10) - floor(count($rows) / 10)) * 10;
                            if(count($rows) != 10){
                                if($G < 10 && $G != 0){
                                    $G++;
                                    $num = count($rows) + 1;
                                    while($G <= 10) {
                                        echo '  
                                            <tr style="line-height:5px !important; border:1px solid #000000 !important;">
                                                <td style="border-right: 1px solid #000000 !important; text-align: center;">'.$num.'</td>
                                                <td style="border-right: 1px solid #000000 !important;"></td>
                                                <td style="text-align:center;border-right: 1px solid #000000 !important;"></td>
                                                <td style="text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                        if ($Settings->product_discount){
                                            echo '<td style="text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                        }
                                        echo    '<td style="text-align:center;border-right: 1px solid #000000 !important;" ></td>     
                                            </tr> '; 
                                        $G++;
                                        $num++;
                                    }
                                }
                            } ?>
                            </tbody>
                            <div class="tfoot">
								<tr style="font-size: 11px;border-top: 2px solid #000000 !important;">
									<td style="white-space: nowrap; padding: 3.5px 5px; border: 0 solid !important;" rowspan="8" colspan="2">បញ្ជាក់៖ ទំនិញទិញរួចមិនអាចដូរវិញបានទេ!<br></td>
									<td style="white-space: nowrap; padding: 3.5px 5px; text-align: right; border:1px solid !important; font-weight: bold;" colspan="3">សរុបទឹកប្រាក់ / Total</td>
									<td style="white-space: nowrap; padding: 3.5px 5px; text-align: right; border:1px solid !important; font-weight: bold;">$<?=$this->bpas->formatMoney($stotal)?></td>
								</tr>
								<?php if ($inv->order_discount != 0) {
                                    echo '<tr style="font-size: 12px;">
                                            <td style="text-align:right;font-weight: bold;padding-right:10px;border:1px solid !important; " colspan="3">' . lang("order_discount") . '</td>

                                            <td style="text-align:right;font-weight: bold;padding-right:10px;border:1px solid !important; ">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                                } ?>
                                <?php if ($Settings->tax2 && $inv->order_tax >0) {
                                    $tax_rate = $this->site->getTaxRateByID($inv->order_tax_id);
                                    echo '<tr style="font-size: 12px;">
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">អាករលើតម្លៃបន្ថែម /' . lang("vat").'('.$tax_rate->name.')</td>
                                            <td style="text-align:right;font-weight: bold;padding-right:10px;border:1px solid !important;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td>
                                        </tr>';
                                } ?>
                                <?php if ($inv->shipping != 0) {
                                    echo '<tr style="font-size: 12px;"><td style="font-weight: bold;text-align:right;border:1px solid !important; vertical-align:middle !important; padding-right:10px;" colspan="3">' . lang("ការដឹកជញ្ជូន/ Shipping").'</td><td style="text-align:right; vertical-align:middle; padding-right:10px;border:1px solid !important;font-weight: bold;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                                } ?>
								<tr style="font-size: 12px;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ចំនួនទឹកប្រាក់ / Grand Total</td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">$<?=$this->bpas->formatMoney($inv->grand_total)?></td>
                                </tr>
								<tr style="font-size: 12px;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ចំនួនទឹកប្រាក់ជារៀល / Riel</td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">
                                        ៛<?=$this->bpas->formatMoney($inv->grand_total * $inv->currency_rate_kh)?></td>
                                </tr>

                                <tr style="font-size: 12px;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ប្រាក់បានបង់ / Paid Amount</td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?=$this->bpas->formatMoney($inv->paid)?></td>
                                </tr>
                                <tr style="font-size: 12px;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ប្រាក់នៅសល់ / Balance</td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?=$this->bpas->formatMoney($inv->grand_total-$inv->paid);?></td>
                                </tr>

                                <tr style="font-size: 12px;display:none;ne;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3"><b>ប្រាក់ជំពាក់ពីមុន / <?= ucwords(lang("last_balance")) ?></b></td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= ($last_balance > 0) ? $this->bpas->formatMoney($last_balance) : '0.00' ?></td>
                                </tr>
                                <tr style="font-size: 12px;display:none;">
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3"><b>ប្រាក់ជំពាក់សរុប / <?= ucwords(lang("overdue_balance")) ?></b></td>
                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= (($total_balance) > 0) ? $this->bpas->formatMoney($total_balance) : '0.00' ?></td>
                                </tr>
                            </div>
                        </table>
                    </div>
                </tbody>
            </table>
			<?php 
			if (!empty($payments)) { ?>
			<div class="row hide">
				<div class="col-xs-8">
					<table class="table">
							<?php 
							foreach ($payments as $payment) {
							$i=1;
							?>
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
						<?php  $i++; } ?>
					</table>
				</div>
			</div>
			<?php } ?><br>
            <div class="row" style="font-size: 11px; margin-bottom: 5px;">
                <div class="col-xs-4 pull-left text-center" style="margin-top: -10px !important;">
                    <p>អ្នកលក់ / Seller Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center" style="margin-top: -10px !important;">
                    <p>អ្នកទិញ / Buyer Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                </div>
				<div class="col-xs-4 pull-right text-center" style="margin-top: -10px !important;">
                    <p>អ្នកដឹក / Delivery Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                </div>
            </div>
        </div>
    </div>
</div>
<div style="width: 821px; margin: 15px auto;">
    <a class="btn btn-warning no-print" href="<?= site_url('admin/sales'); ?>">
        <i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
    </a>
</div>
</body>
</html>