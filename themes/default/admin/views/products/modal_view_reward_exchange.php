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
                            <div class="col-xs-2">&nbsp;</div>
                            <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                                <h1 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h1>
                                <h1 style="font-weight: bold; font-family: 'FontAwesome';"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h1>
                                <div style="font-size:14px; font-weight: bold; line-height: 110%; text-align: center;">
                                    <?php if ($biller->address) {
                                        echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                                    }
                                    if ($biller->phone) {
                                        echo '<p>Tel: ' . $biller->phone . '</p>';
                                    } ?>
                                </div>
                            </div>
                            <div class="col-xs-2">&nbsp;</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 15px 0;">
                            <div class="col-xs-4" style="">&nbsp;</div>
                            <div class="col-xs-4 text-center" style="font-size: 16px; line-height: 55%; font-family: KhmerOS_muollight !important; font-weight: bold; padding: 0;">
                                <p>ការប្តូររង្វាន់ / <span style="margin-bottom: 0px;"><?= strtoupper('reward exchange') ?></span></p>
                            </div>
                            <div class="col-xs-4" style=""></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="header_th" style="border-radius: 10px; width: 49%; float: left;  font-weight: bold; margin-bottom: 5px !important;line-height:1.6 !important; ">
                                <tr>
                                    <td style="width: 30%; padding-left: 5px;"><?= $inv->category == 'customer' ? 'អតិថិជន' : 'អ្នកផ្គត់ផ្គង់' ?> / <?= lang($inv->category); ?></td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><b><?= $company->company && $company->company != '-' ? $company->company : $company->name; ?></b></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                                    <td>:</td>
                                    <td><?= $company->phone ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន / <?= lang('address'); ?></td>
                                    <td style="vertical-align: top;">:</td>
                                    <td style="padding-bottom: 3px;"><?php echo $company->address . ', ' . $company->city . ' ' . $company->postal_code . ' ' . $company->state . ', ' . $company->country; ?></td>
                                </tr>
                            </table>
                            <table class="header_th" style="border-radius: 10px; width: 49%; float: left;  font-weight: bold; margin-bottom: 5px !important;line-height:1.6 !important; ">
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
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 30px;">Nº</th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 180px">Exchange Product</th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 120px;">Exchange Qty</th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 10%;">Price</th>
                                            <?php if ($inv->type == 'product') { ?>
                                                <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 180px">Receive Product</th>
                                                <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 120px;">Receive Qty</th>
                                            <?php } else { ?>
                                                <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 120px;">Interest</th>
                                            <?php } ?>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 17%;">SubTotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="content-print">
                                        <?php foreach ($rows as $key => $row) { ?>
                                        <tr style="line-height: 5px !important; border:1px solid #000000 !important; font-size: 12px;">
                                            <td style="padding: 5px 5px; border-right: 1px solid #000000 !important; text-align:center;"><?= ($key +1) ?></td>
                                            <td style="padding: 5px 5px; border-right: 1px solid #000000 !important; font-size: 12px;" class="cap-height">
                                                <?php echo $descr = $row->exchange_product_name; ?>
                                            </td>
                                            <td style="padding: 5px 5px; text-align:center; border-right: 1px solid #000000 !important;">
                                                <?= $this->bpas->formatQuantity($row->exchange_quantity) . ' ' . $row->exchange_unit_name; ?>
                                            </td>
                                            <td style="padding: 5px 5px; text-align:center; border-right: 1px solid #000000 !important;">
                                                <?php echo $detault_currency . $this->bpas->formatMoney($row->unit_price); ?>
                                            </td>
                                            <?php if ($inv->type == 'product') { ?>
                                                <td style="padding: 5px 5px; border-right: 1px solid #000000 !important; font-size: 12px;" class="cap-height">
                                                    <?php echo $descr = $row->receive_product_name; ?>
                                                </td>
                                                <td style="padding: 5px 5px; text-align:center; border-right: 1px solid #000000 !important;">
                                                    <?= $this->bpas->formatQuantity($row->receive_quantity) . ' ' . $row->receive_unit_name; ?>
                                                </td>
                                            <?php } else { ?>
                                                <td style="padding: 5px 5px; text-align:center; border-right: 1px solid #000000 !important;">
                                                    <?= $this->bpas->formatDecimal($row->interest); ?>
                                                </td>
                                            <?php } ?>
                                            <td style="padding: 5px 5px; text-align: right; border-right: 1px solid #000000 !important;">
                                                <?php echo $detault_currency . $this->bpas->formatMoney($row->subtotal); ?> 
                                            </td>      
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="line-height: 5px !important; font-size: 12px;">
                                            <td colspan="<?= ($inv->type == 'product' ? '4' : '3'); ?>" rowspan="3"></td>
                                            <td colspan="2" style="padding: 5px 5px; font-weight: bold; text-align: right; border: 1px solid #000000 !important;"><?= 'សរុបទឹកប្រាក់ / ' . lang('grand_total'); ?></td>
                                            <td style="padding: 5px 5px; font-weight: bold; text-align:right; border: 1px solid #000000 !important;"><?php echo $detault_currency . $this->bpas->formatMoney($inv->grand_total); ?></td>
                                        </tr>
                                        <tr style="line-height: 5px !important; font-size: 12px;">
                                            <td colspan="2" style="padding: 5px 5px; font-weight: bold; text-align: right; border: 1px solid #000000 !important;"><?= 'ប្រាក់បានបង់ / ' . lang('paid'); ?></td>
                                            <td style="padding: 5px 5px; font-weight: bold; text-align:right; border: 1px solid #000000 !important;"><?php echo $detault_currency . $this->bpas->formatMoney($inv->paid); ?></td>
                                        </tr>
                                        <tr style="line-height: 5px !important; font-size: 12px;">
                                            <td colspan="2" style="padding: 5px 5px; font-weight: bold; text-align: right; border: 1px solid #000000 !important;"><?= 'ប្រាក់នៅសល់ / ' . lang('balance'); ?></td>
                                            <td style="padding: 5px 5px; font-weight: bold; text-align:right; border: 1px solid #000000 !important;"><?php echo $detault_currency . $this->bpas->formatMoney($inv->grand_total - $inv->paid); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
                <div class="col-xs-4 pull-right text-center ">
                    <p style="margin-top: 2px;">អ្នកដឹក / Delivery Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center hide">
                    <p style="margin-top: 2px;">អ្នកយល់ព្រម / Approved by</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
            </div>
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