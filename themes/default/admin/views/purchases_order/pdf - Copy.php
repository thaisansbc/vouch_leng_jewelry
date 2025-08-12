<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('purchase') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">

           <?php
            if ($logo) {
                //$path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                $type   = pathinfo($path, PATHINFO_EXTENSION);
                $data   = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                <div style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?=$Settings->site_name; ?>">
                </div>
            <?php
            } ?>
            <h2 align="center"><strong>PURCHASE ORDER</strong></h2>
            <hr style="margin-bottom: 0px; margin-top: 0px;">
            <div class="row padding10">
                <div class="col-xs-6">
                    
                </div>
                <div class="col-xs-6">
                        <div style="text-align: right; ">   <strong><?php echo $this->lang->line("refer"); ?> : </strong> 
                                    <?= $inv->reference_no; ?>
                        </div>
                        <div style="text-align: right; ">   <strong><?php echo $this->lang->line("date"); ?> : </strong> 
                                    <?= $inv->date; ?>
                        </div>
                      
                </div>
            </div>
            <div class="well-sm">
                <div class="row">
                    <div class="col-xs-6">
                            <div style="border: 1px solid #cccccc;border-radius: 5px;">
                                <strong><?= $this->lang->line('vendor'); ?></strong>
                                <hr style="margin-bottom: 0px; margin-top: 3px; width: 280px;">
                                <div>   <strong><?php echo $this->lang->line("name"); ?> : </strong> 
                                        <?= $supplier->company ? $supplier->company : $supplier->company; ?>
                                </div>
                                <div> <strong><?php echo $this->lang->line("Attn"); ?> : </strong> 
                                        <?= $supplier->name ? $supplier->name : $supplier->name; ?>
                                </div>
                                <div> <strong><?php echo $this->lang->line("address"); ?> : </strong> 
                                        <?= $supplier->address; ?>
                                </div>
                                <div> <strong><?php echo $this->lang->line("city"); ?> : </strong> 
                                        <?= $supplier->city; ?>
                                </div>
                                <?php
                                echo '<strong>'.lang("phone") . ": </strong>" . $supplier->phone . "<br />";

                                echo "<p>";

                                if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                                    echo "<br>" . lang("vat_no") . ": " . $supplier->vat_no;
                                }
                                if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                                    echo "<br>" . lang("scf1") . ": " . $supplier->cf1;
                                }
                                if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                                    echo "<br>" . lang("scf2") . ": " . $supplier->cf2;
                                }
                                if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                                    echo "<br>" . lang("scf3") . ": " . $supplier->cf3;
                                }
                                if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                                    echo "<br>" . lang("scf4") . ": " . $supplier->cf4;
                                }
                                if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                                    echo "<br>" . lang("scf5") . ": " . $supplier->cf5;
                                }
                                if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                                    echo "<br>" . lang("scf6") . ": " . $supplier->cf6;
                                }

                                echo "</p>";
                                ?>
                            </div>
                    </div>
     
                    <div class="col-xs-6">
                        <div style="border: 1px solid #cccccc;border-radius: 5px;">
                            <strong><?= $this->lang->line('ship_to'); ?></strong>
                            <hr style="margin-bottom: 0px; margin-top: 3px; width: 270px;">
                            <div>   <strong><?php echo $this->lang->line("name"); ?> : </strong> 
                                    <?= $warehouse->name; ?>
                            </div>
                            <div>   <strong><?php echo $this->lang->line("Attn"); ?> : </strong> 
                                    
                            </div>
                            <div>   <strong><?php echo $this->lang->line("address"); ?> : </strong> 
                                    <?php echo strip_tags($warehouse->address); ?>
                            </div>
                            <div>   <strong><?php echo $this->lang->line("city"); ?> : </strong> 
                                    
                            </div>
                            <div>   <strong><?php echo $this->lang->line("phone"); ?> : </strong> 
                                    <?= $warehouse->phone; ?>
                            </div>
                    
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php
                $col = $Settings->indian_gst ? 5 : 4;
                if ($inv->status == 'partial') {
                    $col++;
                }
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
                }
            ?>
            <div style="margin-top: 15px;">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                    <tr class="active">
                            <th><?= lang("item"); ?></th>
                            <th><?= lang("qty"); ?></th>
                            <th><?= lang("unit"); ?></th>
                            <!-- <th><?= lang("code"); ?></th> -->
                            <th><?= lang("description"); ?></th>
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
                            <th style="padding-right:20px;"><?= lang("unit_price"); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                            }
                            if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                            }
                            ?>
                            <th style="padding-right:20px;"><?= lang("total_usd"); ?></th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <!-- <td style="vertical-align:middle;">
                                    <?= $row->product_code; ?>
                        
                                </td> -->
                                <td style="width: 75px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <td style="width: 65px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <td style="vertical-align:middle; width: 130px;">
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
                                </td>
                                <!--  <td style="vertical-align:middle;">
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
                                </td>
                                <td style="vertical-align:middle;">
                                    <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                        
                                </td> -->  
                                <?php if ($Settings->indian_gst) { ?>
                                <td style="width: 70px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php } ?>
                                
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?>
                                <td style="text-align:right; width:90px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) { ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php } ?>
                                    <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                        }
                        ?>
                        </tbody>
                    <tfoot>
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
                        if (($Settings->product_discount  && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
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
                                    style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        if ($return_purchase) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->indian_gst) {
                            if ($inv->cgst > 0) {
                                $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                            }
                            if ($inv->sgst > 0) {
                                $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                            }
                            if ($inv->igst > 0) {
                                $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                            }
                        } ?>
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr style="display: none;">
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
                        </tr>

                        </tfoot>
                </table>
            </div>
             <div class="row">
                    
                        <div class="col-xs-3">
                            <table class="table">
                                <tr>
                                    <td style="text-align: center;"><?php echo lang('payment_detail'); ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="well-sm">
                                            <input type="checkbox" name="ch1" value="check"> <sup>Check</sup> <br>
                                            <input type="checkbox" name="ch1" value="check"> <sup>Cash</sup><br>
                                            <input type="checkbox" name="ch1" value="check"> <sup>Telegraphic Transfer</sup><br>
                                            <h6><?= lang('name_f'); ?></h6>
                                            <h6><?= lang('cc#'); ?></h6>
                                            <h6><?= lang('exp_date'); ?></h6>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table">
                                <tr>
                                    <td style="text-align: center;"><?= lang('shipping_date') ?></td>
                                </tr>
                                <tr>
                                    <td>
                                     <div class="well-sm">
                                        <!-- <hr>
                                        <h6>Shipping Date</h6> -->
                                        <p><?= lang("date_f"); ?></p><br/>
                                        </div>
                                    </td>
                                </tr>
                            </table>
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

                    <div class="col-xs-6">
                            <div class="well-sm">
                                <hr>
                                <p><strong><?= lang('representative'); ?></strong></p> 
                            </div><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="col-xs-8">
                               <strong><?= lang('managing_director'); ?></strong>
                            </div>
                    </div>
                    </div>
                </div>
            

        </div>
    </div>
</div>
</body>
</html>
