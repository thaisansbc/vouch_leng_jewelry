<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= lang("ar_aging_form") ?></title>
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
            margin: 0.30in 0 0.30in 0;
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
                            <td><hr style="border: 1px solid black; margin: 0px;"></td>
                        </tr>
                        <tr>
                            <td style="">
                                <div style="margin-left: 20px; margin-top: 5px; font-size: 14px; font-style: italic; font-family: 'Time New Romance'; font-weight: bold;">As of <?= date('M d, Y') ?></div>
                            </td>
                        </tr>
                    </div>
                    <tbody>
                        <?php if (!empty($ar_aging)) { ?>
                            <?php $detault_currency = ($Settings->default_currency == "USD" ? "$" : ($Settings->default_currency == "KHR" ? "៛" : "฿"));  ?>
                            <tr>
                                <td>
                                    <table class="table" style="width: 100%; margin-top: 5px;">
                                        <thead style="border: 1px solid #000000 !important; font-size: 12px;">
                                            <tr style="border: 1px solid #000000 !important; background-color: #5DADE2 !important;">
                                                <th colspan="100%" style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important; font-size: 14px;"><?= lang('account_receivable'); ?></th>
                                            </tr>
                                            <tr style="border: 1px solid #000000 !important; background-color: #5DADE2 !important;">
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important; width: 10px;"><?= lang('no'); ?></th>
                                                <th style="text-align: left !important; border: 1px solid #000000 !important; line-height: 12px !important; width: 20%;"><?= lang('customer_name'); ?></th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= lang('current'); ?></th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">1 - 30</th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">31 - 60</th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">61 - 90</th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;">91 - Over</th>
                                                <th style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= strtoupper(lang('total')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 12px;" class="content-print">
                                            <?php 
                                            $i = 1;
                                            $total_current = 0; 
                                            $total_1_30    = 0;
                                            $total_31_60   = 0;
                                            $total_61_90   = 0;
                                            $total_91_over = 0;
                                            $total_balance = 0;
                                            foreach ($ar_aging as $key => $customer) { 
                                                $total_current += $customer->balance_current;
                                                $total_1_30    += $customer->balance_1_30;
                                                $total_31_60   += $customer->balance_31_60;
                                                $total_61_90   += $customer->balance_61_90;
                                                $total_91_over += $customer->balance_91_over;
                                                $total_balance += $customer->total_balance;
                                            ?>
                                                <tr>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $i++; ?></td>
                                                    <td style="text-align: left !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $customer->customer_name; ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->balance_current); ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->balance_1_30); ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->balance_31_60); ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->balance_61_90); ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->balance_91_over); ?></td>
                                                    <td style="text-align: center !important; border: 1px solid #000000 !important; line-height: 12px !important;"><?= $this->bpas->formatMoney($customer->total_balance); ?></td>
                                                </tr>
                                            <?php } ?>
                                            <tr style="font-size: 12px;">
                                                <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="2"><?= lang('total'); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_current); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_1_30); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_31_60); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_61_90); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_91_over); ?></td>
                                                <td style="text-align: center; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency . $this->bpas->formatMoney($total_balance); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
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
        <a class="btn btn-warning no-print" href="<?= admin_url('account/list_ar_aging'); ?>">
            <i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
        </a>
    </div>
</body>
</html>