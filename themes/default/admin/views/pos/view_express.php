<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if ($modal) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php
} else {
    $rec_cur= $inv->currency =="usd" ? "$" : "៛";
    ?>
<!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
        <base href="<?=base_url()?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
        <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
        <style type="text/css" media="all">
            body { color: #000;}
            #wrapper { max-width: 480px; margin: 0 auto; padding-top: 10px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            .bootbox .modal-footer { border-top: 0; text-align: center; }
            h3 { margin: 5px 0; }
            .order_barcodes img { float: none !important; margin-top: 5px; }
            table{
                font-size: 11px !important;
            }
            table .border_top{
                border-top:none;
            }
            table .border_bottom{
                border-bottom:none;
            }
            table .border_left{
                border-left:none;
            }
            table .border_right{
                border-right:none;
            }
            th span .text1{
                text-align: left;
            }
            th span .text2{
                text-align: right;
            }
            @media print {
                table{
                    font-size: 11px !important;
                }

                table .border_left{
                    border-left:none;
                }
                table .border_right{
                    border-right:none;
                }
                .no-print { display: none; }
                #wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
                table tfoot { display: table-row-group; }
            }
        </style>
    </head>

    <body>
        <?php
    } ?>

    <div id="wrapper">
        <div id="receiptData">
            <div class="no-print">
                <?php
                if ($message) {
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?=is_array($message) ? print_r($message, true) : $message;?>
                    </div>
                    <?php
                } ?>
            </div>
            <div id="receipt-data">
                <div class="text-center">
                    <?php echo  !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                    <h3 style="text-transform:uppercase;" class="hide"><?=$biller->company != '-' ? $biller->company : $biller->name;?></h3>
                    <?php
                    $detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";
                    $payments_=$payments[0];
                    if(isset($payments_)){
                        $paid_amount= explode(',',$payments_->paid_amount);
                        $paid_amount_usd=$paid_amount[0];
                        $paid_amount_kh=$paid_amount[1];
                        $paid_amount_bat=$paid_amount[2];
                        $currency_rate= explode(',',$payments_->currency_rate);
                        $currency_rate_usd=$currency_rate[0];
                        $currency_rate_kh=$currency_rate[1];
                        $currency_rate_bat=$currency_rate[2];
                    }else{
                        $paid_amount_usd='';
                        $paid_amount_kh='';
                        $paid_amount_bat='';
                        $currency_rate_usd='';
                        $currency_rate_kh='';
                        $currency_rate_bat='';
                    }
                    $usd=" $";
                    $khm=" ៛";
                    $bat=" B";
                  // echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
                    "<br>" . lang("tel") . ": " . $biller->phone;
                    // comment or remove these extra info if you don't need
                    if (!empty($biller->cf1) && $biller->cf1 != "-") {
                        echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                    }
                    if (!empty($biller->cf2) && $biller->cf2 != "-") {
                        echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                    }
                    if (!empty($biller->cf3) && $biller->cf3 != "-") {
                        echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                    }
                    if (!empty($biller->cf4) && $biller->cf4 != "-") {
                        echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    }
                    if (!empty($biller->cf5) && $biller->cf5 != "-") {
                        echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    }
                    if (!empty($biller->cf6) && $biller->cf6 != "-") {
                        echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    }
                    // end of the customer fields
                    echo "<br>";
                    // if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                    //     echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
                    // }
                    // if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                    //     echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
                    // }
                    echo '</p>';
                    ?>
                </div>
                <?php

                if ($Settings->invoice_view == 1 || $Settings->indian_gst) {
                    ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('tax_invoice');?></h4>
                    </div>
                    <?php
                }else{
                     ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('Ticket');?></h4>
                    </div>
                    <?php
                }
                ?>
                <table width="100%">
                    <tr>
                        <td width="5%"><?php echo lang("Customer");?></td>
                        <td width="40%"><?php echo ': ' . ($customer->company && $customer->company != '-' ? $customer->company : $customer->name); ?></td>
                        <td width="15%">Inv N<sup>0</sup></td>
                        <td width="45%">
                        <?php echo ': '.$inv->reference_no;
                         if (!empty($inv->return_sale_ref)) {
                            echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a data-target="#myModal2" data-toggle="modal" href="'.admin_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                            } else {
                                echo '</p>';
                            }
                        }
                        ?>
                        </td>
                    </tr>
                    <?php // if ($pos_settings->pos_type !="pos") {?>
                    <tr style="border-bottom:1px dashed #cccccc;">
                        <td width="15%">From</td>
                        <td width="30%"><?php echo ': Phnom Phenh';?></td>
                        <td width="15%">To</td>
                        <td width="40%"><?php echo ': Kondulkiri';?></td>
                    </tr>
                    <?php //}?>
                    <tr style="border-bottom:1px dashed #cccccc;">
                       
                        <td width="15%">Departure</td>
                        <td width="40%"><?php echo ': 23/12/2121 07:00 PM';?></td>
             
                        <td width="15%">Cashier</td>
                        <td width="40%"><?php echo ': '.$created_by->first_name." ".$created_by->last_name;?></td>
                    
                    </tr>
                    <?php
                    if ($pos_settings->customer_details) {
                        echo "<tr>";
                            echo "<td>";
                                echo lang("address");
                            echo "</td>";
                            echo "<td colspan='3'>";
                                echo ': '.$customer->address." ".$customer->city ." ".$customer->state." ".$customer->country ."<br>";
                            echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
                <div style="clear:both;"></div>
                <table class="table table-condensed">
                    <thead style="border:1px solid #adabab;background:#f8f8f8;">
                        <th>Seat</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </thead>
                    <tbody>
                        <?php
                        $r = 1; $category = 0;
                        $tax_summary = array();
                        $defaultGrandTotal = 0;
                        // var_dump($rows);exit;
                        foreach ($rows as $row) {
                            
                            if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                $category = $row->category_id;
                                echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                            }
                            $defaultPrice = $this->site->getProductDefaultPrice($row->product_id);
                            $defaultGrandTotal = $defaultGrandTotal + ($row->real_unit_price * $row->unit_quantity);
                            if($row->product_code != "Time"){
                            echo '<tr>
                                    
                                    <td class="border-bottom-">5C, 6C</td>'; /*'<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';*/
                                   
                                            echo '<td class="border-bottom-">'.$this->bpas->formatQuantity($row->unit_quantity).'</td>';
                                        
                            if($defaultPrice->price !== $row->unit_price){
                                echo '
                                        <td class="border-bottom-">8</td>
                                        <td class="no-border border-bottom text-right">'.$row->currency.$this->bpas->formatMoney($row->subtotal) . '</td>
                                    </tr>';
                            }else{
                                echo '
                                    <td class="border-bottom-">'. $row->currency . $this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td>
                                    <td class="border-bottom- text-right">'.$row->currency.$this->bpas->formatMoney($row->subtotal) . '</td>
                                </tr>';
                            }
                               

                            }
                            $r++;
                        }
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row) {
                                if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                    $category = $row->category_id;
                                    echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                                }
                                echo '<tr>
                                    <td class="no-border">'.$r.'</td>';
                                echo '<td class="no-border">'. product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';
                                echo '<td class="no-border border-bottom">' . $this->bpas->formatQuantity($row->unit_quantity).'</td>';
                                echo '<td class="no-border border-bottom">' . $this->bpas->formatQuantity($row->unit_quantity) . ' x '.$this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td><td class="no-border border-bottom text-right">' . $this->bpas->formatMoney($row->subtotal) . '</td></tr>';
                                $r++;
                            }
                        }

                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><?php echo "សរុប";?>/<?=lang("total");?></td>
                            <th colspan="2" ><span class="pull-left">:</span>
                            <span class="pull-right"><?= $detault_currency.$this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></span></td>
                        </tr>
                        <?php
                        if ($inv->order_discount != 0) {
                         //   $dd = ($inv->order_discount / $inv->total);
                             $dp = '';//' (' . round($dd * 100) . '%)';
                            echo '<tr>
                                    <td colspan="2" class="text-right">បញ្ចុះតំលៃ /' . lang("discount") . $dp.'</td>
                                    <td colspan="2" ><span class="pull-left">:</span>
                                    <span class="pull-right">' . $khr.$this->bpas->formatMoney($inv->order_discount) . '<span></td></tr>';
                            // $dp = '';//' (' . round($dd * 100) . '%)';
                            // echo '<tr>
                            //         <td colspan="2">បញ្ចុះតំលៃ /' . lang("discount") . $dp.'</td>
                            //         <th colspan="2" class="pull-right"><span class="pull-left">:</span><span class="pull-right">' . $usd.$this->bpas->formatMoney($inv->order_discount) . '<span></td></tr>';
                        }
                        if ($inv->order_tax != 0) {
                        ?>
                          
                          <?php
                            echo '<td class="text-right" colspan="2">' . lang("អាករ") . '/' . lang("VAT 10%") . '</td><th  colspan="2"><span class="pull-left">:</span><span class="pull-right">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</span></td></tr>';
                        }

                        if ($inv->shipping != 0) {
                            echo '<tr><td colspan="2" class="text-right">ថ្លៃដឹកជញ្ជូន/' . lang("delivery") . '</td><th colspan="2"><span class="pull-left">:</span><span class="pull-right">' . '-' . $this->bpas->formatMoney($inv->shipping) . '</span></td></tr>';
                        }
                        if ($return_sale) {
                            if ($return_sale->surcharge != 0) {
                                echo '<tr><td colspan="2" class="text-right">' . lang("order_discount") . '</td><th ><span class="pull-left">:</span><span class="pull-right">' . $this->bpas->formatMoney($return_sale->surcharge) . '</span></td></tr>';
                            }
                        }
                 
                        if ($pos_settings->rounding || $inv->rounding != 0) {
                            ?>
                            <tr>
                                <td><?=lang("rounding");?></td>
                                <th class="text-right"><?= $this->bpas->formatMoney($inv->rounding);?></td>
                            </tr>
                            <tr>
                                <td><?=lang("grand_total");?></td>
                                <th class="text-right"><?=$this->bpas->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));?></td>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <?php
                            if($Settings->default_currency =='USD'){
                                $default_grand_total_lang = "សរុបចុងក្រោយ".'/'.lang("grand_total");
                                $grand_total_lang_kh = "សរុបចុងក្រោយជារៀល".'/'.lang("grand_total_kh");
                            }else{
                                $default_grand_total_lang = "សរុបចុងក្រោយជារៀល".'/'.lang("grand_total_kh");
                                $grand_total_lang_kh = "សរុបចុងក្រោយ".'/'.lang("grand_total");
                            }
                            ?>
                            <tr style="border:1px solid #adabab;">
                                <th colspan="2" style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;text-align: right;">
                                    <?= $default_grand_total_lang;?></td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $detault_currency.$this->bpas->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);?></td>
                            </tr>
                            <tr style="border:1px solid #adabab;">
                                <th colspan="2" class="text-right" style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">
                                    <?php echo $grand_total_lang_kh;?></td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</td>
                                <?php 
                                if($Settings->default_currency =='USD'){

                                    $gTotalkh = $this->bpas->formatDecimal($return_sale ? (($inv->grand_total+$return_sale->grand_total) * $currency_rate_kh) : $inv->grand_total * $currency_rate_kh);
                                    if($gTotalkh % 100 >= 50):
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $khm. number_format(ceil($gTotalkh/100)*100);?></td>
                                    <?php
                                    else:
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $khm. number_format(ceil($gTotalkh/100)*100);?></td>
                                <?php 
                                    endif;
                                }else{
                                    $gTotalud = $return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total;
                           
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right">
                                        <?= $usd. $this->bpas->formatMoney($gTotalud/$currency_rate_kh);?></td>
                                  
                                    <?php
                                }
                                ?>
                            </tr>
                       
                            <?php
                            
                        }
               
                       
                        
                        if ($payments) {
                            $total_paid = 0;
                   
                            echo '<tr>';
                                    echo '<td colspan="2" class="border_top text-right">'.$default_received_lang.'</td>';
                                    echo '<td class="border_top">:</td>';
                                    echo '<td class="border_top border_right text-right">'.$detault_currency.$this->bpas->formatMoney($total_paid).'</td>';
                            echo '</tr>';
                            if ($inv->paid < ($inv->grand_total + $inv->rounding)) {
                            ?>
                                <?php
                                $origi_due_amount=$this->bpas->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));
                                ?>
                                <tr>
                                    <td colspan="2" class="text-right"><?php echo "ប្រាក់ជំពាក់";?>/<?=lang("due_amount").$usd;?></td>
                                  
                                    <th colspan="2" class="border_right"><span class="pull-left">:</span>
                                        <span class="pull-right"><?= $usd.$origi_due_amount;?></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            foreach ($payments as $payment) {
                                if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                                    $decChange = $this->bpas->formatMoney($payment->pos_balance > 0 ? $payment->pos_balance : 0);
                                    if($decChange>0){
                                        if($Settings->default_currency =='USD'){
                                            if($paid_amount_bat){
                                              $change_bat=$this->bpas->formatMoney($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0);
                                                echo '<tr>';
                                                    echo '<td colspan="2" class="border_bottom text-right">' ."ប្រាក់អាប់".'/' .lang("change").$bat.'</td>';
                                                    echo '<td class="border_bottom">:</td>';
                                                    echo '<td class="border_bottom border_right text-right">'.$bat.$this->bpas->formatMoney($change_bat * $currency_rate_bat).'</td>';
                                                echo '</tr>';
                                            }elseif($paid_amount_kh){
                                                $change_kh=$this->bpas->formatMoney($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0);
                                                echo '<tr>';
                                                    echo '<td colspan="2" class="border_bottom text-right">' ."ប្រាក់អាប់".'/' .lang("change").$khm.'</td>';
                                                   
                                                    $realChanged = $change_kh * $currency_rate_kh;
                                                    if($realChanged % 100 >= 50){
                                                        echo '<td class="border_bottom border_right  "colspan="2"><span class="pull-left">:</span><span class="pull-right">'. $usd . number_format($change_kh, 2, '.', '') . ' = ' .$khm.number_format(floor(($change_kh * $currency_rate_kh) / 100) * 100) . '</td>';
                                                    }else{
                                                        echo '<td class="border_bottom border_right  "colspan="2"><span class="pull-left">:</span><span class="pull-right">'. $usd . number_format($change_kh, 2, '.', '') . ' = ' .$khm.number_format(floor(($change_kh * $currency_rate_kh) / 100) * 100) . '</td>';
                                                    }
                                                echo '</tr>';
                                            }else{
                                                
                                                  $change_us = $this->bpas->formatMoney($payment->pos_balance > 0 ? $payment->pos_balance : 0);
                                                echo '<tr>';
                                                    echo '<td colspan="2" class="border_bottom text-right">' ."ប្រាក់អាប់".'/' .lang("change").$usd.'</td>';
                                                    echo '<td class="border_bottom">:</td>';
                                                    echo '<td class="border_bottom border_right text-right">'.$detault_currency.$change_us . '</td>';
                                                echo '</tr>';
                                                echo '<tr>';
                                                    echo '<td class="border_bottom"></td>';
                                                    echo '<td class="border_bottom"></td>';
                                                    $decChangeFinal = $change_us;
                                                    echo ($decChangeFinal > 0) ? '<td class="border_bottom border_right text-right" colspan="2">'. $usd . number_format($decChangeFinal, 2, '.', '') . ' = ' .$khm . floor(($decChangeFinal * $currency_rate_kh) / 100)*100 . '</td>' : '<td></td>';
                                                echo '</tr>';
                                            }
                                        }else{
                                            if($paid_amount_usd){
                                                $change_us = $payment->pos_balance > 0 ? $payment->pos_balance : 0;
                                                echo '<tr>';
                                                    echo '<td colspan="2" class="border_bottom text-right">' ."ប្រាក់អាប់".'/' .lang("change").$khm.'</td>';
                                                   
                                                    echo '<td class="border_bottom border_right" colspan="2"><span class="pull-left">:</span><span class="pull-right>'.$khm.$this->bpas->formatMoney($change_us) . '</span></td>';
                                                echo '</tr>';
                                                echo '<tr>';
                                                    echo '<td class="border_bottom"></td>';
                                                    echo '<td class="border_bottom"></td>';
                                                    $decChangeFinal = $change_us;
                                                    echo ($decChangeFinal > 0) ? '<td class="border_bottom border_right text-right" colspan="2">'. $khm . $this->bpas->formatMoney($decChangeFinal). ' = 
                                                        ' .$usd.$this->bpas->formatMoney($change_us/$currency_rate_kh). '</td>' : '<td></td>';
                                                echo '</tr>';
                                            }elseif($paid_amount_kh){
                                                $change_kh=$this->bpas->formatMoney($payment->pos_balance > 0 ? $payment->pos_balance : 0);
                                                echo '<tr>';
                                                    echo '<td colspan="2" class="border_bottom text-right">' ."ប្រាក់អាប់".'/' .lang("change").$khm.'</td>';
                                                    echo '<td class="border_bottom">:</td>';
                                                    $realChanged = $change_kh * $currency_rate_kh;
                                                    echo '<td class="border_bottom border_right text-right">'.$khm.$change_kh . '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        ?>
                    </tfoot>
                </table>
                 <?php

                if ($payments && count($payments) <= 1) { 
                    echo '<table class="table table-striped table-condensed hide"><tbody>';
                    foreach ($payments as $payment) {
                        echo '<tr>';
                     /*   if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
                        }*/
                        //if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                        if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe')) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            //echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            //echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'ABA' ||$payment->paid_by == 'Acleda' || $payment->paid_by == 'PiPay'||$payment->paid_by == 'Wing' || $payment->paid_by == 'AliPay' || $payment->paid_by == 'toanchet') {
                            echo '<td style="text-transform: uppercase;">' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td></td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . $this->bpas->formatMoney($this->bpas->getCardBalance($payment->cc_no)) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                
                else { 
                    echo '<table class="table table-striped table-condensed hide"><tbody>';

                    foreach ($payments as $payment) {
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
                        }
                        //if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                        if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe')) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            //echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            //echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'ABA' ||$payment->paid_by == 'Acleda' || $payment->paid_by == 'PiPay'||$payment->paid_by == 'Wing' || $payment->paid_by == 'AliPay' || $payment->paid_by == 'toanchet') {
                            echo '<td style="text-transform: uppercase;">' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td></td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . $this->bpas->formatMoney($this->bpas->getCardBalance($payment->cc_no)) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

                if ($return_payments) {
                    echo '<strong>'.lang('return_payments').'</strong><table class="table table-striped table-condensed"><tbody>';
                    foreach ($return_payments as $payment) {
                        $payment->amount = (0-$payment->amount);
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . $this->bpas->formatMoney($this->bpas->getCardBalance($payment->cc_no)) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                ?>


                <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax)) : ''; ?>

                <?= $customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                .'<br>'.
                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>' : ''; ?>
                <?= $inv->note ? '<p class="text-center">' . $this->bpas->decode_html($inv->note) . '</p>' : ''; ?>
                <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->bpas->decode_html($inv->staff_note) . '</p>' : ''; ?>

                <div class="text-left">Note: - សូមរក្សាលុយកាក់និងសំភារះដោយខ្លួនឯង ក្រុមហ៊ុនមិនទទួលខុសត្រូវ លើការបាត់បង់</div>
                <?php // $biller->invoice_footer ? '<div class="text-center">'.$this->bpas->decode_html($biller->invoice_footer).'</div>' : ''; ?>
                <?php // $this->Settings->developed_by ? '<div class="text-center"><font style="font-size:11px;">Solutions provided by <a href="http://sbcsolution.biz/">SBC Solutions</a></font></div>' : ''; ?>

            </div>
            <div class="order_barcodes text-center">
             <!--   <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                <br>-->
                <?php // $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                <?php echo $this->Settings->developed_by ? $this->bpas->qrcode('link', urlencode('http://sbcsolution.biz/'), 2) : ''; ?>
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="text-transform:uppercase;" class="no-print">

            <hr>
            <?php
            if ($message) {
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php
            } ?>
            <?php
            if ($modal) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($pos->remote_printing == 1) {
                            echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        } else {
                            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        }

                        ?>
                    </div>
                    <div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <span class="pull-right col-xs-12">
                    <?php
                    if ($pos->remote_printing == 1) {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
                    }
                    ?>
                </span>

                <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?php
                    if ($pos_settings->pos_type =="pos") {
                        echo admin_url('pos');
                    }else{
                        echo admin_url('table');
                    }?>"><?= lang("back_to_pos"); ?></a>
                </span>
                 <span class="pull-left col-xs-12"><a class="btn btn-block btn-info" href="<?php echo admin_url('pos/sales')?>" id="sales"><?= lang("sales"); ?></a></span>
                <?php
            }
            if ($pos->remote_printing == 12) {
                ?>
                <div style="clear:both;"></div>
                <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                    <p style="font-weight:bold;">
                        Please don't forget to disble the header and footer in browser print settings.
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp; Header/Footer Make all --blank--
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer in Option &amp; Set Margins to None
                    </p>
                </div>
                <?php
            } ?>
            <div style="clear:both;"></div>
        </div>
    </div>

    <?php
    if( ! $modal) {
        ?>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
        <?php
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#email').click(function () {
                bootbox.prompt({
                    title: "<?= lang("email_address"); ?>",
                    inputType: 'email',
                    value: "<?= $customer->email; ?>",
                    callback: function (email) {
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= admin_url('pos/email_receipt') ?>",
                                data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                                dataType: "json",
                                success: function (data) {
                                    bootbox.alert({message: data.msg, size: 'small'});
                                },
                                error: function () {
                                    bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                                    return false;
                                }
                            });
                        }
                    }
                });
                return false;
            });
        });

        <?php
        // if ($pos_settings->remote_printing == 1) {
        //     ?>
        //     $(window).load(function () {
        //         window.print();
        //         return false;
        //     });
        //     <?php
        // }
        ?>

    </script>
    <?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
    <?php include 'remote_printing.php'; ?>
    <?php
    if($modal) {
        ?>
    </div>
</div>
</div>
<?php
} else {
    ?>
</body>
</html>
<?php
}
?>
