<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= lang("ap_statements") ?></title>
    <link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
    <link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style type="text/css">
    .container {
        margin: 20px auto;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
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
            margin: 0.30in 0 0 0;
        }
        .sub-container { width: 821px !important; }
        thead { display: table-header-group; }
    }
    @font-face {
        font-family: 'KhmerOS_muollight';
        src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
    }
</style>
<body>
    <div class="container" style="width: 821px; margin: 15px auto;">
        <div class="col-xs-12 sub-container" style="width: 794px;">
            <div class="row">
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <table style="width: 100%; margin: 0 auto;" id="tb_outter">
                    <div class="thead" style="display: table-header-group;">
                        <tr>
                            <td>
                                <div class="col-xs-2">
                                    <div><img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>" width="250"></div>
                                </div>
                                <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                                    <h4 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h4>
                                    <h4 style="font-weight: bold; font-family: 'FontAwesome';"><?=  $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h4>
                                    <div style="font-size:14px;font-weight: bold;line-height: 80%; text-align: center;">
                                        <?php
                                        echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                                        echo '<p>Tel: ' . $biller->phone . '</p>';
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><hr style="border: 1px solid black; margin-top: 0px;"></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <div style="font-family: KhmerOS_muollight !important; font-weight: bold; padding: 0; font-size: 16px;">បញ្ជីវិក្កយបត្រមិនទាន់ទូទាត់</div>
                                <div style="font-size: 16px;">គិតត្រឹមថ្ងៃទី <?= ($start_date != 0 ? $this->bpas->fldc($start_date . ' 00:00:00') : '00/00/0000') .' ដល់ ' . ($end_date != 0 ? $this->bpas->fldc($end_date . ' 23:59:59') : '00/00/0000'); ?></div>
                            </td>
                        </tr>
                    </div>
                    <tbody>
                        <?php if (!empty($suppliers)) { ?>
                            <?php $detault_currency = ($Settings->default_currency == "USD" ? "$" : ($Settings->default_currency == "KHR" ? "៛" : "฿"));  ?>
                            <?php foreach ($suppliers as $key => $supplier) { 
                                $invs = $this->accounts_model->getPurchaseBysupplierV2($supplier->supplier_id, $start_date, $end_date, $balance);
                                ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <div style="margin-top: 20px;">
                                            <p style="display: inline-block; float: left; width: 30% !important;">អ្នកផ្គត់ផ្គង់ឈ្មោះ: <?= $supplier->supplier; ?></p>
                                            <p style="float: left; width: 40% !important;">អាសយដ្ឋាន: <?= $supplier->address; ?></p>
                                            <p>កូដលេខ: </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table class="table" style="width: 100%; margin-top: 5px;">
                                            <thead style="border: 1px solid #000000 !important; font-size: 12px;">
                                                <tr style="border: 1px solid #000000 !important; background-color: #5DADE2 !important;">
                                                    <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important; width: 10px;">ល.រ</th>
                                                    <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important; width: 20%;">ថ្ងៃ ខែ ឆ្នាំ</th>
                                                    <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">វិក្កយបត្រ</th>
                                                    <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">សមតុល្យ</th>
                                                    <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">ចុះថ្លៃ</th>
                                                </tr>
                                            </thead>
                                            <tbody style="font-size: 12px;" class="content-print">
                                                <?php 
                                                $i = 1;
                                                $cus_balance         = 0; 
                                                $cus_discount        = 0;
                                                $cus_return_balance  = 0; 
                                                $cus_return_discount = 0;
                                                $total_balance       = 0;
                                                foreach ($invs as $inv) {
                                                    $purchase = $this->accounts_model->getPurchaseByPID($inv->id);
                                                    $total_balance += ($purchase->grand_total - $purchase->paid);
                                                    if ($purchase->status == 'returned') {
                                                        $cus_return_balance  += ($purchase->grand_total - $purchase->paid + $purchase->order_discount);
                                                        $cus_return_discount += $purchase->order_discount;
                                                        continue;
                                                    } else {
                                                        $cus_balance  += ($purchase->grand_total - $purchase->paid + $purchase->order_discount);
                                                        $cus_discount += $purchase->order_discount;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $i++; ?></td>
                                                        <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->hrsd($purchase->date) ?></td>
                                                        <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $purchase->reference_no ?></td>
                                                        <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $detault_currency . $this->bpas->formatMoney($purchase->grand_total - $purchase->paid + $purchase->order_discount); ?></td>
                                                        <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $detault_currency . $purchase->order_discount ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <tr style="font-size: 12px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">សរុប</td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($cus_balance); ?></td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($cus_discount); ?></td>
                                                </tr>
                                                <tr style="font-size: 12px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ទំនិញផ្ញើរសងវិញ</td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($cus_return_balance); ?></td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($cus_return_discount); ?></td>
                                                </tr>
                                                <tr style="font-size: 12px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ទឹកប្រាក់ត្រូវទូទាត់</td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"></td>
                                                    <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_balance); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="row" style="font-size: 11px; margin-bottom: 15px;">
                    <div class="col-xs-4 pull-left text-center" style="margin-top: 10px !important;">
                        <p>អ្នកលក់ / Seller Signature</p><br><br>
                        <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                    </div>
                    <div class="col-xs-4 pull-right text-center" style="margin-top: 10px !important;">
                        <p>អ្នកទិញ / Buyer Signature</p><br><br>
                        <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                    </div>
                    <div class="col-xs-4 pull-right text-center" style="margin-top: 10px !important;">
                        <p>អ្នកដឹក / Delivery Signature</p><br><br>
                        <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 20px auto 0 auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="width: 821px; margin: 15px auto;">
        <a class="btn btn-warning no-print" href="<?= admin_url('account/ar_by_supplier'); ?>">
            <i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
        </a>
    </div>
</body>
</html>