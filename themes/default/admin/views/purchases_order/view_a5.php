<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= lang('view_a5') ?></title>
	<link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style type="text/css">
    <?php if ((!$Owner && !$Admin) && !$GP['products-cost']) { ?>
        .show-cost { display: none !important; }
    <?php } ?>
</style>
<style>
    @media print {
        .table-bordered th, 
        .table-bordered td{
            border: 1px solid #282b2e;
        }
        table th{
            background: #428bca;
        }
        @page {
            size: A5;
            margin: 0;
        }
    }
    .container {
		width: 29.7cm;
		margin: 20px auto;
		box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
	}
    /* table {
        width: 100%;
    } */
    table th{
        background: #428bca;
    }
    #printable table th, #printable table td {
        padding: 5px;
        border: 1px solid #282b2e;
    }
    .approve_form {
        width: 32%;
        border: 1px solid #000000;
        float: left; 
        margin-right: 1.33%;
        padding: 5px;
        margin-bottom: 5px;
    }
</style>

<div class="box">
    <div class="container" style="width: 821px; margin: 10px auto;">
        <div class="col-xs-12" style="width: 794px;">
            <div class="row" style="margin-top: 10px !important;">
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:10px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
            </div>
            <div class="row">
                <?php
                if ($Settings->logo ){
                    $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                    $type   = pathinfo($path, PATHINFO_EXTENSION);
                    $data   = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                    <center>
                        <div>
                            <img src="<?= $path; ?>" alt="<?=$Settings->site_name; ?>" style="max-height: 80px;">
                        </div>
                    </center>
                <?php
                } ?>
                <div class="well-sm">
                    <div class="col-xs-4">
                        <div style="border-bottom: 3px solid #000000;">&nbsp;</div>
                    </div>
                    <div class="col-xs-4">
                        <h2 style="font-size: 20px;" align="center"><strong style="margin-top: -10px; display: block;"><?= strtoupper(lang('purchase_order')); ?></strong></h2>
                    </div>
                    <div class="col-xs-4">
                        <div style="border-bottom: 3px solid #000000;">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-xs-6" style="margin-top: 5px;"> 
                        <strong><?= $this->lang->line('vendor'); ?> : </strong>
                        <div style="border: 1px solid #ccc; border-radius: 5px; padding: 5px 10px;">
                            <table>
                                <tr>
                                    <td style="width: 42%;"><strong><?php echo $this->lang->line("name"); ?></strong></td>
                                    <td style="width: 8%;">:</td>
                                    <td><?= $supplier->company ? $supplier->company : $supplier->company; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("Attn"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->name ? $supplier->name : $supplier->name; ?></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top !important;"><strong><?php echo $this->lang->line("address"); ?></strong></td>
                                    <td style="vertical-align:top !important;">:</td>
                                    <td><?= $supplier->address; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("city"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->city; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("phone"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->phone; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-xs-6" style="margin-top: 5px;">
                        <strong><?= $this->lang->line('ship_to'); ?> : </strong>
                        <div style="border: 1px solid #ccc; border-radius: 5px; padding: 5px 10px;">
                            <table>
                                <tr>
                                    <td style="width: 42%;"><strong><?php echo $this->lang->line("name"); ?></strong></td>
                                    <td style="width: 8%;">:</td>
                                    <td><?= $warehouse->name; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("Attn"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $warehouse->atten_name; ?></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top !important;"><strong><?php echo $this->lang->line("address"); ?></strong></td>
                                    <td style="vertical-align:top !important;">:</td>
                                    <td><?= strip_tags($warehouse->address); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("city"); ?></strong></td>
                                    <td>:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $this->lang->line("phone"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $warehouse->phone; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive" id="printable">
                            <table class="print_table table-hover table-striped print-table order-table" style="margin: 10px auto; width: 96%;">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?= lang("item"); ?></th>
                                        <th class="text-center"><?= lang("qty"); ?></th>
                                        <th class="text-center"><?= lang("unit"); ?></th>
                                        <th class="text-center"><?= lang("description"); ?></th>
                                        <?php if ($Settings->indian_gst) { ?>
                                            <th><?= lang("hsn_code"); ?></th>
                                        <?php } ?>
                                        <?php
                                            if ($inv->status == 'partial') {
                                                echo '<th>'.lang("received").'</th>';
                                            }
                                        ?>
                                        <th class="show-cost" style="text-align: right;"><?= lang("unit_price"); ?></th>
                                        <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<th class="show-cost" style="text-align: right; vertical-align: middle;">' . lang("tax") . '</th>';
                                        }
                                        if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                            echo '<th class="show-cost" style="text-align: right; vertical-align: middle;">' . lang("discount") . '</th>';
                                        }
                                        ?>
                                        <th class="show-cost" style="text-align: right;"><?= lang("total_usd"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $r = 1; 
                                        foreach ($rows as $row):
                                    ?>
                                    <tr>
                                        <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                        <td style="vertical-align:middle; width: 180px;">
                                            <?= $row->product_name; ?>
                                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                            <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                                            <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                            <?php
                                            $data= explode("|",$row->variant);
                                            if($row->variant){
                                                echo $data[0];
                                                if(isset($data[1])){
                                                //  echo $data[1];
                                                }
                                            }
                                            ?>
                                            <?php 
                                                if(isset($data[1])){ echo $data[1]; }
                                                if(isset($data[2])){ echo ' | '.$data[2]; }
                                            ?>
                                        </td>
                                        <?php if ($Settings->indian_gst) { ?>
                                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                        <?php } ?>
                                        
                                        <?php
                                        if ($inv->status == 'partial') {
                                            echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                        }
                                        ?>
                                        <td class="show-cost" style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                        <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td class="show-cost" style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                            echo '<td class="show-cost" style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                        }
                                        ?>
                                        <td class="show-cost" style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                    </tr>
                                    <?php
                                        $r++;
                                        endforeach;
                                    ?>
                                </tbody>
                                <tfoot class="show-cost">
                                    <?php
                                        $col = $Settings->indian_gst ? 6 : 5;
                                        if ($inv->status == 'partial') {
                                            $col++;
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            $col++;
                                        }
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            $col++;
                                        }
                                        if (($Settings->product_discount && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
                                            $tcol = $col - 2;
                                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                                            $tcol = $col - 1;
                                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                                            $tcol = $col - 1;
                                        } else {
                                            $tcol = $col;
                                        }
                                    ?>
                                    <?php if ($inv->grand_total != $inv->total) { ?>
                                        <tr>
                                            <td colspan="<?= $tcol; ?>"
                                                style="text-align:right; font-weight:bold; padding-right:10px;"><?= lang('total'); ?>
                                                (<?= $default_currency->code; ?>)
                                            </td>
                                            <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                        } ?>
                                            <td style="text-align:right; font-weight:bold; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                        if ($return_purchase) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                                        }
                                        if ($inv->surcharge != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                                        } ?>
                                        <?php if ($inv->order_discount != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                                        } ?>
                                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                                        } ?>
                                        <?php if ($inv->shipping != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                                    } ?>
                                        <tr>
                                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; padding-right:10px;"><?= lang('total_amount'); ?>
                                                (<?= $default_currency->code; ?>)
                                            </td>
                                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; padding-right:10px;"><?= lang('balance'); ?>
                                                (<?= $default_currency->code; ?>)
                                            </td>
                                            <td style="text-align:right; font-weight:bold; padding-right:10px;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                                        </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-4" style="margin-top: 20px; padding: 30px;">
                        <table style="border: 1px solid #cccccc; width:300px;border-radius: 5px;">
                            <tr>
                                <th style="text-align: center;border-bottom: 1px solid #cccccc;" height="30">Payment Details</th>
                            </tr>
                            <tr>
                                <td>
                                    <div class="well-sm">
                                    <div style="width: 15px;float: left;height: 15px;border:1px solid #000000;"></div>&nbsp;Check<br>
                                    <div style="width: 15px;float: left;height: 15px;border:1px solid #000000;"></div>&nbsp;Cash<br>
                                    <div style="width: 15px;float: left;height: 15px;border:1px solid #000000;"></div>&nbsp; Telegraphic Transfer<br>
                                    <h6>Name:    .........................................</h6>
                                    <h6>CC#:     .........................................</h6>
                                    <h6>Exp Date: ......../......../..............</h6>
                                    </div>
                                </td>
                            </tr>
                        </table><br/>
                        <table style="border: 1px solid #cccccc; width:300px;border-radius: 5px;">
                            <tr>
                                <th style="text-align: center;border-bottom: 1px solid #cccccc;" height="30">Shipping Date</th>
                            </tr>
                            <tr>
                                <td>
                                    <div class="well-sm">
                                        <!-- <hr>
                                        <h6>Shipping Date</h6> -->  
                                        <p><?= lang("date"); ?>: ............/............/..............</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-3" style="padding: 30px;">
                    <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-xs-5 text-center" style="padding: 30px;">
                        <div class="well-sm">
                            <hr>
                            <p><strong><?= lang('representative'); ?></strong></p> 
                        </div>
                        <div class="col-xs-12" style="margin-top: 45%;">
                            <strong><?= lang('managing_director'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="width: 821px; margin: 10px auto;">
        <a class="btn btn-warning no-print" href="<?= site_url('admin/purchases_order'); ?>">
            <i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
        </a>
    </div>
</div>
