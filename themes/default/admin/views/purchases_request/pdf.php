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
                $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                $type   = pathinfo($path, PATHINFO_EXTENSION);
                $data   = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                <div style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?=$Settings->site_name; ?>">
                </div>
            <?php
            } ?>
            <h2 align="center"><strong>EXPENDITURE REQUISITION FORM</strong></h2>
            <h3 align="center"><strong>(CAPITAL / OPERATION)</strong></h3>
            <hr>
            <div style="border: 1px solid gray; padding-right:10px;">
                <div class="col-xs-5 border-right">
                            <div class="col-xs-12">
                                <div class="col-xs-12">
                                    <strong><?= lang('requesting_department'); ?></strong><br/>
                                    <strong><?= lang('reference_no'); ?></strong><br/>
                                    <strong><?= lang('attached_specification'); ?></strong>
                                
                                </div>
                            </div>
                            <div class="clearfix"></div>
                    </div>
                    
                    <div class="col-xs-7">
                            <div class="col-xs-12">
                                <div class="col-xs-12">
                                <?= 'IMO'; ?><br/>
                                <?= '20190730/1 IMO'; ?><br/>
                                <input type="checkbox" name=""> <sup>Yes</sup> <input type="checkbox" name=""> <sup>No</sup><br/>
                                </div>
                                <div class="clearfix"></div>
                           </div>
                        <div class="clearfix"></div>
                    </div>
                   
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div><br/>


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
                            <th><?= lang("code"); ?></th>
                            <th><?= lang("description"); ?></th>
                            <th><?= lang("reason_for_request"); ?></th>
                            <th><?= lang("expected_date"); ?></th>
                            <?php if ($Settings->indian_gst) { ?>
                                <th><?= lang("hsn_code"); ?></th>
                            <?php } ?>
                            <th><?= lang("quantity"); ?></th>
                            <!-- <th><?= lang("unit"); ?></th> -->
                            <?php
                                if ($inv->status == 'partial') {
                                    echo '<th>'.lang("received").'</th>';
                                }
                            ?>
                            <th style="padding-right:20px;"><?= lang("unit_cost"); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                            }
                            if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                            }
                            ?>
                            <th style="padding-right:20px;"><?= lang("subtotal"); ?></th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:30px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle; width: 90px;">
                                    <?= $row->product_code; ?>
                        
                                </td>
                                <td style="vertical-align:middle; width: 100px;" >
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
                                
                                <td style="vertical-align:middle; width: 60px;">
                                  <!--    <?php
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                        
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?> -->
                                </td>
                                <td style="vertical-align:middle; width: 85px;">
                                    <?= $inv->date; ?>
                                   <!--  <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?> -->
                        
                                </td> 
                                <?php if ($Settings->indian_gst) { ?>
                                <td style="width: 60px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php } ?>
                                <td style="width: 60px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                               <!--  <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?> -->
                                <td style="text-align:right; width:80px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:80px; padding-right:20px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                      
                        ?>
                        </tbody>
                    <tfoot>
                        <?php
                        $col = $Settings->indian_gst ? 8 : 7;
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
                       
             

                        </tfoot>
                </table>
            </div>
            <div class="row">
                <div class="col-xs-1">
                        
                    </div>
                    <div class="col-xs-3">
                        <?= lang("procurment"); ?><br/>
                        <?= lang("aapproved_by"); ?>
                    </div>
                    <div class="col-xs-8">
                        <input type="checkbox" name=""> <sup>Yes</sup> <input type="checkbox" name=""> <sup>No</sup><br/>
                        <input type="checkbox" name=""> <sup>MD</sup> <input type="checkbox" name=""> <sup>Manager Head ot Department</sup>
                    </div>
            </div><br/>
            <div style="padding: 0 60px 0 0 !important; text-align: center;">
                <div class="row">
                    <div class="col-xs-1">
                        
                    </div>
                    <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                            <?= lang("head_of_department"); ?><br/><br/><br/><br/>
                                    <?= lang("signaturs"); ?><br/>
                                    <?= lang("head_names"); ?><br/>
                                    <?= lang("head_dates"); ?>
                            
                    </div>

                    <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                            <?= lang("manager_of_department"); ?><br/><br/><br/><br/>
                                    <?= lang("signaturs"); ?><br/>
                                    <?= lang("head_names"); ?><br/>
                                    <?= lang("head_dates"); ?>
                    </div>
                    <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                            <?= lang("requested_by"); ?><br/><br/><br/><br/>
                                    <?= lang("signaturs"); ?><br/>
                                    <?= lang("head_names"); ?><br/>
                                    <?= lang("head_dates"); ?>
                    </div>
                </div>
             </div><br/>
             <div style="padding: 0 60px 0 0 !important;">
                 <div class="row">
                    <div class="col-xs-1">
                        
                    </div>
                     <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                                <?= lang("manager_director"); ?><br/><br/><br/><br/>
                                        <?= lang("signaturs"); ?><br/>
                                        <?= lang("head_names"); ?><br/>
                                        <?= lang("head_dates"); ?>
                        </div>
                        <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                                <?= lang("acknowledged_by"); ?><br/><br/><br/><br/>
                                        <?= lang("signaturs"); ?><br/>
                                        <?= lang("head_names"); ?><br/>
                                        <?= lang("head_dates"); ?>
                        </div>
                        <div class="col-xs-3 text-left" style="border: 1px solid gray;">
                                <?= lang("acknowledged_by"); ?><br/><br/><br/><br/>
                                    <?= lang("signaturs"); ?><br/>
                                    <?= lang("head_names"); ?><br/>
                                    <?= lang("head_dates"); ?>
                        </div> 
                 </div>
             </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
