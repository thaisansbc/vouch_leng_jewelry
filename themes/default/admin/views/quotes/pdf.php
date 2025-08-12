<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('quote') . ' ' . $inv->reference_no; ?></title>
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
            height: 30px;   
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
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12 well">
                    <div style="width: 25%; float: left;">
                        <?php if ($logo) {
                            $path   = base_url() . 'assets/uploads/logos/' . $biller->logo;
                            $type   = pathinfo($path, PATHINFO_EXTENSION);
                            $data   = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                            <div>
                                <img src="<?= $base64; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                            </div>
                        <?php } ?>
                    </div>  
                    <div class="col-sm-6 col-xs-6">
                        <div class="text-center" style="line-height: normal;">
                            <h2 style="margin: 0; font-size: 18px;">SBC Cambodia</h2>
                            <h3 style="margin: 3px 0 5px; font-size: 16px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                            <?php
                                echo $biller->address . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;
                                echo '<p style="margin-top: -3px; padding-bottom: -18px;">ទូរស័ព្ទលេខ (' . lang('tel') . '): ' . $biller->phone . '</p>';
                                echo '<p>សារអេឡិចត្រូនិច (' . lang('email') . '): ' . $biller->email . '</p>';
                            ?>
                        </div>
                    </div>
                    <div class="col-sm-2 col-xs-2 text-right order_barcodes">
                        <?php
                            $path   = admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1');
                            $type   = $Settings->barcode_img ? 'png' : 'svg+xml';
                            $data   = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        ?>
                        <!-- <img src="<?= $base64; ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" width="80"/> -->

                        <?php 
                            $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($this->Settings->barcode_img ? '.png' : '.svg');
                            $data = file_get_contents($file_name);
                            $base64 = 'data:image/png;base64,' . base64_encode($data); 
                        ?>
                        <img src="<?= $base64; ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" width="50%"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-xs-12 text-center">
                    <h3 style="font-family: 'Khmer OS Muol Light'; font-weight: bold; margin-top: -5px;">វិក្កយបត្រ</h3>
                    <h3 style="margin-top: -5px;">INVOICE</h3>
                </div>
            </div>

            <div class="row">
                <?php if ($Settings->invoice_view == 1) { ?>
                <div class="col-xs-12 text-center">
                    <h1><?= lang('tax_invoice'); ?></h1>
                </div>
                <?php } ?>

                <div class="col-sm-6 col-xs-6">
                    <div class="divTable">
                        <div class="divRow">
                            <div class="divCell">ឈ្មោះក្រុមហ៊ុន<br>Company Name</div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><h2 style="margin-top: 0;"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h2></div>
                        </div>
                        <div class="divRow">
                            <div class="divCell">អាសយដ្ឋាន<br><?= lang('address'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country;?></div>
                        </div>
                        <div class="divRow">
                            <div class="divCell">ទូរស័ព្ទលេខ (<?= lang('tel'); ?>)</div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $customer->phone ?></div>
                       </div>
                       <div class="divRow">
                            <div class="divCell">អុីម៉ែល (<?= lang('email'); ?>)</div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $customer->email ?></div>
                       </div>
                    </div>
                </div>
                <div class="col-sm-5 col-xs-5">
                    <div class="divTable" style="margin-left: 25px;">
                        <div class="divRow">
                            <div class="divCell">កាលបរិច្ឆេទ<br><?= lang('date'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $this->bpas->hrld($inv->date); ?></div>
                        </div>
                        <div class="divRow">
                            <div class="divCell">លេខរៀងវិក្កយបត្រ<br><?= lang('ref'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $inv->reference_no; ?></div>
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
                    <table class="table_pro" style="margin-top: 15px;">
                        <thead class="thead" style="font-size: 11px;">
                            <tr style="color: #FFF !important; background-color: #444 !important;">
                                <th>ល.រ<br><?= strtoupper(lang('no.')) ?></th>
                                <th style="width: 25%;">បរិយាយមុខទំនិញ<br><?= strtoupper(lang('description')) ?></th>
                                <?php if ($Settings->indian_gst) { ?>
                                    <th><?= lang('hsn_code'); ?></th>
                                <?php } ?>
                                <th style="width: 8%;">ខ្នាត<br><?= strtoupper(lang('unit')) ?></th>
                                <th>ចំនួន<br><?= strtoupper(lang('quantity')) ?></th>
                                <th style="width: 12%;">តម្លៃ<br><?= strtoupper(lang('unit_price')) ?></th>
                                <?php if ($Settings->product_discount) {
                                    echo '<th>' . 'បញ្ចុះតម្លៃ <br>' . strtoupper(lang('discount')) . '</th>';
                                } 
                                if ($Settings->tax1) {
                                    echo '<th>' . 'ពន្ធទំនិញ <br>' . strtoupper(lang('tax')) . '</th>';
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
                                    <?php if ($Settings->indian_gst) { ?>
                                        <td style="vertical-align: middle; text-align: center;"><?= $row->hsn_code; ?></td>
                                    <?php } ?>
                                    <td style="vertical-align: middle; text-align: center">
                                        <?= $row->product_unit_code; ?>
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <?= $this->bpas->formatQuantity($row->unit_quantity) ?>
                                    </td>
                                    <td style="vertical-align: middle; text-align: center">
                                        <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                        $<?= $this->bpas->formatMoney($row->unit_price); ?>
                                    </td>
                                    <?php if ($Settings->product_discount) {
                                        echo '<td style="vertical-align: middle; text-align: center">$' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    }
                                    if ($Settings->tax1) {
                                        echo '<td style="vertical-align: middle; text-align: center">$' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    } ?>
                                    <td style="vertical-align: middle; text-align: right; padding-right: 10px;">
                                        $<?= $this->bpas->formatMoney($row->subtotal); ?>
                                    </td>
                                </tr>
                            <?php $no++; $erow++; $totalRow++;
                                if ($totalRow % 25 == 0) {
                                    echo '<tr class="pageBreak"></tr>';
                                }
                            } ?>
                            <?php
                                if($erow < 13){
                                    $k=13 - $erow;
                                    for($j=1; $j<=$k; $j++) {
                                        echo '<tr>
                                                <td height="34px" style="text-align: center; vertical-align: middle">'.$no.'</td>
                                                <td></td>
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
                                $row = 3;
                                $col =5;
                            
                                if ($inv->grand_total != $inv->total) {
                                    $row++;
                                }
                                if ($inv->order_discount != 0) {
                                    $row++;
                                    $col = 4;
                                }
                                if ($inv->shipping != 0) {
                                    $row++;
                                    $col = 4;
                                }
                                if ($inv->order_tax != 0) {
                                    $row++;
                                    $col = 4;
                                }
                            ?>
                        </div>
                        <div class="tfoot">
                            <?php if ($inv->grand_total != $inv->total) { ?>
                            <tr>
                                <td rowspan = "<?= $row; ?>"></td>
                                <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">សរុប​ / <?= strtoupper(lang('total')) ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td align="right" style="padding-right: 10px;">$<?=$this->bpas->formatMoney($inv->total); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0) { ?>
                            <tr>
                                <td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                                <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">បញ្ចុះតម្លៃលើការបញ្ជាទិញ / <?= strtoupper(lang('order_discount')) ?></td>
                                <td align="right" style="padding-right: 10px;"><?php echo $this->bpas->formatMoney($inv->product_discount); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($Settings->tax1 && $inv->product_tax > 0) { ?>
                            <tr>
                                <td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                                <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">ពន្ធអាករ / <?= strtoupper(lang('tax')) ?></td>
                                <td align="right" style="padding-right: 10px;">$<?= $this->bpas->formatMoney($inv->product_tax) ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($Settings->indian_gst) {
                                if ($inv->cgst > 0) {
                                    $cgst = $inv->cgst;
                                    echo '<tr><td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td><td colspan="' . $col . '" class="text-right" style="padding-right: 10px;">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right" style="padding-right: 10px;">' . ($Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                                }
                                if ($inv->sgst > 0) {
                                    $sgst = $inv->sgst;
                                    echo '<tr><td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td><td colspan="' . $col . '" class="text-right" style="padding-right: 10px;">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right" style="padding-right: 10px;">' . ($Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                                }
                                if ($inv->igst > 0) {
                                    $igst = $inv->igst;
                                    echo '<tr><td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td><td colspan="' . $col . '" class="text-right" style="padding-right: 10px;">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right" style="padding-right: 10px;">' . ($Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                                }
                            } ?>
                            <?php if ($inv->order_discount != 0) {
                                echo '<tr>' . 
                                    '<td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' . 
                                    '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                        lang('order_discount') . ' (' . $default_currency->code . ')</td>' . 
                                    '<td style="text-align: right; padding-right: 10px;">' . 
                                        ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($inv->order_discount) . 
                                    '</td>' . 
                                '</tr>';
                            } ?>
                            <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                echo '<tr>' . 
                                    '<td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                    '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                        lang('order_tax') . ' (' . $default_currency->code . ')' . 
                                    '</td>' . 
                                    '<td style="text-align: right; padding-right: 10px;">' . 
                                        $this->bpas->formatMoney($inv->order_tax) . 
                                    '</td>' . 
                                '</tr>';
                            } ?>
                            <?php if ($inv->shipping != 0) {
                                echo '<tr>' . 
                                    '<td colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                    '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                        lang('shipping') . ' (' . $default_currency->code . ')' . 
                                    '</td>' . 
                                    '<td style="text-align: right; padding-right: 10px;">' . 
                                        $this->bpas->formatMoney($inv->shipping) . 
                                    '</td>' . 
                                '</tr>';
                            } ?>
                            <tr>
                                <td rowspan="<?= $row; ?>" colspan="2" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;">
                                    <?php if (!empty($inv->invoice_footer)) { ?>
                                        <p><strong><u>Note: </u></strong></p>
                                        <p><?= $inv->invoice_footer ?></p>
                                    <?php } ?>
                                </td>
                                <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">តម្លៃសរុបរួម​​ / <?= strtoupper(lang('total_amount')) ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td align="right" style="padding-right: 10px;">$<?= $this->bpas->formatMoney($inv->grand_total); ?></td>
                            </tr>
                        </div>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                <?php if ($inv->note || $inv->note != '') { ?>
                    <div style="margin-top: 20px; background-color: #f2f2f2; border: 1px solid #dadbd7;">
                        <div style="margin: 10px; font-size: 10px;">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <div style="margin-left: 20px;"><?= $this->bpas->decode_html($inv->note); ?></div>
                        </div>
                    </div>
                <?php } ?>
                </div>
                <div class="col-xs-4 pull-right text-center">
                    <hr class="signature" style="color: black; margin: 25px; height: 2px">
                    <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នករៀបចំ<br>Prepared's Signature & Name</p>
                </div>
            </div>
            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, null, $inv->product_tax) : ''; ?>
        </div>        
    </div>
</body>
</html>