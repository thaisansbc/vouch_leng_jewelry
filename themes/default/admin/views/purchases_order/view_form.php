<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    <?php if ((!$Owner && !$Admin) && !$GP['products-cost']) { ?>
        .show-cost { display: none !important; }
    <?php } ?>
</style>
<style type="text/css">
    @media print {
        .table-bordered th, 
        .table-bordered td{
            border: 1px solid #ddd;
        }
        table th{
            background: #428bca;
        }
    }
    table{
        width: 100%;

    }
    table th{
        background: #428bca;
    }
    #printable table th,#printable table td{
        padding: 5px;
        border: 1px solid #ddd;
    }
</style>
<head>
    <meta charset="utf-8">
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
             <div class="row">
                <div class="col-lg-12">
                    <div class="col-xs-4">
                        <?php
                        if ($Settings->logo ){
                            $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                            $type   = pathinfo($path, PATHINFO_EXTENSION);
                            $data   = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                            <center>
                                <div>
                                    <img src="<?= $path; ?>" alt="<?= $Settings->site_name; ?>" style="max-height: 80px;">
                                </div>
                            </center>
                        <?php
                        } ?>
                    </div>
                    <div class="col-xs-8">
                        
                    </div>
                
                </div>
                <div class="col-lg-12">
                    <div class="well-sm">
                        <div class="col-xs-4">
                            <div style="border-bottom: 3px solid #000000;">&nbsp;</div>
                        </div>
                        <div class="col-xs-4">
                            <h2 style="font-size: 20px;" align="center"><strong>PURCHASE ORDER</strong></h2></div>
                        <div class="col-xs-4">
                            <div style="border-bottom: 3px solid #000000;">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="row" style="font-weight: bold;">

           
                <div class="col-lg-12">
                    <div class="well-sm">
                        <table style="border-radius: 10px; border: 2px solid #000000; border-collapse: separate !important; width: 48%; float: left; margin-right: 15px; font-weight: bold; margin-bottom: 5px !important;">
                            <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 35%; margin-bottom: -5px; font-style: italic !important;"><?= lang('vendor'); ?></caption>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('name'); ?></td>
                                <td style="width: 75%;">: <b><?= $supplier->company ? $supplier->company : $supplier->company; ?></b></td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('Attn'); ?></td>
                                <td style="width: 75%;">: <b><?= $supplier->name ? $supplier->name : $supplier->name; ?></b></td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('address'); ?></td>
                                <td style="width: 75%;">: <b><?= $supplier->address; ?></b></td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('city'); ?></td>
                                <td style="width: 75%;">: <b><?= $supplier->city; ?></b></td>
                            </tr>
                        </table>
                        <table style="border-radius: 10px; border: 2px solid #000000; border-collapse: separate !important; width: 48%; font-weight: bold;">
                            <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 35%; margin-bottom: -5px; font-style: italic !important;"><?= lang('to'); ?></caption>
              
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('date'); ?></td>
                                <td style="width: 75%;">: <?= $inv->date; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;"><?= lang('invoice'); ?></td>
                                <td style="width: 75%;">: <?= $inv->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;">&nbsp;</td>
                                <td style="width: 75%;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="width: 25%; padding-left: 5px;">&nbsp;</td>
                                <td style="width: 75%;">&nbsp;</td>
                            </tr>
                        </table>                    
                    </div>
                    <div class="table-responsive" id="printable">
                        <table class="print_table table-hover table-striped print-table order-table">
                            <thead>
                            <tr>
                                <th class="text-center"><?= lang("no"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th class="text-center"><?= lang("Ship To"); ?></th>
                                <th class="text-center"><?= lang("unit"); ?></th>
                                <th class="text-center"><?= lang("qty"); ?></th>
                                <!-- <th><?= lang("color"); ?></th>
                                <th><?= lang("size"); ?></th> -->
                                <?php if ($Settings->indian_gst) { ?>
                                    <th><?= lang("hsn_code"); ?></th>
                                <?php } ?>
                                <?php
                                    if ($inv->status == 'partial') {
                                        echo '<th>'.lang("received").'</th>';
                                    }
                                ?>
                                <th class="show-cost" style="padding-right: 10px; text-align: right;"><?= lang("unit_price"); ?></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th class="show-cost" style="padding-right: 10px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<th class="show-cost" style="padding-right: 10px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                }
                                ?>
                                <th class="show-cost" style="padding-right: 10px; text-align: right;"><?= lang("total_usd"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $r = 1; 
                            foreach ($rows as $row):
                                ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle; width: 180px;">
                                        <?= $row->product_code; ?>
                                        <?= $row->product_name; ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                        <!-- color  -->
                                        <?php
                                        $data= explode("|",$row->variant);
                                        if($row->variant){
                                            
                                            echo $data[0];
                                            if(isset($data[1])){
                                            //  echo $data[1];
                                            }
                                            
                                        }
                                        //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                        ?>
                                        <!-- size -->
                                        <?php if(isset($data[1])){ echo $data[1];}
                                        if(isset($data[2])){ echo ' | '.$data[2];}
                                        ?>
                                    </td>
                                    <td style="vertical-align:middle; width: 180px;">
                                         <?= $row->description; ?>
                                    </td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                    } ?>
                                    <td class="show-cost" style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td class="show-cost" style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                        echo '<td class="show-cost" style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    } ?>
                                    <td class="show-cost" style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                            ?>
                            </tbody>
                            <tfoot class="show-cost">
                            <?php
                            $col = $Settings->indian_gst ? 7 : 6;
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
                            <?php if ($inv->grand_total != $inv->total) {
                                ?>
                                <tr>
                                    <td colspan="<?= $tcol; ?>" style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                    } ?>
                                    <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                </tr>
                            <?php } ?>
                            <?php
                            if ($return_purchase) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                            }
                            if ($inv->surcharge != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                            } ?>
                            <?php if ($inv->order_discount != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                            } ?>
                            <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                            } ?>
                            <?php if ($inv->shipping != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                            } ?>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; padding-right:10px;"><?= lang('total_amount'); ?> (<?= $default_currency->code; ?>)</td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; padding-right:10px;"><?= lang('balance'); ?> (<?= $default_currency->code; ?>)</td>
                                <td style="text-align:right; font-weight:bold; padding-right:10px;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <br><br>
                    <div class="row">
                            <div class="col-xs-4">
                                <table style="border: 1px solid #cccccc;
                                    width:300px;border-radius: 5px;">
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
                                </table><br/>
                            </div>
                        <div class="col-xs-3">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                            <?php if ($inv->note || $inv->note != "") { ?>
                                <div class="well well-sm">
                                    <p class="bold"><?= lang("note"); ?>:</p>
                                    <div><?= $this->bpas->decode_html($inv->note); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-xs-5 text-center">
                            <div class="well-sm">
                                <hr>
                                <p><strong><?= lang('representative'); ?></strong></p> 
                                </div><br/><br/><br/><br/><br/><br/>
                                <div class="col-xs-12">
                                   <strong><?= lang('managing_director'); ?></strong>
                                </div>
                        </div>
                    </div>
            </div>
            </div>
    </div>
</div>
</body>